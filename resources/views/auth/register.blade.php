
 <x-guest-layout>
    <!-- Social Login Buttons -->
    <div class="mb-6 text-center">
        <p class="text-sm text-gray-500 mb-2">Register with</p>
        <div class="flex justify-center space-x-3">
            <a href="{{ url('/auth/google') }}?mode=register" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded">
                Google
            </a>
          
            <a href="{{ url('/auth/facebook') }}?mode=register" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                Facebook
            </a>
        </div>
    </div>

    <!-- Divider -->
    <div class="flex items-center justify-center my-4">
        <div class="border-t border-gray-300 w-1/3"></div>
        <span class="mx-2 text-gray-500 text-sm">or</span>
        <div class="border-t border-gray-300 w-1/3"></div>
    </div>

    <!-- Breeze Registration Form -->
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number (+961)')" />
            <div class="relative">
                <div id="phone-group" class="inline-flex items-stretch w-full rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-primary bg-white">
                    <span class="px-3 flex items-center bg-gray-50 text-gray-500 text-sm select-none">+961</span>
                    <input
                        id="phone"
                        class="flex-1 px-3 py-2.5 outline-none border-0 focus:ring-0"
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                        autocomplete="tel"
                        placeholder="Enter 8 digits"
                        pattern="^\d{8}$"
                        maxlength="8"
                        inputmode="numeric"
                        title="Enter exactly 8 digits"
                    />
                    <span id="phone-status-success" class="mr-3 self-center hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414l2.293 2.293 6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <span id="phone-status-error" class="mr-3 self-center hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-5a1 1 0 112 0 1 1 0 01-2 0zm1-8a1 1 0 00-1 1v5a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                </div>
            </div>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>
        <script>
            (function () {
                const input = document.getElementById('phone');
                const okIcon = document.getElementById('phone-status-success');
                const errIcon = document.getElementById('phone-status-error');
                const group = document.getElementById('phone-group');
                if (!input) return;
                function updateState() {
                    const valid = /^\d{8}$/.test(input.value);
                    group?.classList.remove('ring-2','ring-red-500','ring-green-500','focus:ring-red-500','focus:ring-green-500');
                    okIcon?.classList.add('hidden');
                    errIcon?.classList.add('hidden');
                    if (input.value.length === 0) {
                        return;
                    }
                    if (valid) {
                        group?.classList.add('ring-2','ring-green-500','focus:ring-green-500');
                        okIcon?.classList.remove('hidden');
                    } else {
                        group?.classList.add('ring-2','ring-red-500','focus:ring-red-500');
                        errIcon?.classList.remove('hidden');
                    }
                }
                input.addEventListener('input', updateState);
                updateState();
            })();
        </script>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                type="password"
                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
