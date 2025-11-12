@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Settings - Transferly</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#000000",
            "background-light": "#f7f7f7",
            "background-dark": "#191919"
          },
          fontFamily: { display: "Manrope" },
        },
      },
    }
  </script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100">
<div class="flex h-screen">
  <!-- Sidebar -->
  @include('components.user-sidebar')

  <!-- Main Content -->
  <main class="flex-1 p-8 overflow-y-auto">
    <header class="flex justify-between items-center mb-10">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
      <div class="flex items-center gap-4">
        <button class="relative text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
          <span class="material-symbols-outlined">notifications</span>
          <span class="absolute top-0 right-0 w-2 h-2 bg-primary rounded-full"></span>
        </button>

      </div>
    </header>

    <div class="max-w-xl mx-auto bg-white dark:bg-gray-800 p-8 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="space-y-8">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Account</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Manage your payment preferences.</p>
        </div>

        <a href="{{ route('user.payment-methods.index') }}"
           class="w-full bg-transparent text-gray-900 dark:text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-between gap-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-4">
            <span class="material-symbols-outlined text-gray-500 dark:text-gray-400">credit_card</span>
            <span>Payment Methods</span>
          </div>
          <span class="material-symbols-outlined text-lg text-gray-400 dark:text-gray-500">chevron_right</span>
        </a>
      </div>
    </div>
  </main>
</div>
</body>
</html>
@endsection
