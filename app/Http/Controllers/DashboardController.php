<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Common time axis for charts (last 14 days)
        $from = now()->subDays(13)->startOfDay();
        $to   = now()->endOfDay();
        $days = collect(range(0, 13))->map(fn($i) => $from->copy()->addDays($i)->toDateString());

        // ---------------- System-wide cards (always available) ----------------
        // If your DB grows, consider Cache::remember('all_overview', 60, fn () => [...])
        $allCards = [
            'Courses'              => Course::count(),
            'Published'            => Course::where('published', true)->count(),
            'Drafts'               => Course::where('published', false)->count(),
            'Unique Students'      => DB::table('course_user')->distinct('user_id')->count('user_id'),
            'Lessons'              => Lesson::count(),
            'Comments'             => Comment::count(),
            'With Attachments'     => Lesson::whereNotNull('attachment_path')->count(),
            'Without Attachments'  => Lesson::whereNull('attachment_path')->count(),
        ];

        // ---------------- Instructor dashboard ----------------
        if (method_exists($user, 'isInstructor') ? $user->isInstructor() : ($user->role ?? null) === 'instructor') {
            // Reuse instructor course ids across queries
            $courseIds = Course::where('instructor_id', $user->id)->pluck('id');

            $totalCourses   = $courseIds->count();
            $publishedCount = $totalCourses
                ? Course::whereIn('id', $courseIds)->where('published', true)->count()
                : 0;
            $draftCount     = $totalCourses - $publishedCount;
            $totalLessons   = $totalCourses
                ? Lesson::whereIn('course_id', $courseIds)->count()
                : 0;

            // Unique students across ALL of the instructor’s courses
            $uniqueStudentsCount = $totalCourses
                ? DB::table('course_user')->whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id')
                : 0;

            // Chart: students per course (top 8)
            $topCourses = $totalCourses
                ? Course::whereIn('id', $courseIds)
                ->withCount('students')
                ->orderByDesc('students_count')
                ->take(8)
                ->get(['id', 'title'])
                : collect();

            $chartStudentsPerCourse = [
                'labels' => $topCourses->pluck('title')->values(),
                'data'   => $topCourses->pluck('students_count')->values(),
            ];

            // Chart: comments per day (last 14 days) on instructor’s courses
            $commentsGrouped = $totalCourses
                ? Comment::whereHas('lesson', fn($q) => $q->whereIn('course_id', $courseIds))
                ->whereBetween('created_at', [$from, $to])
                ->get()
                ->groupBy(fn($c) => $c->created_at->toDateString())
                : collect();

            $chartCommentsPerDay = [
                'labels' => $days,
                'data'   => $days->map(fn($d) => $commentsGrouped->get($d, collect())->count())->values(),
            ];

            // List: enrolled students under this instructor (top by #courses with you)
            $enrolledStudents = $totalCourses
                ? User::query()
                ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(course_user.course_id) as courses_count'))
                ->join('course_user', 'course_user.user_id', '=', 'users.id')
                ->whereIn('course_user.course_id', $courseIds)
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderByDesc('courses_count')
                ->limit(12)
                ->get()
                : collect();

            return view('dashboard', [
                'role'   => 'instructor',
                // Personal Insights
                'cards'  => [
                    'courses'   => $totalCourses,
                    'published' => $publishedCount,
                    'drafts'    => $draftCount,
                    'students'  => $uniqueStudentsCount, // unique people in your courses
                    'lessons'   => $totalLessons,
                ],
                'enrolledStudents'          => $enrolledStudents,
                // Platform Summary (always present)
                'allCards'                  => $allCards,
                // Charts
                'chart_students_per_course' => $chartStudentsPerCourse,
                'chart_comments_per_day'    => $chartCommentsPerDay,
            ]);
        }

        // ---------------- Student dashboard ----------------
        $enrolledCourses = $user->coursesEnrolled()
            ->withCount(['lessons', 'students'])
            ->get();

        $enrolledCount = $enrolledCourses->count();
        $totalELessons = $enrolledCourses->sum('lessons_count');

        // Chart: lessons per enrolled course (top 8)
        $topEnrolled = $enrolledCourses->sortByDesc('lessons_count')->take(8);
        $chartLessonsPerCourse = [
            'labels' => $topEnrolled->pluck('title')->values(),
            'data'   => $topEnrolled->pluck('lessons_count')->values(),
        ];

        // Chart: my comments per day (last 14 days)
        $myCommentsGrouped = Comment::where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy(fn($c) => $c->created_at->toDateString());

        $chartMyCommentsPerDay = [
            'labels' => $days,
            'data'   => $days->map(fn($d) => $myCommentsGrouped->get($d, collect())->count())->values(),
        ];

        return view('dashboard', [
            'role'  => 'student',
            // Personal Insights
            'cards' => [
                'enrolled'            => $enrolledCount,
                'lessons'             => $totalELessons,
            ],
            // Platform Summary (present for students too → fixes Undefined variable $allCards)
            'allCards' => $allCards,
            // Charts
            'chart_lessons_per_course' => $chartLessonsPerCourse,
            'chart_my_comments_per_day' => $chartMyCommentsPerDay,
        ]);
    }
}
