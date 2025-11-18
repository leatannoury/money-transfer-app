@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="flex h-screen">

  <!-- Sidebar -->
  @include('components.user-sidebar')

  <!-- Main -->
  <div class="flex-1 overflow-y-auto">

      <!-- Header -->
      <header class="flex h-20 items-center justify-end border-b border-[#CCCCCC] px-8 dark:border-white/20">
          <div class="flex items-center gap-6">
              <div class="relative">
                  <span class="material-symbols-outlined text-black dark:text-white text-2xl cursor-pointer">support_agent</span>
              </div>
          </div>
      </header>

      <!-- Content -->
      <div class="p-8">
          <div class="mx-auto max-w-3xl">

              <!-- Title -->
              <div class="mb-8">
                  <h1 class="text-black dark:text-white text-4xl font-black">Support Chat</h1>
                  <p class="text-black/60 dark:text-white/60 mt-2">
                      Chat with our support team for assistance
                  </p>
              </div>

              <!-- Chat Box -->
              <div class="bg-white dark:bg-gray-900 border border-[#CCCCCC] dark:border-white/20 rounded-xl p-6 h-[500px] overflow-y-auto space-y-2">
@foreach ($messages as $message)
    <div class="flex {{ $message->sender_type == 'user' ? 'justify-end' : 'justify-start' }} mb-0.5">
        <div class="inline-block max-w-[70%] rounded-xl text-sm break-words whitespace-pre-wrap 
            {{ $message->sender_type == 'user'
                ? 'bg-blue-600 text-white dark:bg-white dark:text-black px-1.5 py-0'
                : 'bg-gray-200 dark:bg-gray-700 text-black dark:text-white px-1.5 py-0'
            }}">
            {{ $message->message }}
        </div>
    </div>
    <div class="text-[10px] opacity-50 mt-1 flex {{ $message->sender_type == 'user' ? 'justify-end' : 'justify-start' }}">
        {{ $message->created_at->format('Y-m-d H:i') }}
    </div>
@endforeach



              </div>

              <!-- Send Message Form -->
              <form action="{{ route('user.chat.send') }}" method="POST" class="mt-4">
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
