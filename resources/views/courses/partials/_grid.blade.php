@if ($courses->isEmpty())
    <p class="text-gray-600">No courses found.</p>
@else
    <div class="grid md:grid-cols-3 gap-4">
        @foreach ($courses as $course)
            <a class="border rounded p-4 hover:bg-gray-50 block" href="{{ route('courses.show', $course) }}">
                <div class="font-medium">{{ $course->title }}</div>
                <div class="text-sm text-gray-600">
                    {{ $course->published ? 'Published' : 'Draft' }}
                    @isset($course->students_count)
                        • {{ $course->students_count }} students
                    @endisset
                </div>
            </a>
        @endforeach
    </div>
    <div class="mt-3">{{ $courses->links() }}</div>
@endif
