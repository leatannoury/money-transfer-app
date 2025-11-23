<div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-100 dark:border-white/10 rounded-2xl shadow-sm">
    <table class="min-w-full table-auto text-sm text-left text-gray-700 dark:text-gray-200">
        <thead class="bg-gray-50 dark:bg-gray-800/80 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
        <tr>
            <th class="px-5 py-3 rounded-tl-2xl">ID</th>
            <th class="px-5 py-3">Sender</th>
            <th class="px-5 py-3">Receiver</th>
            <th class="px-5 py-3 text-right">Amount</th>
            <th class="px-5 py-3 text-right">Agent Commission</th>
            <th class="px-5 py-3">Currency</th>
            <th class="px-5 py-3">Status</th>
            @if(!empty($showActions))
                <th class="px-5 py-3 text-center">Action</th>
            @endif
            <th class="px-5 py-3 rounded-tr-2xl">Created At</th>
        </tr>
        </thead>

        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($rows as $tx)
            @php
                $commissionAmount      = 0;
                $commissionRateDisplay = null;
                $currency              = $tx->currency ?? 'USD';

                if ($tx->status !== 'failed' && $tx->agent_id === $agent->id) {

                    // Fixed 0.5% for cash-in / cash-out
                    if (in_array($tx->service_type, ['cash_in', 'cash_out'], true)) {
                        $commissionAmount      = $tx->amount * 0.005;
                        $commissionRateDisplay = '0.5%';

                    // Normal transfers / cash_pickup → agent % from profile
                    } else {
                        $commissionRate = $agent->commission ?? 0;
                        if ($commissionRate > 0) {
                            $commissionAmount      = ($tx->amount * $commissionRate) / 100;
                            $commissionRateDisplay = rtrim(rtrim(number_format($commissionRate, 2), '0'), '.') . '%';
                        }
                    }
                }
            @endphp

            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/60 transition-colors">
                {{-- ID --}}
                <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    #{{ $tx->id }}
                </td>

                {{-- Sender --}}
                <td class="px-5 py-3 whitespace-nowrap">
                    <span class="font-medium text-gray-800 dark:text-gray-100">
                        {{ $tx->sender->name ?? 'N/A' }}
                    </span>
                </td>

                {{-- Receiver / beneficiary --}}
                <td class="px-5 py-3">
                    @if(in_array($tx->service_type, ['cash_pickup', 'deposit_to_person']))
                        <div class="space-y-0.5">
                            <div class="font-medium text-gray-800 dark:text-gray-100">
                                {{ $tx->recipient_name ?? 'N/A' }}
                            </div>
                            @if($tx->recipient_phone)
                                <div class="text-[11px] text-gray-500 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] leading-none">call</span>
                                    <span>{{ $tx->recipient_phone }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <span class="font-medium text-gray-800 dark:text-gray-100">
                            {{ $tx->receiver->name ?? 'N/A' }}
                        </span>
                    @endif
                </td>

                {{-- Amount --}}
                <td class="px-5 py-3 text-right font-semibold tabular-nums">
                    {{ \App\Services\CurrencyService::format($tx->amount, $currency) }}
                </td>

                {{-- Agent Commission --}}
                <td class="px-5 py-3 text-right tabular-nums">
                    @if($commissionRateDisplay !== null)
                        <span class="font-medium">
                            {{ \App\Services\CurrencyService::format($commissionAmount, $currency) }}
                        </span>
                        <span class="text-[11px] text-gray-500 ml-1">
                            ({{ $commissionRateDisplay }})
                        </span>
                    @else
                        <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>

                {{-- Currency --}}
                <td class="px-5 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">
                    {{ $currency }}
                </td>

                {{-- Status --}}
                <td class="px-5 py-3">
                    <span class="
                        inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold
                        @if($tx->status === 'pending_agent') bg-yellow-50 text-yellow-700 border border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800
                        @elseif($tx->status === 'in_progress') bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800
                        @elseif($tx->status === 'completed') bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800
                        @elseif($tx->status === 'failed') bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800
                        @else bg-gray-50 text-gray-700 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $tx->status)) }}
                    </span>
                </td>

                {{-- Actions --}}
                @if(!empty($showActions))
                    <td class="px-5 py-3">
                        @if($tx->status === 'pending_agent')
                            <form action="{{ route('agent.accept', $tx->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-xs font-semibold
                                               bg-blue-600 text-white hover:bg-blue-700 shadow-sm">
                                    Accept
                                </button>
                            </form>

                        @elseif($tx->status === 'in_progress' && $tx->agent_id === $agent->id)

                            {{-- CASH PICKUP → phone + complete + separate reject form --}}
                            @if($tx->service_type === 'cash_pickup')
                                <form action="{{ route('agent.complete', $tx->id) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <div>
                                        <input type="text"
                                               name="recipient_phone"
                                               class="w-44 rounded-lg border border-gray-300 px-3 py-1.5 text-xs
                                                      focus:outline-none focus:ring-1 focus:ring-black
                                                      dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"
                                               placeholder="Recipient phone"
                                               value="{{ old('recipient_phone') }}"
                                               maxlength="8"
                                               pattern="\d{8}"
                                               inputmode="numeric"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                                               required>
                                        @error('recipient_phone')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-full px-3 py-1.5 rounded-full text-xs font-semibold
                                                   bg-green-600 text-white hover:bg-green-700 shadow-sm">
                                        Complete
                                    </button>
                                </form>

                                <form action="{{ route('agent.reject', $tx->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-full px-3 py-1.5 rounded-full text-xs font-semibold
                                                   bg-red-600 text-white hover:bg-red-700 shadow-sm">
                                        Reject
                                    </button>
                                </form>

                            {{-- Normal transfers --}}
                            @else
                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <form action="{{ route('agent.complete', $tx->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-xs font-semibold
                                                       bg-green-600 text-white hover:bg-green-700 shadow-sm w-full">
                                            Complete
                                        </button>
                                    </form>

                                    <form action="{{ route('agent.reject', $tx->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-xs font-semibold
                                                       bg-red-600 text-white hover:bg-red-700 shadow-sm w-full">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @endif

                        @else
                            <button class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-xs font-semibold
                                           bg-gray-300 text-gray-700 dark:bg-gray-700 dark:text-gray-300 cursor-not-allowed w-full">
                                No Action
                            </button>
                        @endif
                    </td>
                @endif

                {{-- Created At --}}
                <td class="px-5 py-3 text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                    {{ $tx->created_at ? $tx->created_at->format('Y-m-d H:i') : 'N/A' }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 text-[11px] text-gray-500 dark:text-gray-400 gap-2">
            <div>
                Showing
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $rows->firstItem() }}</span>
                to
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $rows->lastItem() }}</span>
                of
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $rows->total() }}</span>
                results
            </div>
            <div class="flex items-center justify-end">
                {{ $rows->onEachSide(1)->links() }}
            </div>
        </div>
    @endif
</div>
