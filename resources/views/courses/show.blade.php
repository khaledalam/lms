<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course') }}: {{ $course->title }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 space-y-6">
        @if (session('success'))
            <div class="bg-green-100 p-3">{{ session('success') }}</div>
        @endif

        <x-back-button :url="route('courses.index')">Go Back</x-back-button>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ $course->title }}</h1>
                <p class="text-gray-700 mt-1">{{ $course->description }}</p>
            </div>
            <div class="flex gap-3 items-center">
                @if ($isInstructorOwner)
                    <a href="{{ route('courses.edit', $course) }}" class="px-3 py-2 border rounded">Edit</a>
                    <a href="{{ route('courses.students', $course) }}" class="px-3 py-2 border rounded"
                        style="width: 130px;">Students
                        ({{ $course->students_count }})</a>
                    <a href="{{ route('courses.lessons.create', $course) }}"
                        class="px-3 py-2 center bg-black text-white rounded" style="width: 95px;">+ Lesson</a>
                @elseif(!$isEnrolled && Auth::user()->isStudent())
                    <form method="POST" action="{{ route('courses.enroll', $course) }}" class="inline">
                        @csrf
                        <button class="px-3 py-2 bg-black text-white rounded">Enroll</button>
                    </form>
                @endif
            </div>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-2">Lessons</h2>
            @if (!$isEnrolled && !$isInstructor)
                <p class="text-gray-600 mb-4">⚠️ Enroll in this course to access the lessons.</p>
            @elseif ($lessons->isEmpty())
                <p class="text-gray-600">No lessons yet.</p>
            @else
                <div class="space-y-2">
                    @foreach ($lessons as $lesson)
                        <div class="border rounded p-3 flex items-center justify-between">
                            <a href="{{ route('lessons.show', $lesson) }}"
                                class="font-medium underline">{{ $lesson->order }}.
                                {{ $lesson->title }} @if ($lesson->attachment_path) 📎 @endif</a>
                            @if ($isInstructorOwner)
                                <div class="flex gap-3 items-center">
                                    @if ($course->lessons_count > 1)
                                        <form method="POST" action="{{ route('lessons.move_up', $lesson) }}"
                                            class="inline">
                                            @csrf
                                            <button class="px-2 py-1 border rounded">↑</button>
                                        </form>
                                        <form method="POST" action="{{ route('lessons.move_down', $lesson) }}"
                                            class="inline">
                                            @csrf
                                            <button class="px-2 py-1 border rounded">↓</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('lessons.edit', $lesson) }}"
                                        class="px-2 py-1 border rounded">Edit</a>
                                    <form method="POST" action="{{ route('lessons.destroy', $lesson) }}"
                                        class="inline" onsubmit="return confirm('Delete this lesson?')">
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
</x-app-layout>
