<div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm">
    <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
        <thead class="bg-gray-100 dark:bg-gray-800 uppercase text-xs tracking-wider">
        <tr>
            <th class="px-6 py-3">ID</th>
            <th class="px-6 py-3">Sender</th>
            <th class="px-6 py-3">Receiver</th>
            <th class="px-6 py-3">Amount</th>
            <th class="px-6 py-3">Agent Commission</th>
            <th class="px-6 py-3">Currency</th>
            <th class="px-6 py-3">Status</th>
            @if(!empty($showActions))
                <th class="px-6 py-3">Action</th>
            @endif
            <th class="px-6 py-3">Created At</th>
        </tr>
        </thead>

        <tbody>
        @foreach($rows as $tx)
            @php
                // ----------------------------------
                // Commission logic
                // ----------------------------------
                $commissionAmount      = 0;
                $commissionRateDisplay = null;
                $currency              = $tx->currency ?? 'USD';

                if ($tx->status !== 'failed' && $tx->agent_id === $agent->id) {

                    // Fixed 0.5% for cash-in / cash-out
                    if (in_array($tx->service_type, ['cash_in', 'cash_out'], true)) {
                        $commissionAmount      = $tx->amount * 0.005;
                        $commissionRateDisplay = '0.5%';

                    // Normal transfers → agent % from profile
                    } else {
                        $commissionRate = $agent->commission ?? 0;
                        if ($commissionRate > 0) {
                            $commissionAmount      = ($tx->amount * $commissionRate) / 100;
                            $commissionRateDisplay = rtrim(rtrim(number_format($commissionRate, 2), '0'), '.') . '%';
                        }
                    }
                }
            @endphp

            <tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/40">
                {{-- ID --}}
                <td class="px-6 py-3">{{ $tx->id }}</td>

                {{-- Sender / Receiver --}}
                <td class="px-6 py-3">{{ $tx->sender->name ?? 'N/A' }}</td>
                <td class="px-6 py-3">{{ $tx->receiver->name ?? 'N/A' }}</td>

                {{-- Amount --}}
                <td class="px-6 py-3 font-semibold">
                    {{ \App\Services\CurrencyService::format($tx->amount, $currency) }}
                </td>

                {{-- Agent Commission --}}
                <td class="px-6 py-3">
                    @if($commissionRateDisplay !== null)
                        {{ \App\Services\CurrencyService::format($commissionAmount, $currency) }}
                        <span class="text-xs text-gray-500">
                            ({{ $commissionRateDisplay }})
                        </span>
                    @else
                        <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>

                {{-- Currency --}}
                <td class="px-6 py-3">{{ $currency }}</td>

                {{-- Status --}}
                <td class="px-6 py-3">
                    <span class="
                        px-3 py-1 rounded-full text-xs font-semibold
                        @if($tx->status === 'pending_agent') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400
                        @elseif($tx->status === 'in_progress') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                        @elseif($tx->status === 'completed') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                        @elseif($tx->status === 'failed') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                        @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $tx->status)) }}
                    </span>
                </td>

                {{-- Actions (only when showActions = true) --}}
                @if(!empty($showActions))
                    <td class="px-6 py-3">
                        @if($tx->status === 'pending_agent')
                            <form action="{{ route('agent.accept', $tx->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-semibold">
                                    Accept
                                </button>
                            </form>

                        @elseif($tx->status === 'in_progress' && $tx->agent_id === $agent->id)
                            <div class="flex gap-2">
                                <form action="{{ route('agent.complete', $tx->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-semibold">
                                        Complete
                                    </button>
                                </form>

                                <form action="{{ route('agent.reject', $tx->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-semibold">
                                        Reject
                                    </button>
                                </form>
                            </div>

                        @else
                            <button class="bg-gray-400 text-white px-4 py-2 rounded-lg text-xs font-semibold cursor-not-allowed"
                                    disabled>
                                No Action
                            </button>
                        @endif
                    </td>
                @endif

                {{-- Created At --}}
                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">
                    {{ $tx->created_at ? $tx->created_at->format('Y-m-d H:i') : 'N/A' }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Pagination footer (only when $rows is paginator) --}}
    @if($rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400">
            <div>
                Showing
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $rows->firstItem() }}</span>
                to
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $rows->lastItem() }}</span>
                of
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $rows->total() }}</span>
                results
            </div>
            <div class="flex items-center">
                {{ $rows->onEachSide(1)->links() }}
            </div>
        </div>
    @endif
</div>
