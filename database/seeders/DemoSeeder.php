<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class DemoSeeder extends Seeder
{
    const COURSES_COUNT = 10;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear old data
        Storage::makeDirectory('lesson-attachments');

        User::truncate();
        Course::truncate();
        Lesson::truncate();
        Comment::truncate();

        // --- Users ----------------------------------------------------------
        // Instructors (fixed emails for easy login)
        $instructorA = User::updateOrCreate(
            ['email' => 'instructor1@example.com'],
            [
                'name'     => 'Instructor One',
                'password' => Hash::make('password'),
                'role'     => UserRoles::INSTRUCTOR,
            ]
        );

        $instructorB = User::updateOrCreate(
            ['email' => 'instructor2@example.com'],
            [
                'name'     => 'Instructor Two',
                'password' => Hash::make('password'),
                'role'     => UserRoles::INSTRUCTOR,
            ]
        );

        // Students
        $student1 = User::updateOrCreate(
            ['email' => 'student1@example.com'],
            [
                'name'     => 'Student One',
                'password' => Hash::make('password'),
                'role'     => UserRoles::STUDENT,
            ]
        );
        $student2 = User::updateOrCreate(
            ['email' => 'student2@example.com'],
            [
                'name'     => 'Student Two',
                'password' => Hash::make('password'),
                'role'     => UserRoles::STUDENT,
            ]
        );
        $student3 = User::updateOrCreate(
            ['email' => 'student3@example.com'],
            [
                'name'     => 'Student Three',
                'password' => Hash::make('password'),
                'role'     => UserRoles::STUDENT,
            ]
        );

        $instructors = collect([$instructorA, $instructorB]);
        $students    = collect([$student1, $student2, $student3]);

        // Create lessons with optional attachments
        $sampleFiles = [
            base_path('storage/app/lesson-attachments/demo1.pdf'),
            base_path('storage/app/lesson-attachments/demo2.txt'),
        ];

        // --- Courses + Lessons + Enrollments + Comments ---------------------
        // Create self::COURSES_COUNT courses owned by random instructor
        $courses = collect();
        for ($i = 0; $i < self::COURSES_COUNT; $i++) {
            $owner  = $instructors->random();
            /** @var Course $course */
            $course = Course::factory()->create([
                'instructor_id' => $owner->id,
            ]);
            $courses->push($course);

            // Lessons (5-15), maintain sequential 'order'
            $lessonsCount = fake()->numberBetween(5, 15);
            for ($ord = 1; $ord <= $lessonsCount; $ord++) {
                Lesson::factory()->create([
                    'course_id' => $course->id,
                    'order'     => $ord,
                ]);
            }

            // Enroll 1-3 random students
            $enrolled = $students->random(fake()->numberBetween(1, $students->count()));
            $course->students()->syncWithoutDetaching($enrolled->pluck('id')->all());

            // Comments per lesson: 0-5 by enrolled students or instructor
            $commenters = $enrolled->push($owner);
            $course->load('lessons');
            foreach ($course->lessons as $lesson) {
                $commentsNum = fake()->numberBetween(0, 5);
                for ($c = 0; $c < $commentsNum; $c++) {
                    Comment::factory()->create([
                        'lesson_id' => $lesson->id,
                        'user_id'   => $commenters->random()->id,
                    ]);
                }

                // 50% chance to attach a file
                if (rand(0, 1) === 1) {
                    $randomFile = $sampleFiles[array_rand($sampleFiles)];
                    $path = Storage::putFile('lesson-attachments', new File($randomFile));
                    $lesson->attachment_path = $path;
                    $lesson->save();
                }
            }
        }

        $this->command?->info('Demo data seeded: 2 instructors, 3 students, ' . self::COURSES_COUNT . ' courses with lessons, enrollments, comments.');
        $this->command?->info('Login with instructor1@example.com / password or student1@example.com / password');
    }
}
