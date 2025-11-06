<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $from = now()->subDays(13)->startOfDay();
        $to   = now()->endOfDay();
        $days = collect(range(0, 13))->map(fn($i) => $from->copy()->addDays($i)->toDateString());

        // -------- System-wide cards --------
        $allCards = Cache::remember('dash.allCards', 60, function () {
            // courses: total / published / drafts in ONE query
            $c = Course::selectRaw("
                COUNT(*)                                       as total,
                SUM(CASE WHEN published = 1 THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN published = 0 THEN 1 ELSE 0 END) as drafts
            ")->first();

            // lessons: total / with_attachment / without_attachment in ONE query
            $l = Lesson::selectRaw("
                COUNT(*)                                            as total,
                SUM(CASE WHEN attachment_path IS NOT NULL THEN 1 ELSE 0 END) as with_attach,
                SUM(CASE WHEN attachment_path IS NULL     THEN 1 ELSE 0 END) as without_attach
            ")->first();

            return [
                'Courses'             => (int) $c->total,
                'Published'           => (int) $c->published,
                'Drafts'              => (int) $c->drafts,
                'Unique Students'     => (int) DB::table('course_user')->distinct('user_id')->count('user_id'),
                'Lessons'             => (int) $l->total,
                'Comments'            => (int) Comment::count(),
                'With Attachments'    => (int) $l->with_attach,
                'Without Attachments' => (int) $l->without_attach,
            ];
        });

        // -------- Instructor view --------
        if (method_exists($user, 'isInstructor') ? $user->isInstructor() : (($user->role ?? null) === 'instructor')) {

            [$cards, $chartStudentsPerCourse, $chartCommentsPerDay, $enrolledStudents] =
                Cache::remember("dash.instructor.{$user->id}", 60, function () use ($user, $from, $to, $days) {

                    $courseIds = Course::where('instructor_id', $user->id)->pluck('id');
                    $totalCourses = $courseIds->count();

                    // batch courses published/drafts in one query (only when instructor has courses)
                    $publishedCount = $totalCourses
                        ? (int) Course::whereIn('id', $courseIds)
                            ->selectRaw("SUM(CASE WHEN published = 1 THEN 1 ELSE 0 END) AS p")->value('p')
                        : 0;
                    $draftCount   = $totalCourses - $publishedCount;

                    // lessons count in one query
                    $totalLessons = $totalCourses
                        ? (int) Lesson::whereIn('course_id', $courseIds)->count()
                        : 0;

                    // unique students across instructor courses
                    $uniqueStudentsCount = $totalCourses
                        ? (int) DB::table('course_user')->whereIn('course_id', $courseIds)
                            ->distinct('user_id')->count('user_id')
                        : 0;

                    // top courses by students (one query)
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

                    // comments per day for last 14 days (one grouped query)
                    $commentsRows = $totalCourses
                        ? Comment::selectRaw("DATE(created_at) as d, COUNT(*) as c")
                        ->whereIn('lesson_id', function ($q) use ($courseIds) {
                            $q->select('id')->from('lessons')->whereIn('course_id', $courseIds);
                        })
                        ->whereBetween('created_at', [$from, $to])
                        ->groupBy('d')
                        ->pluck('c', 'd')
                        : collect();

                    $chartCommentsPerDay = [
                        'labels' => $days,
                        'data'   => $days->map(fn($d) => (int) ($commentsRows[$d] ?? 0))->values(),
                    ];

                    // enrolled students list (one query)
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

                    return [
                        // cards
                        [
                            'courses'   => $totalCourses,
                            'published' => $publishedCount,
                            'drafts'    => $draftCount,
                            'students'  => $uniqueStudentsCount,
                            'lessons'   => $totalLessons,
                        ],
                        $chartStudentsPerCourse,
                        $chartCommentsPerDay,
                        $enrolledStudents,
                    ];
                });

            return view('dashboard', [
                'role'                       => 'instructor',
                'cards'                      => $cards,
                'enrolledStudents'           => $enrolledStudents,
                'allCards'                   => $allCards,
                'chart_students_per_course'  => $chartStudentsPerCourse,
                'chart_comments_per_day'     => $chartCommentsPerDay,
            ]);
        }

        // -------- Student view --------
        [$cards, $chartLessonsPerCourse, $chartMyCommentsPerDay] =
            Cache::remember("dash.student.{$user->id}", 60, function () use ($user, $from, $to, $days) {

                $enrolledCourses = $user->coursesEnrolled()
                    ->withCount(['lessons', 'students'])
                    ->get(['courses.id', 'title']);

                $enrolledCount  = $enrolledCourses->count();
                $totalELessons  = (int) $enrolledCourses->sum('lessons_count');

                $top = $enrolledCourses->sortByDesc('lessons_count')->take(8);
                $chartLessonsPerCourse = [
                    'labels' => $top->pluck('title')->values(),
                    'data'   => $top->pluck('lessons_count')->values(),
                ];

                $mine = Comment::selectRaw("DATE(created_at) as d, COUNT(*) as c")
                    ->where('user_id', $user->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->groupBy('d')
                    ->pluck('c', 'd');

                $chartMyCommentsPerDay = [
                    'labels' => $days,
                    'data'   => $days->map(fn($d) => (int) ($mine[$d] ?? 0))->values(),
                ];

                return [
                    ['enrolled' => $enrolledCount, 'lessons' => $totalELessons],
                    $chartLessonsPerCourse,
                    $chartMyCommentsPerDay,
                ];
            });

        return view('dashboard', [
            'role'                        => 'student',
            'cards'                       => $cards,
            'allCards'                    => $allCards,
            'chart_lessons_per_course'    => $chartLessonsPerCourse,
            'chart_my_comments_per_day'   => $chartMyCommentsPerDay,
        ]);
    }
}
