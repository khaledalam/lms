<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', fn() => redirect()->route('courses.index'));

Route::get('/welcome', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('courses', CourseController::class);

    // Lessons (shallow so /lessons/{lesson}/edit works)
    Route::resource('courses.lessons', LessonController::class)->shallow();

    // Simple reorder
    Route::post('/lessons/{lesson}/move-up',   [LessonController::class, 'moveUp'])->name('lessons.move_up');
    Route::post('/lessons/{lesson}/move-down', [LessonController::class, 'moveDown'])->name('lessons.move_down');

    // Enroll (student-only via controller logic)
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('courses.enroll');

    // Comments on lessons
    Route::post('/lessons/{lesson}/comments', [CommentController::class, 'store'])->name('lessons.comments.store');

    // Attachments on lessons
    Route::get('/lessons/{lesson}/attachment', [\App\Http\Controllers\LessonController::class, 'attachment'])->name('lessons.attachment');

    // Instructor: view student roster
    Route::get('/courses/{course}/students', [EnrollmentController::class, 'index'])->name('courses.students');

    // Student Comments
    Route::get('/me/comments', [CommentController::class, 'myComments'])->name('me.comments');
});

// For Debug:
Route::post('/run-demo-seeder', function () {
    try {
        Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        return response()->json(['success' => true, 'message' => 'Demo data seeded successfully!', 'csrf' => csrf_token(),]);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
})->name('run.demo.seeder');

require __DIR__ . '/auth.php';
