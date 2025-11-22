@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="flex h-screen">
    {{-- Sidebar --}}
    @include('components.agent-sidebar')

    {{-- Main content --}}
    <div class="flex-1 overflow-y-auto bg-[#F5F5F7] dark:bg-[#050509]">
        {{-- Header --}}
        <header class="flex h-20 items-center justify-between border-b border-gray-200/80 dark:border-white/10 px-8 bg-white/80 dark:bg-black/40 backdrop-blur">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-50">
                    Edit Profile
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Keep your contact details, commission and working hours up to date.
                </p>
            </div>

            <a href="{{ route('agent.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 dark:border-gray-700 px-4 py-2 text-xs font-medium
                      text-gray-800 dark:text-gray-100 bg-white dark:bg-transparent
                      hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Back to Dashboard</span>
            </a>
        </header>

        <div class="p-8">
            <div class="mx-auto max-w-4xl space-y-6">

                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200 px-4 py-3 rounded-xl text-sm border border-emerald-100 dark:border-emerald-800">
                        {{ session('success') }}
                    </div>
                @elseif(session('error'))
                    <div class="bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-200 px-4 py-3 rounded-xl text-sm border border-red-100 dark:border-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/40 text-red-700 dark:text-red-200 text-sm border border-red-100 dark:border-red-800">
                        <p class="font-semibold mb-1">Please fix the following:</p>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Card --}}
                <div class="bg-white dark:bg-[#050509] border border-gray-200/80 dark:border-white/10 rounded-2xl shadow-sm px-8 py-7">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-50">
                                Profile Details
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                These details will be shown to users when they select you as an agent.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('agent.updateProfile') }}" method="POST" class="space-y-8">
                        @csrf

                        {{-- Contact info --}}
                        <div class="space-y-4">
                            <h3 class="text-xs font-semibold tracking-wide text-gray-500 dark:text-gray-400 uppercase">
                                Contact Information
                            </h3>

                            <div class="grid md:grid-cols-2 gap-4">
                                {{-- Phone --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                        Phone
                                    </label>
                                    <input
                                        type="text"
                                        name="phone"
                                        value="{{ old('phone', $agent->phone) }}"
                                        class="w-full rounded-lg border text-sm px-3 py-2.5
                                               border-gray-300 dark:border-gray-700
                                               bg-gray-50 dark:bg-gray-900
                                               text-gray-900 dark:text-gray-50
                                               focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent
                                               @error('phone') border-red-500 focus:ring-red-500 dark:focus:ring-red-400 @enderror">
                                    @error('phone')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- City --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                        City
                                    </label>
                                    <input
                                        type="text"
                                        name="city"
                                        value="{{ old('city', $agent->city) }}"
                                        placeholder="e.g. Beirut"
                                        class="w-full rounded-lg border text-sm px-3 py-2.5
                                               border-gray-300 dark:border-gray-700
                                               bg-gray-50 dark:bg-gray-900
                                               text-gray-900 dark:text-gray-50
                                               focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent
                                               @error('city') border-red-500 focus:ring-red-500 dark:focus:ring-red-400 @enderror">
                                    @error('city')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                        Your map location is automatically updated based on this city.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Commission & availability --}}
                        <div class="space-y-4">
                            <h3 class="text-xs font-semibold tracking-wide text-gray-500 dark:text-gray-400 uppercase">
                                Agent Settings
                            </h3>

                            <div class="grid md:grid-cols-2 gap-4">
                                {{-- Commission --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                        Commission (%)
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            name="commission"
                                            value="{{ old('commission', $agent->commission) }}"
                                            class="w-full rounded-lg border text-sm px-3 py-2.5 pr-10
                                                   border-gray-300 dark:border-gray-700
                                                   bg-gray-50 dark:bg-gray-900
                                                   text-gray-900 dark:text-gray-50
                                                   focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent
                                                   @error('commission') border-red-500 focus:ring-red-500 dark:focus:ring-red-400 @enderror">
                                        <span class="absolute inset-y-0 right-3 flex items-center text-xs text-gray-400">%</span>
                                    </div>
                                    @error('commission')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                        This percentage will be used to calculate your commission on transfers.
                                    </p>
                                </div>

                                {{-- Availability toggle --}}
                                <div class="flex flex-col justify-center">
                                    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            name="is_available"
                                            value="1"
                                            {{ old('is_available', $agent->is_available) ? 'checked' : '' }}
                                            class="w-[18px] h-[18px] rounded border-gray-300 dark:border-gray-600
                                                   text-black dark:text-white focus:ring-black dark:focus:ring-white
                                                   dark:bg-gray-900">
                                        <div>
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                                Set myself as available
                                            </span>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                You’ll appear as “Available Now” while inside your work hours.
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Work hours --}}
                        <div class="space-y-4">
                            <h3 class="text-xs font-semibold tracking-wide text-gray-500 dark:text-gray-400 uppercase">
                                Working Hours
                            </h3>

                            <div class="grid md:grid-cols-2 gap-4">
                                {{-- Start time --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                        Work Start Time
                                    </label>
                                    <input
                                        type="time"
                                        name="work_start_time"
                                        value="{{ old('work_start_time', $agent->work_start_time ? substr($agent->work_start_time,0,5) : '') }}"
                                        class="w-full rounded-lg border text-sm px-3 py-2.5
                                               border-gray-300 dark:border-gray-700
                                               bg-gray-50 dark:bg-gray-900
                                               text-gray-900 dark:text-gray-50
                                               focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent
                                               @error('work_start_time') border-red-500 focus:ring-red-500 dark:focus:ring-red-400 @enderror">
                                    @error('work_start_time')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- End time --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                        Work End Time
                                    </label>
                                    <input
                                        type="time"
                                        name="work_end_time"
                                        value="{{ old('work_end_time', $agent->work_end_time ? substr($agent->work_end_time,0,5) : '') }}"
                                        class="w-full rounded-lg border text-sm px-3 py-2.5
                                               border-gray-300 dark:border-gray-700
                                               bg-gray-50 dark:bg-gray-900
                                               text-gray-900 dark:text-gray-50
                                               focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent
                                               @error('work_end_time') border-red-500 focus:ring-red-500 dark:focus:ring-red-400 @enderror">
                                    @error('work_end_time')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Agents are shown as available only when the toggle is ON and the current time is between your start and end time.
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end pt-2">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-full px-6 py-2.5
                                       bg-black text-white text-sm font-semibold
                                       hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black
                                       dark:bg-white dark:text-black dark:hover:bg-gray-100 dark:focus:ring-white dark:focus:ring-offset-black">
                                <span class="material-symbols-outlined text-sm">save</span>
                                <span>Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
