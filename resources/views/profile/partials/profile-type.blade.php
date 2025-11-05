<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Type') }}
        </h2>

        <h2 class="mt-1 text-sm text-gray-600">
            You are: {{ $user->isStudent() ? 'Student 🧑‍🎓' : ($user->isInstructor() ? 'Instructor 🧑‍🏫' : 'N/A') }}
        </h2>
    </header>

</section>
