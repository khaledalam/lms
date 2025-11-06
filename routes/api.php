<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseApiController;
use App\Http\Controllers\Api\LessonApiController;
use App\Http\Controllers\Api\CommentApiController;
use App\Http\Controllers\Api\EnrollmentApiController;
use App\Http\Controllers\Api\AuthApiController;

// test api
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::post('/auth/token', [AuthApiController::class, 'login'])->name('api.auth.token');

// Protected routes will go here (auth:sanctum)
Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('me/courses', [EnrollmentApiController::class, 'index']);


    Route::apiResource('courses', CourseApiController::class);
    Route::apiResource('courses.lessons', LessonApiController::class)->shallow();
    Route::post('courses/{course}/enroll', [EnrollmentApiController::class, 'store']);

    Route::apiResource('lessons.comments', CommentApiController::class)->only(['index', 'store'])->shallow();
    Route::get('lessons/{lesson}/attachment', [LessonApiController::class, 'attachment'])->name('lessons.attachment');
    Route::get('lessons/{lesson:id}/comments', [CommentApiController::class, 'index']);
});
