@extends('layouts.base')

@section('content')
    <div class="max-w-5xl mx-auto py-8">
        <h1 class="text-2xl font-semibold mb-4">
            Students enrolled in: "{{ $course->title }}"
        </h1>

        @if (session('success'))
            <div class="bg-green-100 p-3 mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 p-3 mb-4">{{ session('error') }}</div>
        @endif
        @if (session('info'))
            <div class="bg-blue-100 p-3 mb-4">{{ session('info') }}</div>
        @endif

        @if ($students->count() === 0)
            <p class="text-gray-600">No students enrolled yet.</p>
        @else
            <div class="overflow-x-auto border rounded">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Enrolled At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $i => $student)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $students->firstItem() + $i }}</td>
                                <td class="px-4 py-2">{{ $student->name }}</td>
                                <td class="px-4 py-2">{{ $student->email }}</td>
                                <td class="px-4 py-2">{{ $student->pivot->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $students->links() }}
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('courses.show', $course) }}" class="text-blue-600 hover:underline">← Back to course</a>
        </div>
    </div>
@endsection
