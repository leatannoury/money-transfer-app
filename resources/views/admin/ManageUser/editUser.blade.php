@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    <main class="flex-1 flex flex-col">
        <header class="flex items-center justify-center border-b px-8 py-4 bg-card-light dark:bg-card-dark">
            <h2 class="text-xl font-bold">Edit User</h2>
        </header>

        <div class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-xl mx-auto bg-card-light dark:bg-card-dark p-6 rounded-xl shadow-md">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf

                    <!-- Username -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium mb-1">Username</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                               class="w-full px-4 py-2 border border-border-light rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                               class="w-full px-4 py-2 border border-border-light rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium mb-1">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full px-4 py-2 border border-border-light rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium mb-1">New Password (optional)</label>
                        <input type="password" name="password" id="password"
                               class="w-full px-4 py-2 border border-border-light rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="w-full px-4 py-2 border border-border-light rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <div class="flex justify-between mt-4">
                        <a href="{{ route('admin.users') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-white hover:text-red-600 transition">
                            Back
                        </a>
                        <button type="submit" class="px-4 py-2 bg-primary text-white bg-blue-600 rounded-lg hover:bg-white text-blue-600 hover:text-blue-600 transition">
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
