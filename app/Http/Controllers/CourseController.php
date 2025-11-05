<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Students see only published; instructors see their own (all)
        if ($user->isInstructor()) {
            $myCourses = Course::where('instructor_id', $user->id)
                ->withCount('students')->latest()->paginate(10);
            $published = Course::published()->latest()->paginate(10);

            return view('courses.index', compact('myCourses', 'published'));
        }

        $courses = Course::published()->withCount('students')->latest()->paginate(12);

        return view('courses.index', compact('courses'));
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

        $course = Course::create([
            'instructor_id' => Auth::id(),
            'title' => $request->string('title'),
            'description' => $request->input('description'),
            'published' => (bool) $request->boolean('published'),
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

        $course->load(['lessons' => fn($q) => $q->orderBy('order')]);
        $isInstructor = (int) $course->instructor_id === (int) Auth::id();
        $isEnrolled = $course->students()->where('users.id', Auth::id())->exists();
        return view('courses.show', compact('course', 'isInstructor', 'isEnrolled'));
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
