<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Money Transfer App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .peer:placeholder-shown ~ label {
            top: 0.5rem;
            font-size: 1rem;
            color: #9ca3af;
        }
        label {
            transition: all 0.2s ease-out;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 flex flex-col justify-center sm:py-10">

    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-400 to-sky-500 shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl"></div>
        <div class="relative px-4 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20">

            <div class="max-w-md mx-auto">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-semibold">Register</h1>
                    <p class="text-gray-500 mt-1">Create your account</p>
                </div>

                <!-- Social Registration Buttons -->
                <div class="flex flex-col gap-3 mb-6">
                    <a href="{{ url('/auth/google') }}" 
                        class="flex items-center justify-center gap-2 bg-white border border-gray-300 rounded-lg shadow-md px-6 py-2 text-sm font-medium text-gray-800 hover:bg-gray-200 transition">
                        <i class="fab fa-google fa-lg"></i>
                        Continue with Google
                    </a>

                    <a href="{{ url('/auth/facebook') }}" 
                        class="flex items-center justify-center gap-2 bg-blue-600 text-white rounded-lg shadow-md px-6 py-2 text-sm font-medium hover:bg-blue-700 transition">
                        <i class="fab fa-facebook fa-lg"></i>
                        Continue with Facebook
                    </a>
                </div>

                <!-- Divider -->
                <div class="flex items-center my-6">
                    <div class="flex-grow border-t border-gray-300"></div>
                    <span class="mx-4 text-gray-500 text-sm">OR</span>
                    <div class="flex-grow border-t border-gray-300"></div>
                </div>

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    <!-- Name -->
                    <div class="relative">
                        <input type="text" name="name" id="name" autocomplete="name" required
                               class="peer placeholder-transparent h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-cyan-500"
                               placeholder="Full Name" value="{{ old('name') }}">
                        <label for="name"
                               class="absolute left-0 -top-3.5 text-gray-600 text-sm peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 transition-all">
                            Full Name
                        </label>
                        @error('name')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="relative">
                        <input type="email" name="email" id="email" autocomplete="username" required
                               class="peer placeholder-transparent h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-cyan-500"
                               placeholder="Email address" value="{{ old('email') }}">
                        <label for="email"
                               class="absolute left-0 -top-3.5 text-gray-600 text-sm peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 transition-all">
                            Email Address
                        </label>
                        @error('email')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                <!-- Phone Number -->
            <div class="input-field relative bg-gray-50 border border-gray-300 rounded-lg transition-all duration-300 mt-4">
                <label for="phone" class="block text-xs text-gray-500 px-4 pt-3">Phone Number (+961)</label>
                <div id="phone-group" class="inline-flex items-stretch w-full rounded-lg overflow-hidden bg-white focus-within:ring-2 focus-within:ring-cyan-500">
                    <span class="px-3 flex items-center bg-gray-50 text-gray-500 text-sm select-none">+961</span>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required placeholder="Enter 8 digits"
                        maxlength="8" pattern="^\d{8}$" inputmode="numeric" title="Enter exactly 8 digits"
                        class="flex-1 px-3 py-2 outline-none border-0 focus:ring-0">
                </div>
                @error('phone')
                    <p class="text-red-600 text-xs px-4 mt-1">{{ $message }}</p>
                @enderror
            </div>


                    <!-- Password -->
                    <div class="relative">
                        <input type="password" name="password" id="password" autocomplete="new-password" required
                               class="peer placeholder-transparent h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-cyan-500"
                               placeholder="Password">
                        <label for="password"
                               class="absolute left-0 -top-3.5 text-gray-600 text-sm peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 transition-all">
                            Password
                        </label>
                        @error('password')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" required
                               class="peer placeholder-transparent h-10 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-cyan-500"
                               placeholder="Confirm Password">
                        <label for="password_confirmation"
                               class="absolute left-0 -top-3.5 text-gray-600 text-sm peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 transition-all">
                            Confirm Password
                        </label>
                        @error('password_confirmation')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <div class="relative">
                        <button type="submit" class="bg-cyan-500 text-white rounded-md px-4 py-2 w-full hover:bg-cyan-600 transition">Register</button>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="mt-4 text-center">
                    <p class="text-gray-600">Already have an account? 
                        <a href="{{ route('login') }}" class="text-cyan-600 hover:text-cyan-800 font-medium hover:underline">Login</a>
                    </p>
                </div>

            </div>

        </div>
    </div>

</body>
</html>
