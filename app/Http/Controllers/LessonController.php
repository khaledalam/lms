<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        $this->authorize('update', $course);

        return view('lessons.create', compact('course'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'nullable|string',
        ]);

        $max = (int) $course->lessons()->max('order');
        $lesson = $course->lessons()->create([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'order' => $max + 1,
        ]);

        return redirect()->route('courses.show', $course)->with('success', 'Lesson added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        $this->authorize('view', $lesson);

        $lesson->load('course', 'comments.user');

        return view('lessons.show', compact('lesson'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        $this->authorize('update', $lesson);

        $lesson->load('course');
        $course = $lesson->course;

        return view('lessons.edit', compact('course', 'lesson'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $this->authorize('update', $lesson);

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'nullable|string',
        ]);

        $lesson->update($data);

        return redirect()->route('courses.show', $lesson->course)->with('success', 'Lesson updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $this->authorize('delete', $lesson);

        $course = $lesson->course;
        $lesson->delete();

        return redirect()->route('courses.show', $course)->with('success', 'Lesson deleted.');
    }

    /**
     * Move the lesson up in order.
     */
    public function moveUp(Lesson $lesson)
    {
        $this->authorize('update', $lesson);

        $prev = $lesson->course->lessons()->where('order', '<', $lesson->order)->orderBy('order', 'desc')->first();
        if ($prev) {
            [$lesson->order, $prev->order] = [$prev->order, $lesson->order];
            $lesson->save();
            $prev->save();
        }

        return back();
    }

    /**
     * Move the lesson down in order.
     */
    public function moveDown(Lesson $lesson)
    {
        $this->authorize('update', $lesson);
        
        $next = $lesson->course->lessons()->where('order', '>', $lesson->order)->orderBy('order')->first();
        if ($next) {
            [$lesson->order, $next->order] = [$next->order, $lesson->order];
            $lesson->save();
            $next->save();
        }

        return back();
    }
}
