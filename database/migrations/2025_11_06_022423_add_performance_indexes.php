<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // === COURSES ===
        Schema::table('courses', function (Blueprint $table) {
            // For filtering and joins
            $table->index('instructor_id');
            $table->index('published');

            // For LIKE searches and sorting
            $table->index('title');

            // General time-based queries (e.g., recent courses)
            $table->index('created_at');

            // Optional: if you use slug frequently
            if (!Schema::hasColumn('courses', 'slug')) return;
            $table->index('slug');
        });

        // === LESSONS ===
        Schema::table('lessons', function (Blueprint $table) {
            // Most frequent filters and sorts
            $table->index(['course_id', 'order']);
            $table->index('created_at');

            // For your new attachment search/filters
            $table->index('attachment_path');
        });

        // === COURSE_USER (pivot) ===
        Schema::table('course_user', function (Blueprint $table) {
            $table->index(['course_id', 'user_id']); // for enrollments
            $table->index(['user_id', 'course_id']); // reverse lookup (student dashboard)
        });

        // === COMMENTS ===
        Schema::table('comments', function (Blueprint $table) {
            $table->index(['lesson_id', 'created_at']);
            $table->index('user_id'); // for "my comments" and instructor stats
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['instructor_id']);
            $table->dropIndex(['published']);
            $table->dropIndex(['title']);
            $table->dropIndex(['created_at']);
            if (Schema::hasColumn('courses', 'slug')) $table->dropIndex(['slug']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'order']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['attachment_path']);
        });

        Schema::table('course_user', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'user_id']);
            $table->dropIndex(['user_id', 'course_id']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['lesson_id', 'created_at']);
            $table->dropIndex(['user_id']);
        });
    }
};
