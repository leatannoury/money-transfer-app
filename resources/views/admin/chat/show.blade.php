@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="flex h-screen">

    <!-- Sidebar -->
    @include('components.admin-sidebar')

    <!-- Main -->
    <div class="flex-1 overflow-y-auto">

        <!-- Header -->
        <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
                <h2 class="text-xl font-bold">Chat with {{ $chatRoom->user->name }}</h2>
             <form method="POST" action="{{ route('admin.chat.close', $chatRoom->id) }}">
                @csrf
                <button class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Close Chat
                </button>
            </form>
        </header>

        <!-- Content -->
        <div class="p-8">
            <div class="mx-auto max-w-3xl">

                <!-- Chat Box -->
                <div class="bg-white dark:bg-gray-900 border border-[#CCCCCC] dark:border-white/20 rounded-xl p-6 h-[500px] overflow-y-auto space-y-2">
                    @foreach($chatRoom->messages as $msg)
                        <div class="flex {{ $msg->sender_type === 'admin' ? 'justify-end' : 'justify-start' }} mb-0.5">
                            <div class="inline-block max-w-[70%] rounded-xl text-sm break-words whitespace-pre-wrap
                                {{ $msg->sender_type === 'admin'
                                    ? 'bg-blue-600 text-white dark:bg-blue-400 dark:text-black px-4 py-2'
                                    : 'bg-gray-200 dark:bg-gray-700 text-black dark:text-white px-4 py-2'
                                }}">
                                {{ $msg->message }}
                            </div>
                        </div>
                        <div class="text-[10px] opacity-50 mt-1 flex {{ $msg->sender_type === 'admin' ? 'justify-end' : 'justify-start' }}">
                            {{ $msg->created_at->format('Y-m-d H:i') }}
                        </div>
                    @endforeach
                </div>

                <!-- Send Message Form -->
                <form method="POST" action="{{ route('admin.chat.send', $chatRoom->id) }}" class="mt-4">
                    @csrf
                    <div class="flex items-center gap-3">
                        <textarea
                            name="message"
                            rows="2"
                            class="w-full rounded-lg border border-[#CCCCCC] dark:border-white/20 dark:bg-gray-800 dark:text-white px-4 py-2 focus:ring-2 focus:ring-black dark:focus:ring-white"
                            placeholder="Type your message..."
                            required
                        ></textarea>

                        <button
                            type="submit"
                            class="flex items-center justify-center gap-2 rounded-full h-10 px-6 bg-black text-white text-sm font-bold hover:opacity-80 dark:bg-white dark:text-black"
                        >
                            <span class="material-symbols-outlined text-base">send</span>
                            Send
                        </button>
                    </div>
                </form>

            </div>
        </div>

</div>
</div>

@endsection
