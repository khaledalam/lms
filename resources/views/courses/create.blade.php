<x-app-layout>

    <div class="max-w-5xl mx-auto py-8 space-6">
        @if (session('success'))
            <div class="bg-green-100 p-3">{{ session('success') }}</div>
        @endif
        <h1 class="text-2xl font-semibold mb-4">Create Course</h1>

        <form method="POST" action="{{ route('courses.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block mb-1 font-medium">Title</label>
                <input name="title" class="w-full border rounded p-2" required>
                @error('title')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-medium">Description</label>
                <textarea name="description" class="w-full border rounded p-2" rows="4"></textarea>
            </div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="published" value="1">
                <span>Published</span>
            </label>

            <button class="px-4 py-2 bg-black text-white rounded">Save</button>
            <x-back-button :url="route('courses.index')">Go Back</x-back-button>
        </form>
    </div>
</x-app-layout>
