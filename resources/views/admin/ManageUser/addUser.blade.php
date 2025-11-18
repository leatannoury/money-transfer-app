@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="flex min-h-screen">
    <!-- Sidebar -->
 

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <header class="flex items-center justify-center border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
            <h2 class="text-xl font-bold">Add User</h2>
          
        </header>

        <div class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-xl mx-auto bg-card-light dark:bg-card-dark p-6 rounded-xl shadow-md">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                <!-- Username -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium mb-1">Username</label>
                    <input type="text" name="name" id="name" placeholder="Enter username"
                        value="{{ old('name') }}"
                        class="w-full px-4 py-2 border @error('name') border-red-500 @else border-border-light @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" id="email" placeholder="Enter email"
                        value="{{ old('email') }}"
                        class="w-full px-4 py-2 border @error('email') border-red-500 @else border-border-light @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter password"
                        class="w-full px-4 py-2 border @error('password') border-red-500 @else border-border-light @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    @error('password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm password"
                        class="w-full px-4 py-2 border @error('password_confirmation') border-red-500 @else border-border-light @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    @error('password_confirmation')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone Number (+961 prefix) -->
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium mb-1">Phone Number (+961)</label>
                    <div class="relative">
                        <div id="admin-user-phone-group" class="inline-flex items-stretch w-full rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-primary bg-white">
                            <span class="px-3 flex items-center bg-gray-50 text-gray-500 text-sm select-none">+961</span>
                            <input type="text" name="phone" id="phone" placeholder="Enter 8 digits"
                                value="{{ old('phone') }}"
                                pattern="^\d{8}$"
                                maxlength="8"
                                inputmode="numeric"
                                class="flex-1 px-3 py-2.5 outline-none border-0 focus:ring-0 @error('phone') ring-2 ring-red-500 @enderror">
                            <span id="phone-status" class="material-symbols-outlined mr-3 self-center text-lg hidden">check_circle</span>
                        </div>
                    </div>
                    @error('phone')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <script>
                    (function () {
                        const input = document.getElementById('phone');
                        const statusIcon = document.getElementById('phone-status');
                        const group = document.getElementById('admin-user-phone-group');
                        if (!input) return;
                        function updateState() {
                            const valid = /^\d{8}$/.test(input.value);
                            group?.classList.remove('ring-2','ring-red-500','ring-green-500','focus:ring-red-500','focus:ring-green-500');
                            if (input.value.length === 0) {
                                statusIcon?.classList.add('hidden');
                                return;
                            }
                            if (valid) {
                                group?.classList.add('ring-2','ring-green-500','focus:ring-green-500');
                                if (statusIcon) {
                                    statusIcon.textContent = 'check_circle';
                                    statusIcon.classList.remove('hidden');
                                    statusIcon.classList.remove('text-red-500');
                                    statusIcon.classList.add('text-green-500');
                                }
                            } else {
                                group?.classList.add('ring-2','ring-red-500','focus:ring-red-500');
                                if (statusIcon) {
                                    statusIcon.textContent = 'error';
                                    statusIcon.classList.remove('hidden');
                                    statusIcon.classList.remove('text-green-500');
                                    statusIcon.classList.add('text-red-500');
                                }
                            }
                        }
                        input.addEventListener('input', updateState);
                        updateState();
                    })();
                </script>

                    <!-- Submit Button -->
                    <div class="flex justify-between mt-4">
                            <a href="{{ route('admin.users') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-white hover:text-red-600 transition">
                            Cancle
                        </a>

                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg bg-blue-600 hover:text-blue-600 hover:bg-white transition">
                            Add User
                        </button>
                        
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


@endsection
