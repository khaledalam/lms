<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    @php
        $icons = [
            'courses' => '📘',
            'published' => '✅',
            'drafts' => '📝',
            'unique students' => '👨‍🎓',
            'students' => '👨‍🎓',
            'lessons' => '🎬',
            'comments' => '💬',
            'with attachments' => '📎',
            'without attachments' => '📎',
            'enrolled' => '🎓',
            'attachments' => '📎',
        ];
    @endphp
    <div class="py-8 bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Personal Insights --}}
            <section class="mt-4">
                <h3 class="text-gray-700 font-semibold text-lg mb-4">Personal Insights</h3>

                <div class="!grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
                    @foreach ($cards as $title => $value)
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition p-4 flex flex-col justify-between min-h-[110px]">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-500 text-sm font-medium capitalize">{{ $title }}</span>
                                <span class="text-lg">{{ $icons[Str::lower($title)] ?? '📊' }}</span>
                            </div>
                            <div class="text-3xl font-semibold text-gray-800 leading-none mt-1">{{ $value }}
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($role === 'instructor')
                    {{-- Enrolled Students list --}}
                    <div class="mt-5 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-700">Enrolled Students</h4>
                            <span class="text-xs text-gray-500">Top by courses with you</span>
                        </div>

                        @if ($enrolledStudents->isEmpty())
                            <p class="text-sm text-gray-500">No students enrolled yet.</p>
                        @else
                            <ul class="divide-y divide-gray-100">
                                @foreach ($enrolledStudents as $stu)
                                    <li class="py-2 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 text-sm flex items-center justify-center">
                                                {{ Str::of($stu->name)->trim()->substr(0, 1)->upper() }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-800">{{ $stu->name }}</div>
                                                <a href="mailto:{{ $stu->email }}"
                                                    class="text-xs text-gray-500 hover:underline">
                                                    {{ $stu->email }}
                                                </a>
                                            </div>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                                            {{ $stu->courses_count }} course{{ $stu->courses_count == 1 ? '' : 's' }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </section>

            {{-- Platform Summary --}}
            <section class="mt-4">
                <h3 class="text-gray-700 font-semibold text-lg mb-4">Platform Summary</h3>

                <div class="!grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">


                    @foreach ($allCards as $title => $value)
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition p-4 flex flex-col justify-between min-h-[110px]">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-500 text-sm font-medium capitalize">{{ $title }}</span>
                                <span class="text-lg">{{ $icons[Str::lower($title)] ?? '📊' }}</span>
                            </div>
                            <div class="text-3xl font-semibold text-gray-800 leading-none mt-1">
                                {{ $value }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Charts Section --}}
            <section>
                <h3 class="text-gray-700 font-semibold text-lg mb-3">Analytics</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @if ($role === 'instructor')
                        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                            <h3 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">
                                Students per Course
                            </h3>
                            <div class="h-[280px]">
                                <canvas id="studentsPerCourse"></canvas>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                            <h3 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">
                                Comments (Last 14 Days)
                            </h3>
                            <div class="h-[280px]">
                                <canvas id="commentsPerDay"></canvas>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                            <h3 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">
                                Lessons per Enrolled Course
                            </h3>
                            <div class="h-[280px]">
                                <canvas id="lessonsPerCourse"></canvas>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                            <h3 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">
                                My Comments (Last 14 Days)
                            </h3>
                            <div class="h-[280px]">
                                <canvas id="myCommentsPerDay"></canvas>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @if ($role === 'instructor')
        <script>
            const baseOpts = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12
                        }
                    }
                }
            };

            new Chart(document.getElementById('studentsPerCourse'), {
                type: 'bar',
                data: {
                    labels: @json($chart_students_per_course['labels']),
                    datasets: [{
                        label: 'Students',
                        data: @json($chart_students_per_course['data']),
                        backgroundColor: 'rgba(59,130,246,0.5)',
                        borderColor: 'rgba(37,99,235,0.8)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                    }]
                },
                options: baseOpts
            });

            new Chart(document.getElementById('commentsPerDay'), {
                type: 'line',
                data: {
                    labels: @json($chart_comments_per_day['labels']),
                    datasets: [{
                        label: 'Comments',
                        data: @json($chart_comments_per_day['data']),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.15)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 2,
                    }]
                },
                options: baseOpts
            });
        </script>
    @else
        <script>
            const baseOpts = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12
                        }
                    }
                }
            };

            new Chart(document.getElementById('lessonsPerCourse'), {
                type: 'bar',
                data: {
                    labels: @json($chart_lessons_per_course['labels']),
                    datasets: [{
                        label: 'Lessons',
                        data: @json($chart_lessons_per_course['data']),
                        backgroundColor: 'rgba(16,185,129,0.4)',
                        borderColor: 'rgba(5,150,105,0.8)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                    }]
                },
                options: baseOpts
            });

            new Chart(document.getElementById('myCommentsPerDay'), {
                type: 'line',
                data: {
                    labels: @json($chart_my_comments_per_day['labels']),
                    datasets: [{
                        label: 'Comments',
                        data: @json($chart_my_comments_per_day['data']),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.15)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 2,
                    }]
                },
                options: baseOpts
            });
        </script>
    @endif
</x-app-layout>
