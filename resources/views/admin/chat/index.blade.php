@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="relative flex min-h-screen w-full flex-col">
    <div class="flex min-h-screen">
        @include('components.admin-sidebar')

        <div class="flex-1 flex flex-col">

            <header class="flex items-center justify-center border-b px-8 py-4 bg-card-light dark:bg-card-dark">
                <h2 class="text-xl font-bold">User Chat Requests</h2>
            </header>

            <div class="p-8 space-y-10">

                <div class="overflow-hidden rounded-xl border bg-white dark:bg-gray-900">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Updated</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($chatRooms as $chat)
                            <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <td class="px-6 py-4">{{ $chat->user->email }}</td>

                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $chat->status === 'open' ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' }}">
                                        {{ ucfirst($chat->status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-gray-500">
                                    {{ $chat->updated_at->format('Y-m-d H:i') }}
                                </td>
                            @if($chat->status==='open')
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.chat.show', $chat->id) }}"
                                       class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Open Chat
                                    </a>
                                </td>
                             @endif
                              @if($chat->status!=='open')
                              <td class="px-6 py-4">
                                  
                                       
                             <span class="text-gray-400 text-sm italic">Closed</span>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No chat rooms found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
    </div>
    </div>
</div>
@endsection
