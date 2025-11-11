<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome | {{ config('app.name', 'MyApp') }}</title>

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: "#3B82F6" },
                        accent: "#10B981",
                        "background-light": "#F9FAFB",
                        "background-dark": "#111827",
                        "card-light": "#FFFFFF",
                        "card-dark": "#1F2937",
                        "text-light": "#1F2937",
                        "text-dark": "#F9FAFB",
                        "border-light": "#E5E7EB",
                        "border-dark": "#374151",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>

    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;
        }
    </style>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark min-h-screen flex flex-col">

    <!-- Redirect Logged-In Users -->
    @auth
        @php
            if (Auth::user()->role === 'admin') {
                header('Location: ' . route('admin.dashboard'));
                exit;
            } else {
                header('Location: ' . route('user.dashboard'));
                exit;
            }
        @endphp
    @endauth

   
    <main class="flex-1">
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-50 to-white dark:from-blue-900/20 dark:to-background-dark"></div>

            <div class="max-w-7xl mx-auto px-6 py-24 md:py-32 flex flex-col-reverse md:flex-row items-center gap-12 relative z-10">
                <!-- Left: Text -->
                <div class="flex-1 space-y-6 text-center md:text-left">
                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight text-text-light dark:text-text-dark">
                        Send Money Instantly.<br>
                        <span class="text-primary">Simple. Secure. Smart.</span>
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300 text-lg max-w-md mx-auto md:mx-0">
                        Manage your wallet, transfer funds globally, and stay in control of your finances with one seamless app experience.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start pt-4">
                        <a href="{{ route('login') }}" class="px-8 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-blue-600 transition">
                            Get Started
                        </a>
                        <a href="{{ route('register') }}" class="px-8 py-3 border border-primary text-primary font-semibold rounded-lg hover:bg-primary hover:text-white transition">
                            Create Account
                        </a>
                    </div>
                </div>

                <!-- Right: Illustration -->
                <div class="flex-1 flex justify-center md:justify-end">
                <div class="relative w-full max-w-md dark:bg-background-dark">
    <div class="absolute -top-10 -left-10 w-40 h-40 bg-primary/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-accent/10 rounded-full blur-3xl"></div>

    <img src="{{ asset('images/money.webp') }}" 
         alt="Money Transfer Illustration"
         class="relative z-10 rounded-2xl"> 
</div>


                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-card-light dark:bg-card-dark border-t border-border-light dark:border-border-dark">
            <div class="max-w-6xl mx-auto px-6 text-center">
                <h2 class="text-3xl font-bold mb-10 text-text-light dark:text-text-dark">Why Choose Us?</h2>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-6 rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark shadow-sm hover:shadow-lg transition">
                        <span class="material-symbols-outlined text-primary text-4xl mb-3">bolt</span>
                        <h3 class="text-xl font-semibold mb-2">Instant Transfers</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Send and receive money instantly with real-time balance updates.</p>
                    </div>
                    <div class="p-6 rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark shadow-sm hover:shadow-lg transition">
                        <span class="material-symbols-outlined text-primary text-4xl mb-3">lock</span>
                        <h3 class="text-xl font-semibold mb-2">Bank-Level Security</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Your data and funds are protected with advanced encryption and 2FA.</p>
                    </div>
                    <div class="p-6 rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark shadow-sm hover:shadow-lg transition">
                        <span class="material-symbols-outlined text-primary text-4xl mb-3">trending_up</span>
                        <h3 class="text-xl font-semibold mb-2">Track & Control</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Monitor your transactions, analytics, and spending with insights.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark text-center py-6 text-sm text-gray-500 dark:text-gray-400">
        &copy; {{ date('Y') }} {{ config('app.name', 'MyApp') }}. All rights reserved.
    </footer>

</body>
</html>
