<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Notifications\EnrollmentConfirmed;
use Illuminate\Support\Facades\Notification;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_enroll_and_appears_in_api_me_courses(): void
    {
        $instructor = User::factory()->create(['role' => UserRoles::INSTRUCTOR]);
        /** @var Course $course */
        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'published' => true,
            'slug' => Str::slug(fake()->unique()->sentence(3)),
        ]);

        /** @var User $student */
        $student = User::factory()->create(['role' => UserRoles::STUDENT]);

        // Enroll via web route
        $this->actingAs($student)->post(route('courses.enroll', $course))
            ->assertStatus(302);

        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id'   => $student->id,
        ]);

        // Check API (Sanctum auth not necessary if session is used for /api in tests; if you force Sanctum, use actingAs($student, 'sanctum'))
        $json = $this->actingAs($student)->getJson('/api/me/courses')
            ->assertOk()
            ->json();

        // Ensure course is in 'enrolled' list
        $ids = collect($json['enrolled'])->pluck('id')->all();
        $this->assertContains($course->id, $ids);
    }

    // Enrollment email is sent & queued
    public function test_enrollment_sends_notification(): void
    {
        Notification::fake();

        // Force jobs to run immediately for this test
        config(['queue.default' => 'sync']);

        $student    = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $course = Course::factory()->state([
            'instructor_id' => $instructor->id,
            'published'     => true,
        ])->create();

        $this->withoutExceptionHandling();

        $this->actingAs($student)->post(route('courses.enroll', $course))->assertRedirect();

        Notification::assertSentTo($student, \App\Notifications\EnrollmentConfirmed::class);
    }
}
