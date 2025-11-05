<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lesson') }}: {{ $lesson->title }}
        </h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 space-6">
        @if (session('success'))
            <div class="bg-green-100 p-3">{{ session('success') }}</div>
        @endif
        <h1 class="text-2xl font-semibold mb-4">Edit Lesson – {{ $course->title }}</h1>

        <form method="POST" action="{{ route('lessons.update', $lesson) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block mb-1 font-medium">Title</label>
                <input name="title" class="w-full border rounded p-2" value="{{ old('title', $lesson->title) }}"
                    required>
                @error('title')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-medium">Content</label>
                <textarea name="content" class="w-full border rounded p-2" rows="6">{{ old('content', $lesson->content) }}</textarea>
            </div>
            <button class="px-4 py-2 bg-black text-white rounded">Update</button>
            <x-back-button :url="route('lesson§.index')">Go Back</x-back-button>
        </form>
    </div>
</x-app-layout>
