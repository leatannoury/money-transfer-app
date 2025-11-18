@extends('layouts.app', ['noNav' => true])

@section('content')

<div class="flex h-screen">
    {{-- Sidebar --}}
    @include('components.agent-sidebar')

    {{-- Main content --}}
    <div class="flex-1 overflow-y-auto">
        {{-- Header --}}
        <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
            <div>
                <h1 class="text-2xl font-bold text-black dark:text-white">Edit Profile</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Update your contact info, commission and working hours.
                </p>
            </div>

            <a href="{{ route('agent.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Back to Dashboard</span>
            </a>
        </header>

        <div class="p-8">
            <div class="mx-auto max-w-4xl">

                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 p-4 rounded-lg mb-6">
                        {{ session('success') }}
                    </div>
                @elseif(session('error'))
                    <div class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 p-4 rounded-lg mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Edit Profile Card --}}
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 shadow-sm">
                    <h2 class="text-2xl font-bold mb-4 text-black dark:text-white">Profile Details</h2>

                    <form action="{{ route('agent.updateProfile') }}" method="POST" class="space-y-4">
                        @csrf

                        {{-- Phone --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                            <input type="text" name="phone"
                                   value="{{ old('phone', $agent->phone) }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('phone') border-red-500 @enderror">
                            @error('phone')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- City --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">City</label>
                            <input type="text" name="city"
                                   value="{{ old('city', $agent->city) }}"
                                   placeholder="Enter city name"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('city') border-red-500 @enderror">
                            @error('city')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Location will be automatically updated based on city name.
                            </p>
                        </div>

                        {{-- Commission --}}
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-2">Commission (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="commission"
                                   value="{{ old('commission', $agent->commission) }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('commission') border-red-500 @enderror">
                            @error('commission')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Availability --}}
                        <div class="mb-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_available" value="1"
                                       {{ old('is_available', $agent->is_available) ? 'checked' : '' }}
                                       class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary dark:bg-gray-700 dark:border-gray-600">
                                <span class="text-gray-700 dark:text-gray-300 font-medium">
                                    Set myself as available
                                </span>
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-8">
                                When enabled, you'll be shown as available during your work hours.
                            </p>
                        </div>

                        {{-- Work hours --}}
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 mb-2">Work Start Time</label>
                                <input type="time" name="work_start_time"
                                       value="{{ old('work_start_time', $agent->work_start_time ? substr($agent->work_start_time,0,5) : '') }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('work_start_time') border-red-500 @enderror">
                                @error('work_start_time')
                                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 mb-2">Work End Time</label>
                                <input type="time" name="work_end_time"
                                       value="{{ old('work_end_time', $agent->work_end_time ? substr($agent->work_end_time,0,5) : '') }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('work_end_time') border-red-500 @enderror">
                                @error('work_end_time')
                                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button type="submit"
                                class="mt-4 bg-black dark:bg-white dark:text-black text-white font-bold py-3 px-6 rounded-full hover:opacity-80 transition-opacity">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
</div>
</div>

@endsection
