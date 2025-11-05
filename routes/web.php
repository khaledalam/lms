<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LessonController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('courses.index'));

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
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

    // Instructor: view student roster
    Route::get('/courses/{course}/students', [EnrollmentController::class, 'index'])->name('courses.students');

});

require __DIR__ . '/auth.php';
