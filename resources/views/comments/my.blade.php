<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Comments') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($comments->isEmpty())
                    <p class="text-gray-600">You haven't posted any comments yet.</p>
                    <x-back-button :url="route('courses.index')" class="mt-4">Back to courses</x-back-button>
                @else
                    <div class="space-y-5">
                        @foreach ($comments as $c)
                            <div class="border rounded p-4">
                                <div class="text-sm text-gray-500 flex items-center justify-between">
                                    <div>
                                        <span>On lesson:</span>
                                        <a class="text-blue-600 hover:underline"
                                            href="{{ route('lessons.show', $c->lesson_id) }}">
                                            {{ $c->lesson->title ?? 'Lesson' }}
                                        </a>
                                        <span class="mx-1">•</span>
                                        <span>Course:</span>
                                        <a class="text-blue-600 hover:underline"
                                            href="{{ route('courses.show', $c->lesson->course->id ?? null) }}">
                                            {{ $c->lesson->course->title ?? 'Course' }}
                                        </a>
                                    </div>
                                    <time datetime="{{ $c->created_at }}">
                                        {{ $c->created_at->diffForHumans() }}
                                    </time>
                                </div>
                                <p class="mt-2 whitespace-pre-line">{{ $c->body }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $comments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
