<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'LMS' }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <nav class="bg-white shadow p-4 mb-6">
        <a href="{{ route('courses.index') }}" class="font-bold">Courses</a>
        @auth
            <span class="ml-4 text-gray-600">{{ auth()->user()->name }}</span>
        @endauth
    </nav>

    <main class="px-4">
        @yield('content')
    </main>
</body>
</html>