<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CourseFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_create_course(): void
    {
        $instructor = User::factory()->create(['role' => UserRoles::INSTRUCTOR]);

        $resp = $this->actingAs($instructor)->post(route('courses.store'), [
            'title' => 'Intro to PHP',
            'description' => 'Basics',
            'published' => true,
            'slug' => Str::slug('Intro to PHP'),
        ]);

        $resp->assertStatus(302);
        $this->assertDatabaseHas('courses', [
            'title' => 'Intro to PHP',
            'instructor_id' => $instructor->id,
        ]);
    }

    public function test_student_cannot_create_course(): void
    {
        $student = User::factory()->create(['role' => UserRoles::STUDENT]);

        $resp = $this->actingAs($student)->post(route('courses.store'), [
            'title' => 'Should Fail',
            'description' => 'Nope',
            'published' => false,
            'slug' => 'should-fail',
        ]);

        $resp->assertForbidden();
        $this->assertDatabaseMissing('courses', ['title' => 'Should Fail']);
    }
}
