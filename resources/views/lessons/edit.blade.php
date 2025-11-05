@extends('layouts.base')

@section('content')
    <div class="max-w-3xl mx-auto py-8">
        <h1 class="text-2xl font-semibold mb-4">Edit Lesson – {{ $course->title }}</h1>

        <form method="POST" action="{{ route('lessons.update', $lesson) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block mb-1 font-medium">Title</label>
                <input name="title" class="w-full border rounded p-2" value="{{ old('title', $lesson->title) }}" required>
                @error('title')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-medium">Content</label>
                <textarea name="content" class="w-full border rounded p-2" rows="6">{{ old('content', $lesson->content) }}</textarea>
            </div>
            <button class="px-4 py-2 bg-black text-white rounded">Update</button>
        </form>
    </div>
@endsection
