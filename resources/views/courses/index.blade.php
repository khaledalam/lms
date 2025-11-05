<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Courses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success')) <div class="bg-green-100 p-3 rounded mb-4">{{ session('success') }}</div> @endif

            @isset($myCourses)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">My Courses (Instructor)</h3>
                        <a href="{{ route('courses.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md">
                            + New Course
                        </a>
                    </div>
                    @if($myCourses->isEmpty())
                        <p class="text-gray-600">You haven’t created any courses yet.</p>
                    @else
                        <div class="grid md:grid-cols-2 gap-4">
                            @foreach($myCourses as $course)
                                <a class="border rounded p-4 hover:bg-gray-50 block" href="{{ route('courses.show', $course) }}">
                                    <div class="font-medium">{{ $course->title }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ $course->published ? 'Published' : 'Draft' }} • {{ $course->students_count }} students
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-3">All Published Courses</h3>
                    @include('courses.partials._grid', ['courses' => $courses])
                </div>
            @endisset
        </div>
    </div>
</x-app-layout>