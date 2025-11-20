<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Money Transfer App</title>
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
<body class="min-h-screen bg-gray-100 flex flex-col justify-center sm:py-12">

    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-400 to-sky-500 shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl"></div>
        <div class="relative px-4 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20">

            <div class="max-w-md mx-auto">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-semibold">Login</h1>
                    <p class="text-gray-500 mt-1">Sign in to your account</p>
                </div>

    <!-- Social Login Buttons -->
    <div class="mb-6 text-center">
        <p class="text-sm text-gray-500 mb-2">Log in with</p>
        <div class="flex justify-center space-x-3">
            <a href="{{ url('/auth/google') }}?mode=login"
               class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded">
                Google
            </a>
            <a href="{{ url('/auth/facebook') }}?mode=login"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                Facebook
            </a>
        </div>
    </div>

                <!-- Divider -->
                <div class="flex items-center my-6">
                    <div class="flex-grow border-t border-gray-300"></div>
                    <span class="mx-4 text-gray-500 text-sm">OR</span>
                    <div class="flex-grow border-t border-gray-300"></div>
                </div>

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
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

                    <!-- Password -->
                    <div class="relative">
                        <input type="password" name="password" id="password" autocomplete="current-password" required
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

                    <!-- Submit -->
                    <div class="relative">
                        <button type="submit" class="bg-cyan-500 text-white rounded-md px-4 py-2 w-full hover:bg-cyan-600 transition">Sign In</button>
                    </div>
                </form>

                <!-- Forgot Password -->
                <div class="mt-4 text-center">
                    <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-gray-900 hover:underline">Forgot your password?</a>
                </div>

                <!-- Register Link -->
                <div class="mt-4 text-center">
                    <p class="text-gray-600">Don't have an account? 
                        <a href="{{ route('register') }}" class="text-cyan-600 hover:text-cyan-800 font-medium hover:underline">Register</a>
                    </p>
                </div>

            </div>

        </div>
    </div>
</body>
</html>
