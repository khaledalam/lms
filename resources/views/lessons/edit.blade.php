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

        <x-back-button :url="route('courses.index')">Go Back</x-back-button>

        <form method="POST" action="{{ route('lessons.update', $lesson) }}" enctype="multipart/form-data"
            {{-- important for files --}} class="space-y-4">
            @csrf
            @method('PUT')

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
                @error('content')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            {{-- Current attachment (if any) --}}
            @if ($lesson->attachment_path)
                <div class="p-3 border rounded bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">Current attachment:</p>
                            <a href="{{ route('lessons.attachment', $lesson) }}"
                                class="text-blue-600 hover:underline">Download current file</a>
                        </div>
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="remove_attachment" value="0">
                            <input type="checkbox" name="remove_attachment" value="1">
                            <span class="text-sm">Remove attachment</span>
                        </label>
                    </div>
                </div>
            @endif

            {{-- Replace / upload new attachment --}}
            <div>
                <label class="block mb-1 font-medium">Replace / Upload Attachment (optional)</label>
                <input type="file" name="attachment" class="block w-full border rounded p-2">
                <p class="text-xs text-gray-500 mt-1">Max 10MB.</p>
                @error('attachment')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <button class="px-4 py-2 bg-black text-white rounded">Update</button>
        </form>
    </div>
</x-app-layout>
