<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Courses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 p-3 rounded mb-4">{{ session('success') }}</div>
            @endif

            <form method="GET" class="mb-4 flex flex-wrap justify-center gap-3" style="align-items: end;">
                <div>
                    <label for="search-input" class="block text-sm font-medium mb-1">Search</label>
                    <input id="search-input" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Course title, description, lesson title, or lesson content …"
                        class="border rounded p-2" style="width: 300px;">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Published</label>
                    <select name="published" class="border rounded p-2">
                        <option value="" @selected(($filters['published'] ?? '') === '')>All</option>
                        <option value="1" @selected(($filters['published'] ?? '') === '1')>Published</option>
                        <option value="0" @selected(($filters['published'] ?? '') === '0')>Draft</option>
                    </select>
                </div>

                <button class="px-4 py-2 bg-gray-800 text-white rounded">Apply</button>

                @if (request()->hasAny(['q', 'published']))
                    <a href="{{ route('courses.index') }}" class="text-sm underline ml-2">Reset</a>
                @endif
            </form>
            <hr class="mb-6">

            @isset($myCourses)
                {{-- INSTRUCTOR VIEW --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">My Courses (Instructor)</h3>
                        <a href="{{ route('courses.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md">
                            + New Course
                        </a>
                    </div>

                    @if ($myCourses->isEmpty())
                        <p class="text-gray-600">You haven’t created any courses yet.</p>
                    @else
                        <div class="grid md:grid-cols-2 gap-4">
                            @foreach ($myCourses as $course)
                                <a class="border rounded p-4 hover:bg-gray-50 block"
                                    href="{{ route('courses.show', $course) }}">
                                    <div class="font-medium">{{ $course->title }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ $course->published ? '🟢 Published' : '🔘 Draft' }} •
                                        {{ $course->students_count }}
                                        students • {{ $course->lessons->count() }} lessons • <i>✍︎:
                                            {{ $course->instructor->name }}</i>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3">{{ $myCourses->links() }}</div>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-3">All Published Courses</h3>
                    @includeWhen(isset($published), 'courses.partials._grid', ['courses' => $published])
                </div>
            @else
                {{-- STUDENT VIEW --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold mb-3">My Enrolled Courses</h3>
                    @if ($enrolled->isEmpty())
                        <p class="text-gray-600">You are not enrolled in any courses yet.</p>
                    @else
                        @include('courses.partials._grid', ['courses' => $enrolled])
                        <div class="mt-3">{{ $enrolled->links() }}</div>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-3">Available Courses</h3>
                    @if ($available->isEmpty())
                        <p class="text-gray-600">No more courses available.</p>
                    @else
                        @include('courses.partials._grid_enrollable', ['courses' => $available])
                        <div class="mt-3">{{ $available->links() }}</div>
                    @endif
                </div>
            @endisset
        </div>
    </div>
</x-app-layout>
