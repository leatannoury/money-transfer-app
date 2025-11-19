@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="flex h-screen">
    {{-- Sidebar --}}
    @include('components.agent-sidebar')

    {{-- Main Content --}}
    <main class="flex-1 overflow-y-auto">
        {{-- Header --}}
        <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
            <h1 class="text-2xl font-bold text-black dark:text-white">Agent Transactions</h1>

            <a href="{{ route('agent.dashboard') }}"
               class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium
                      bg-gray-200 dark:bg-gray-800 rounded-full text-gray-800 dark:text-gray-200
                      hover:bg-gray-300 dark:hover:bg-gray-700 transition">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Dashboard
            </a>
        </header>

        <div class="p-8">
            <div class="mx-auto max-w-6xl space-y-10">

                {{-- Alerts --}}
                @if(session('success'))
                    <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 p-4 rounded-lg">
                        {{ session('success') }}
                    </div>
                @elseif(session('error'))
                    <div class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 p-4 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <h2 class="text-3xl font-bold text-black dark:text-white">
                    Welcome, {{ $agent->name }}
                </h2>

                @php
                    $allEmpty =
                        ($userTransfers instanceof \Illuminate\Support\Collection ? $userTransfers->isEmpty() : $userTransfers->count() === 0)
                        && ($cashIns instanceof \Illuminate\Support\Collection ? $cashIns->isEmpty() : $cashIns->count() === 0)
                        && ($cashOuts instanceof \Illuminate\Support\Collection ? $cashOuts->isEmpty() : $cashOuts->count() === 0);
                @endphp

                @if($allEmpty)
                    <p class="text-gray-600 dark:text-gray-400 text-lg">
                        No transactions available yet.
                    </p>
                @else

                    {{-- =====================================================
                         1) USER ↔ USER TRANSFERS (via Agent)
                       ===================================================== --}}
                    <section>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xl font-semibold text-black dark:text-white">
                                Transfers (User ↔ User via Agent)
                            </h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                @if($userTransfers instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                                    {{ $userTransfers->total() }}
                                @else
                                    {{ $userTransfers->count() }}
                                @endif
                                transactions
                            </span>
                        </div>

                        @if($userTransfers->isEmpty())
                            <p class="text-sm text-gray-600 dark:text-gray-400 border border-dashed border-gray-300 rounded-lg p-4">
                                No user-to-user transfers handled yet.
                            </p>
                        @else
                            @include('agent.partials.transactions-table', [
                                'rows'        => $userTransfers,
                                'agent'       => $agent,
                                'showActions' => true,   {{-- Accept / Complete / Reject --}}
                            ])
                        @endif
                    </section>

                    {{-- =====================================================
                         2) CASH-IN OPERATIONS
                       ===================================================== --}}
                    <section>
                        <div class="flex items-center justify-between mb-3 mt-8">
                            <h3 class="text-xl font-semibold text-black dark:text-white">
                                Cash-In Operations
                            </h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                @if($cashIns instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                                    {{ $cashIns->total() }}
                                @else
                                    {{ $cashIns->count() }}
                                @endif
                                transactions
                            </span>
                        </div>

                        @if($cashIns->isEmpty())
                            <p class="text-sm text-gray-600 dark:text-gray-400 border border-dashed border-gray-300 rounded-lg p-4">
                                No cash-in operations yet.
                            </p>
                        @else
                            @include('agent.partials.transactions-table', [
                                'rows'        => $cashIns,
                                'agent'       => $agent,
                                'showActions' => false,  {{-- history only --}}
                            ])
                        @endif
                    </section>

                    {{-- =====================================================
                         3) CASH-OUT OPERATIONS
                       ===================================================== --}}
                    <section>
                        <div class="flex items-center justify-between mb-3 mt-8">
                            <h3 class="text-xl font-semibold text-black dark:text-white">
                                Cash-Out Operations
                            </h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                @if($cashOuts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                                    {{ $cashOuts->total() }}
                                @else
                                    {{ $cashOuts->count() }}
                                @endif
                                transactions
                            </span>
                        </div>

                        @if($cashOuts->isEmpty())
                            <p class="text-sm text-gray-600 dark:text-gray-400 border border-dashed border-gray-300 rounded-lg p-4">
                                No cash-out operations yet.
                            </p>
                        @else
                            @include('agent.partials.transactions-table', [
                                'rows'        => $cashOuts,
                                'agent'       => $agent,
                                'showActions' => false,  {{-- history only --}}
                            ])
                        @endif
                    </section>

                @endif
            </div>
        </div>
    </main>
</div>
@endsection
