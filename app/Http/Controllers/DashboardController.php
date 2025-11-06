<?php

namespace App\Http\Controllers;

use App\Enums\UserRoles;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Comment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $from = \Illuminate\Support\Carbon::now()->subDays(13)->startOfDay();
        $to   = \Illuminate\Support\Carbon::now()->endOfDay();
        $days = collect(range(0, 13))->map(fn($i) => $from->copy()->addDays($i)->toDateString());


        if ($user->isInstructor()) {
            // Instructor stats
            $courses = Course::where('instructor_id', $user->id)
                ->withCount(['students', 'lessons'])
                ->get();

            $publishedCount = (clone $courses)->where('published', true)->count();
            $draftCount     = (clone $courses)->where('published', false)->count();

            $totalStudents  = $courses->sum('students_count');
            $totalLessons   = $courses->sum('lessons_count');

            // Students per course (top 8)
            $topCourses = $courses->sortByDesc('students_count')->take(8);
            $studentsPerCourseLabels = $topCourses->pluck('title')->values();
            $studentsPerCourseData   = $topCourses->pluck('students_count')->values();

            // Comments per day (last 14 days)
            $comments = \App\Models\Comment::whereHas('lesson.course', fn($q) => $q->where('instructor_id', $user->id))
                ->whereBetween('created_at', [$from, $to])
                ->get()
                ->groupBy(fn($c) => $c->created_at->toDateString());

            $commentsPerDay = $days->map(
                fn($d) => $comments->get($d, collect())->count()
            )->values();

            $all_courses      = Course::count();
            $all_published    = Course::where('published', true)->count();
            $all_drafts       = Course::where('published', false)->count();
            $all_students     = DB::table('course_user')->distinct('user_id')->count('user_id');
            $all_lessons      = Lesson::count();
            $all_comments     = Comment::count();
            $all_attach_with  = Lesson::whereNotNull('attachment_path')->count();
            $all_attach_without = Lesson::whereNull('attachment_path')->count();

            $all_cards = [
                'Courses'              => $all_courses,
                'Published'            => $all_published,
                'Drafts'               => $all_drafts,
                'Unique Students'      => $all_students,
                'Lessons'              => $all_lessons,
                'Comments'             => $all_comments,
                'With Attachments'     => $all_attach_with,
                'Without Attachments'  => $all_attach_without,
            ];

            return view('dashboard', [
                'cards' => [
                    'courses'   => $courses->count(),
                    'published' => $publishedCount,
                    'drafts'    => $draftCount,
                    'students'  => $totalStudents,
                    'lessons'   => $totalLessons,
                ],
                'allCards' => $all_cards,
                'role' => UserRoles::INSTRUCTOR,
                'chart_students_per_course' => [
                    'labels' => $studentsPerCourseLabels,
                    'data'   => $studentsPerCourseData,
                ],
                'chart_comments_per_day' => [
                    'labels' => $days,
                    'data'   => $commentsPerDay,
                ],
            ]);
        }

        // Student stats
        $enrolledCourses = $user->coursesEnrolled()
            ->withCount(['lessons', 'students'])
            ->get();

        $enrolledCount  = $enrolledCourses->count();
        $totalELessons  = $enrolledCourses->sum('lessons_count');

        // Lessons per enrolled course (top 8)
        $topEnrolled = $enrolledCourses->sortByDesc('lessons_count')->take(8);
        $lessonsPerCourseLabels = $topEnrolled->pluck('title')->values();
        $lessonsPerCourseData   = $topEnrolled->pluck('lessons_count')->values();

        // Attachments vs no-attachments across enrolled lessons (quick feel)
        $enrolledCourseIds = $enrolledCourses->pluck('id');
        $attachmentsAgg = Lesson::whereIn('course_id', $enrolledCourseIds)->selectRaw("
                SUM(CASE WHEN attachment_path IS NULL THEN 1 ELSE 0 END) as no_attachment,
                SUM(CASE WHEN attachment_path IS NOT NULL THEN 1 ELSE 0 END) as with_attachment
            ")->first();
        $attachWith  = (int) ($attachmentsAgg->with_attachment ?? 0);
        $attachWithout = (int) ($attachmentsAgg->no_attachment ?? 0);

        // My comments per day (last 14 days)
        $myComments = \App\Models\Comment::where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy(fn($c) => $c->created_at->toDateString());

        $myCommentsPerDay = $days->map(
            fn($d) => $myComments->get($d, collect())->count()
        )->values();

        return view('dashboard', [
            'role' => 'student',
            'cards' => [
                'enrolled' => $enrolledCount,
                'lessons'  => $totalELessons,
                'attachments_with' => $attachWith,
                'attachments_without' => $attachWithout,
            ],
            'chart_lessons_per_course' => [
                'labels' => $lessonsPerCourseLabels,
                'data'   => $lessonsPerCourseData,
            ],
            'chart_my_comments_per_day' => [
                'labels' => $days,
                'data'   => $myCommentsPerDay,
            ],
        ]);
    }
}
