@extends('layouts.base')

@section('content')
    <div class="max-w-5xl mx-auto py-8 space-y-6">
        @if (session('success'))
            <div class="bg-green-100 p-3">{{ session('success') }}</div>
        @endif

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ $course->title }}</h1>
                <p class="text-gray-700 mt-1">{{ $course->description }}</p>
            </div>
            <div class="space-x-2">
                @if ($isInstructor)
                    <a href="{{ route('courses.edit', $course) }}" class="px-3 py-2 border rounded">Edit</a>
                    <a href="{{ route('courses.students', $course) }}" class="px-3 py-2 border rounded">Students</a>
                    <a href="{{ route('courses.lessons.create', $course) }}" class="px-3 py-2 bg-black text-white rounded">+
                        Lesson</a>
                @elseif(!$isEnrolled)
                    <form method="POST" action="{{ route('courses.enroll', $course) }}" class="inline">
                        @csrf
                        <button class="px-3 py-2 bg-black text-white rounded">Enroll</button>
                    </form>
                @endif
            </div>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-2">Lessons</h2>
            @if ($course->lessons->isEmpty())
                <p class="text-gray-600">No lessons yet.</p>
            @else
                <div class="space-y-2">
                    @foreach ($course->lessons as $lesson)
                        <div class="border rounded p-3 flex items-center justify-between">
                            <a href="{{ route('lessons.show', $lesson) }}" class="font-medium">{{ $lesson->order }}.
                                {{ $lesson->title }}</a>
                            @if ($isInstructor)
                                <div class="space-x-2">
                                    <form method="POST" action="{{ route('lessons.move_up', $lesson) }}" class="inline">
                                        @csrf
                                        <button class="px-2 py-1 border rounded">↑</button>
                                    </form>
                                    <form method="POST" action="{{ route('lessons.move_down', $lesson) }}" class="inline">
                                        @csrf
                                        <button class="px-2 py-1 border rounded">↓</button>
                                    </form>
                                    <a href="{{ route('lessons.edit', $lesson) }}"
                                        class="px-2 py-1 border rounded">Edit</a>
                                    <form method="POST" action="{{ route('lessons.destroy', $lesson) }}" class="inline"
                                        onsubmit="return confirm('Delete this lesson?')">
                                        @csrf @method('DELETE')
                                        <button class="px-2 py-1 border rounded text-red-600">Delete</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endsection
