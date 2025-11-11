@extends('layouts.app')

@section('content')
<html class="light" lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Laravel App</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary": { DEFAULT: "#3B82F6" },
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
              "display": ["Inter", "sans-serif"],
            },
          },
        },
      }
    </script>
  </head>
  <body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">

    <div class="min-h-screen flex flex-col items-center justify-center text-center px-6">
      <!-- Hero Section -->
      <div class="max-w-3xl space-y-6">
        <div class="flex justify-center mb-4">
          <div class="bg-primary text-white p-3 rounded-lg">
            <span class="material-symbols-outlined text-3xl">bolt</span>
          </div>
        </div>
        <h1 class="text-4xl md:text-5xl font-bold text-text-light dark:text-text-dark">
          Welcome to Your Laravel App ⚡
        </h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl mx-auto">
          Build modern and powerful web applications faster than ever with Laravel and TailwindCSS.
        </p>
        <div class="flex justify-center gap-4 pt-4">
          <a href="{{ route('login') }}" 
             class="px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-blue-600 transition">
             Get Started
          </a>
          <a href="https://laravel.com/docs" target="_blank"
             class="px-6 py-3 border border-border-light dark:border-border-dark rounded-lg font-semibold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
             Documentation
          </a>
        </div>
      </div>

      <!-- Features Section -->
      <div class="grid md:grid-cols-3 gap-6 mt-16 max-w-6xl w-full">
        <div class="p-6 bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark">
          <span class="material-symbols-outlined text-primary text-4xl mb-3">speed</span>
          <h3 class="text-xl font-semibold mb-2">Lightning Fast</h3>
          <p class="text-gray-600 dark:text-gray-400 text-sm">Optimized for speed, simplicity, and clean code.</p>
        </div>
        <div class="p-6 bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark">
          <span class="material-symbols-outlined text-primary text-4xl mb-3">security</span>
          <h3 class="text-xl font-semibold mb-2">Secure & Reliable</h3>
          <p class="text-gray-600 dark:text-gray-400 text-sm">Built with robust authentication and CSRF protection.</p>
        </div>
        <div class="p-6 bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark">
          <span class="material-symbols-outlined text-primary text-4xl mb-3">construction</span>
          <h3 class="text-xl font-semibold mb-2">Developer Friendly</h3>
          <p class="text-gray-600 dark:text-gray-400 text-sm">Extensible, modular, and ready for anything.</p>
        </div>
      </div>

      <!-- Footer -->
      <footer class="mt-16 text-gray-500 dark:text-gray-400 text-sm">
        <p>Made with ❤️ using Laravel & TailwindCSS</p>
      </footer>
    </div>

  </body>
</html>
@endsection
