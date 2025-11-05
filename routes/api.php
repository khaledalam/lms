<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseApiController;
use App\Http\Controllers\Api\LessonApiController;
use App\Http\Controllers\Api\CommentApiController;
use App\Http\Controllers\Api\EnrollmentApiController;

// test api
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// Protected routes will go here (auth:sanctum)
Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('courses', CourseApiController::class);
    Route::apiResource('courses.lessons', LessonApiController::class)->shallow();
    Route::post('courses/{course}/enroll', [EnrollmentApiController::class, 'store']);
    Route::get('me/courses', [EnrollmentApiController::class, 'index']);
    Route::apiResource('lessons.comments', CommentApiController::class)->only(['index', 'store'])->shallow();
});
