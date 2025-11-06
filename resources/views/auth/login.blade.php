<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>

        </div>

        <hr class="mb-4 mt-4" />

        <div class="flex items-center justify-center mb-4">
            <a href="{{ route('register') }}"
                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ms-3">
                {{ __("Don't have an account? Register") }}
            </a>
        </div>

        <hr class="mb-4 mt-4" />

        <div class="panel panel-info">
            <div class="panel-heading font-semibold text-lg mb-2 ">Test Credentials</div>
            <div class="panel-body text-sm">
                <p><strong>Instructors:</strong><br>
                    Email: <code>instructor1@example.com</code><br>Password: <code>password</code><br><br>
                    Email: <code>instructor2@example.com</code><br>Password: <code>password</code>
                </p>

                <hr class="mt-4 mb-4 border-gray-300" />

                <p><strong>Students:</strong><br>
                    Email: <code>student1@example.com</code><br>Password: <code>password</code><br><br>
                    Email: <code>student2@example.com</code><br>Password: <code>password</code>
                </p>
            </div>

            <div class="text-center mt-6">
                <button id="runSeederBtn"
                    class="inline-flex border items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700 focus:outline-none">
                    ▶ Run Demo Seeder (migrate:fresh)
                </button>

                <div id="seedProgress" class="hidden mt-3 text-gray-700 text-sm">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span>Seeding demo data...</span>
                    </div>
                </div>

                <div id="seedResult" class="hidden mt-3 font-medium"></div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.getElementById('runSeederBtn').addEventListener('click', async function() {
                    const btn = this;
                    const progress = document.getElementById('seedProgress');
                    const result = document.getElementById('seedResult');

                    btn.disabled = true;
                    progress.classList.remove('hidden');
                    result.classList.add('hidden');

                    try {
                        const res = await fetch("{{ route('run.demo.seeder') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        progress.classList.add('hidden');
                        result.classList.remove('hidden');

                        if (data.success) {
                            result.classList.remove('text-red-600');
                            result.classList.add('text-green-600');
                            result.textContent = '✅ ' + data.message;

                            //to handle session CSRF token update after seeding
                            setTimeout(() => {
                                window.location.replace(window.location.href);
                            }, 1000);

                        } else {
                            result.classList.remove('text-green-600');
                            result.classList.add('text-red-600');
                            result.textContent = '❌ ' + data.message;
                        }
                    } catch (err) {
                        progress.classList.add('hidden');
                        result.classList.remove('hidden', 'text-green-600');
                        result.classList.add('text-red-600');
                        result.textContent = '⚠️ Error: ' + err.message;
                    } finally {
                        btn.disabled = false;
                    }
                });
            </script>
        @endpush
    </form>
</x-guest-layout>
