<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course') }}: {{ $lesson->course->title }}<br />↳ {{ __('Lesson') }}: {{ $lesson->title }}
        </h2>
    </x-slot>
    <div class="max-w-5xl mx-auto py-8 space-6">
        @if (session('success'))
            <div class="bg-green-100 p-3">{{ session('success') }}</div>
        @endif

        <div class="flex justify-between items-center">
            <a href="{{ route('courses.show', $lesson->course) }}" class="text-sm text-blue-600 hover:underline mb-4">←
                Back
                to course</a>

            <a href="{{ route('lessons.edit', $lesson) }}" class="px-4 py-2 bg-black text-white rounded mb-4">Edit</a>
        </div>

        <h1 class="text-2xl font-semibold mt-4">{{ $lesson->title }}</h1>



        <article class="prose max-w-none">
            {!! nl2br(e($lesson->content)) !!}
        </article>

        <section>
            <h2 class="text-xl font-semibold mb-2">Comments</h2>

            @if ($lesson->comments->isEmpty())
                <p class="text-gray-600">No comments yet.</p>
            @else
                <div class="space-y-3">
                    @foreach ($lesson->comments as $c)
                        <div class="border rounded p-3">
                            <div class="text-sm text-gray-600 mb-1">
                                {{ $c->user->name }} • {{ $c->created_at->diffForHumans() }}
                            </div>
                            <div>{{ $c->body }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($lesson->attachment_path)
                <div class="mt-4 mb-4">
                    <a href="{{ route('lessons.attachment', $lesson) }}" class="text-blue-600 hover:underline">
                        ⬇ Download Attachment
                    </a>
                </div>
            @endif
        </section>

        @auth
            {{-- Only enrolled students or instructor can comment (enforced in controller) --}}
            <form method="POST" action="{{ route('lessons.comments.store', $lesson) }}" class="space-y-3">
                @csrf
                <textarea name="body" rows="3" class="w-full border rounded p-2" placeholder="Write a comment..."></textarea>
                @error('body')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
                <button class="px-4 py-2 bg-black text-white rounded">Post Comment</button>
            </form>
        @endauth
    </div>
</x-app-layout>
