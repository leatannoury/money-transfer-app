@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cash Operations - Transferly</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>

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
    @include('components.agent-sidebar')

    <main class="flex-1 overflow-y-auto">
        <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
            <div>
                <h1 class="text-2xl font-bold text-black dark:text-white">Cash Operations</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Logged in as: <span class="font-semibold">{{ $agent->name }}</span>
                </p>
            </div>

            <a href="{{ route('agent.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Back to Dashboard</span>
            </a>
        </header>

        <div class="p-8">
            <div class="mx-auto max-w-3xl">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-10 shadow-sm text-center">
                    <h2 class="text-2xl font-bold mb-6 text-black dark:text-white">Choose Operation</h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        <a href="{{ route('agent.cash.in.form') }}"
                           class="flex items-center justify-center h-32 rounded-2xl bg-black text-white text-xl font-bold hover:opacity-90 transition">
                            CASH-IN
                        </a>

                        <a href="{{ route('agent.cash.out.form') }}"
                           class="flex items-center justify-center h-32 rounded-2xl bg-white text-black border border-gray-300 text-xl font-bold hover:bg-gray-100 transition">
                            CASH-OUT
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
@endsection
