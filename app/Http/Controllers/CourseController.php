<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $q        = $request->string('q')->toString();                 // search term
        $pubParam = $request->query('published');                      // '1' | '0' | null
        $published = isset($pubParam) ? (int) $pubParam : null;        // convert to int or null

        if ($user->isInstructor()) {
            // Instructor: show their list (with optional filters), plus a public list below.
            $myCourses = Course::query()
                ->select(['id', 'title', 'slug', 'published', 'instructor_id'])
                ->instructor($user->id)
                ->searchDeep($q)
                ->published($published)
                ->withCount(['students', 'lessons'])
                ->with(['instructor:id,name'])
                ->orderByDesc('id')
                ->simplePaginate(8)
                ->appends($request->query());

            $publishedList = Course::query()
                ->select(['id', 'title', 'slug', 'published', 'instructor_id'])
                ->published(true)
                ->searchDeep($q)
                ->withCount(['students', 'lessons'])
                ->with(['instructor:id,name'])
                ->orderBy('title')
                ->simplePaginate(12)
                ->appends($request->query());

            return view('courses.index', [
                'myCourses' => $myCourses,
                'published' => $publishedList,
                'filters'   => ['q' => $q, 'published' => $pubParam],
            ]);
        }

        // Student: only published courses, with search
        $enrolled = $user->coursesEnrolled()
            ->select(['courses.id', 'title', 'slug', 'published', 'instructor_id'])
            ->searchDeep($q)
            ->withCount('students')
            ->published(true)
            ->withCount(['students', 'lessons'])
            ->with(['instructor:id,name'])
            ->orderBy('title')
            ->simplePaginate(8)
            ->appends($request->query());

        $courses = Course::query()
            ->select(['id', 'title', 'slug', 'published', 'instructor_id'])
            ->published(true)
            ->searchTitle($q)
            ->withCount(['students', 'lessons'])
            ->withCount(['students as enrolled_count' => fn($q2) => $q2->where('users.id', $user->id)])
            ->with(['instructor:id,name'])
            ->orderBy('title')
            ->simplePaginate(12)
            ->appends($request->query());

        return view('courses.index', [
            'courses' => $courses,
            'enrolled' => $enrolled,
            'filters' => ['q' => $q, 'published' => $pubParam],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Course::class);

        return view('courses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request)
    {
        $this->authorize('create', Course::class);

        $validated = $request->validated();

        $course = Course::create([
            'instructor_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'published' => (bool) $validated['published'],
        ]);

        return redirect()->route('courses.show', $course)
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $this->authorize('view', $course);

        $course->load([
            'lessons:id,course_id,title,order',
        ])->loadCount('students');

        $isInstructorOwner = (int) $course->instructor_id === (int) Auth::id();
        $isInstructor = Auth::user()->isInstructor();


        $isEnrolled = Auth::check()
            ? $course->students()->whereKey(Auth::id())->exists()
            : false;

        $lessons = Cache::remember("course_{$course->id}_lessons", 300, function () use ($course) {
            return $course->lessons()->orderBy('order')->get(['id', 'title', 'course_id', 'order', 'attachment_path']);
        });

        return view('courses.show', compact('course', 'isInstructor', 'isInstructorOwner', 'isEnrolled',  'lessons'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        return view('courses.edit', compact('course'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCourseRequest $request, Course $course)
    {
        $this->authorize('update', $course);

        $course->update($request->validated());

        return redirect()->route('courses.show', $course)->with('success', 'Course updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted.');
    }
}
