<div class="grid md:grid-cols-3 gap-4">
    @foreach ($courses as $course)
        <div class="border rounded p-4 flex flex-col justify-between">
            <div>
                <div class="font-medium">{{ $course->title }}</div>
                <div class="text-sm text-gray-600 mb-3">
                    {{ $course->published ? '🟢 Published' : '🔘 Draft' }} • {{ $course->students_count }} students •
                    {{ $course->lessons_count }} lessons
                </div>
                <p class="text-sm text-gray-700 line-clamp-3">{{ Str::limit($course->description, 140) }}</p>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <a href="{{ route('courses.show', $course) }}" class="px-3 py-2 border rounded">Details</a>

                <form method="POST" action="{{ route('courses.enroll', $course) }}" class="inline">
                    @csrf
                    <button class="px-3 py-2 bg-gray-800 text-white rounded">Enroll</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
