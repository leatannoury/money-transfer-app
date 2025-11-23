@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="flex h-screen bg-gray-50 dark:bg-[#050509]">
    {{-- Sidebar --}}
    @include('components.agent-sidebar')

    {{-- Main Content --}}
    <div class="flex-1 overflow-y-auto">
        {{-- Top Bar --}}
        <header class="relative z-20 flex h-20 items-center justify-between border-b border-gray-200/80 px-8 bg-white/80 backdrop-blur dark:bg-black/40 dark:border-white/10">
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-50">
                Agent Dashboard
            </h1>

            <div class="flex items-center gap-3">
                {{-- Cash In / Out --}}
                <a href="{{ route('agent.cash.menu') }}"
                   class="inline-flex items-center px-5 py-2.5 rounded-full bg-black text-white text-sm font-semibold
                          hover:bg-gray-900 active:scale-[0.98]
                          dark:bg-white dark:text-black dark:hover:bg-gray-100 transition">
                    Cash In / Out
                </a>

                {{-- Edit Profile --}}
                <a href="{{ route('agent.edit.profile') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-full border border-gray-300
                          bg-white text-sm font-semibold text-gray-800 shadow-sm
                          hover:bg-gray-100 hover:border-gray-400
                          focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black
                          dark:bg-black/40 dark:text-gray-100 dark:border-gray-700
                          dark:hover:bg-gray-900 dark:hover:border-gray-500">
                    Edit Profile
                </a>

                {{-- Notifications --}}
                <div class="relative">
                    <button type="button" id="notifButton"
                            class="relative flex items-center justify-center w-10 h-10 rounded-full border border-transparent
                                   hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <span class="material-symbols-outlined text-gray-800 dark:text-gray-100 text-2xl">
                            notifications
                        </span>

                        {{-- Unread dot --}}
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span id="notifDot" class="absolute top-2 right-2 inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                        @endif
                    </button>

                    {{-- Dropdown --}}
                    <div id="notifDropdown"
                         class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-[#050509] border border-gray-200 dark:border-gray-800 rounded-xl shadow-xl z-50"
                         data-read="{{ ($unreadCount ?? 0) > 0 ? '0' : '1' }}">
                        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                            <div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-50">Notifications</span>
                                <span id="notifUnreadText" class="block text-xs text-gray-500 dark:text-gray-400">
                                    {{ isset($unreadCount) ? $unreadCount : 0 }} unread
                                </span>
                            </div>
                            <button id="notifClearButton"
                                    type="button"
                                    class="text-xs font-semibold text-gray-700 dark:text-gray-200 hover:underline disabled:opacity-40"
                                    {{ (isset($notifications) && $notifications->count()) ? '' : 'disabled' }}>
                                Clear
                            </button>
                        </div>

                        @if(isset($notifications) && $notifications->count())
                            <ul class="max-h-80 overflow-y-auto" id="notifList">
                                @foreach($notifications as $n)
                                    <li @class([
                                            'border-b border-gray-100 dark:border-gray-800 last:border-0',
                                            'bg-black/[0.03] dark:bg-white/[0.04]' => !$n->is_read,
                                        ])>
                                        <a href="{{ route('agent.transactions') }}"
                                           class="block px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-900">
                                            <div class="font-semibold text-gray-900 dark:text-gray-50">
                                                {{ $n->title }}
                                            </div>
                                            @if($n->message)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $n->message }}
                                                </div>
                                            @endif
                                            <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                                                {{ $n->created_at?->diffForHumans() }}
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <div id="notifEmptyState" class="hidden p-4 text-sm text-gray-500 dark:text-gray-400">
                                No notifications.
                            </div>
                        @else
                            <ul id="notifList" class="hidden"></ul>
                            <div id="notifEmptyState" class="p-4 text-sm text-gray-500 dark:text-gray-400">
                                No notifications.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Body --}}
        <div class="p-8">
            <div class="mx-auto max-w-5xl space-y-6">

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-200 px-4 py-3 rounded-lg text-sm border border-emerald-100 dark:border-emerald-800">
                        {{ session('success') }}
                    </div>
                @elseif(session('error'))
                    <div class="bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg text-sm border border-red-100 dark:border-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @if($agent)
                    {{-- Balance Card --}}
                    <div class="bg-white dark:bg-[#0B0B11] border border-gray-200 dark:border-white/10 rounded-2xl p-6 md:p-8 shadow-sm">
                        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            {{-- Left: amount --}}
                            <div>
                                <p class="text-[11px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase">
                                    Total Balance
                                </p>

                                @php
                                    $code   = $selectedCurrency ?? ($baseCurrency ?? 'USD');
                                    $amount = $displayBalance ?? ($agent->balance ?? 0);
                                @endphp

                                <div class="mt-3 flex items-baseline gap-2">
                                    <span class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-gray-50 leading-none">
                                        {{ \App\Services\CurrencyService::format($amount, $code) }}
                                    </span>
                                </div>

                                <!-- Badges removed as per user request -->
                            </div>

                            {{-- Right: currency selector --}}
                            <form method="GET" action="{{ route('agent.dashboard') }}" class="w-full md:w-auto">
                                <label for="currency" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Display currency
                                </label>
                                <div class="relative">
                                    <select id="currency"
                                            name="currency"
                                            class="w-full md:w-64 appearance-none rounded-full border border-gray-300 bg-white py-2.5 pl-4 pr-9 text-sm font-medium
                                                   text-gray-800 shadow-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black
                                                   dark:bg-black/40 dark:text-gray-100 dark:border-gray-700 dark:focus:border-white dark:focus:ring-white">
                                        @if(isset($currencies) && is_array($currencies))
                                            @foreach($currencies as $cCode => $label)
                                                <option value="{{ $cCode }}"
                                                    {{ $code === $cCode ? 'selected' : '' }}>
                                                    {{ $cCode }} - {{ $label }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400 dark:text-gray-500 text-lg">
                                        ▾
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Profile Card --}}
                    <div class="bg-white dark:bg-[#0B0B11] border border-gray-200 dark:border-white/10 rounded-2xl p-6 md:p-8 shadow-sm">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                                My Profile
                            </h2>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-800 dark:text-gray-100">
                            <div><span class="text-gray-500 dark:text-gray-400">ID:</span> <span class="font-medium">{{ $agent->id }}</span></div>
                            <div><span class="text-gray-500 dark:text-gray-400">Name:</span> <span class="font-medium">{{ $agent->name }}</span></div>
                            <div><span class="text-gray-500 dark:text-gray-400">Email:</span> <span class="font-medium">{{ $agent->email }}</span></div>
                            <div><span class="text-gray-500 dark:text-gray-400">Phone:</span> <span class="font-medium">{{ $agent->phone }}</span></div>
                            <div><span class="text-gray-500 dark:text-gray-400">City:</span> <span class="font-medium">{{ $agent->city }}</span></div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Commission:</span>
                                <span class="font-medium">{{ $agent->commission ? $agent->commission . '%' : 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                <span class="font-medium">{{ ucfirst($agent->status ?? 'Active') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Work Hours:</span>
                                <span class="font-medium">
                                    {{ $agent->work_start_time ?? 'N/A' }} – {{ $agent->work_end_time ?? 'N/A' }}
                                </span>
                            </div>

                            <div class="md:col-span-2 mt-2">
                                <span class="text-gray-500 dark:text-gray-400">Availability:</span>
                                @if($agent->isCurrentlyAvailable())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ml-2
                                                 dark:bg-emerald-900/20 dark:text-emerald-200">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        Available now
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 ml-2
                                                 dark:bg-red-900/20 dark:text-red-200">
                                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                        Not available
                                    </span>
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        @if(!$agent->is_available)
                                            • Availability toggle is OFF<br>
                                        @endif
                                        @if(!$agent->work_start_time || !$agent->work_end_time)
                                            • Work hours not set
                                        @else
                                            • Now: {{ now()->format('H:i:s') }} · Work hours: {{ $agent->work_start_time }} – {{ $agent->work_end_time }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Location Card --}}
                    <div class="bg-white dark:bg-[#0B0B11] border border-gray-200 dark:border-white/10 rounded-2xl p-6 md:p-8 shadow-sm mb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                                My Location
                            </h2>
                        </div>

                        @if($agent->latitude && $agent->longitude)
                            <p class="text-xs md:text-sm text-gray-600 dark:text-gray-400 mb-4">
                                📍 <span class="font-medium">{{ $agent->city ?? 'Unknown' }}</span>
                                <span class="ml-2 text-gray-400">
                                    ({{ number_format($agent->latitude, 6) }}, {{ number_format($agent->longitude, 6) }})
                                </span>
                            </p>
                        @else
                            <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200 text-xs md:text-sm border border-amber-100 dark:border-amber-800">
                                <p class="font-semibold mb-1">No location data</p>
                                <p>Set your city in <span class="font-medium">Edit Profile</span> and save. We’ll automatically update your coordinates.</p>
                            </div>
                        @endif

                        <div id="map" class="h-80 md:h-96 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-gray-100 dark:bg-black/40"></div>
                    </div>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-300">No agent profile found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Leaflet map
    var map = L.map('map').setView([33.8938, 35.5018], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var agent = @json($agent);
    var latitude = agent.latitude ? parseFloat(agent.latitude) : null;
    var longitude = agent.longitude ? parseFloat(agent.longitude) : null;

    if (latitude && longitude && !isNaN(latitude) && !isNaN(longitude)) {
        var marker = L.marker([latitude, longitude]).addTo(map);
        var popupContent = '<div style="min-width: 150px;">';
        popupContent += '<b>' + (agent.name || 'My Profile') + '</b><br>';
        if (agent.city) {
            popupContent += '<span style="color: #666;">📍 ' + agent.city + '</span><br>';
        }
        popupContent += '<span style="color: #666; font-size: 0.85em;">Lat: ' + latitude.toFixed(6) + ', Lng: ' + longitude.toFixed(6) + '</span>';
        popupContent += '</div>';
        marker.bindPopup(popupContent);
        map.setView([latitude, longitude], 12);
    } else {
        var noLocationDiv = document.createElement('div');
        noLocationDiv.className = 'p-4 text-center text-gray-600 dark:text-gray-400';
        noLocationDiv.innerHTML = '<p class="mb-2">📍 No location data available</p><p class="text-sm">Update your city in the profile to set your location.</p>';
        document.getElementById('map').appendChild(noLocationDiv);
    }

    // Notification dropdown toggle + mark-as-read
    const notifButton      = document.getElementById('notifButton');
    const notifDropdown    = document.getElementById('notifDropdown');
    const notifDot         = document.getElementById('notifDot');
    const notifUnreadText  = document.getElementById('notifUnreadText');
    const unreadCountInit  = {{ isset($unreadCount) ? (int) $unreadCount : 0 }};
    const markReadUrl      = "{{ route('agent.notifications.read') }}";
    const clearUrl         = "{{ route('agent.notifications.clear') }}";
    const notifClearButton = document.getElementById('notifClearButton');
    const notifList        = document.getElementById('notifList');
    const notifEmptyState  = document.getElementById('notifEmptyState');
    const csrfMeta         = document.querySelector('meta[name="csrf-token"]');
    const csrfToken        = csrfMeta ? csrfMeta.getAttribute('content') : null;

    if (notifButton && notifDropdown) {
        notifButton.addEventListener('click', () => {
            notifDropdown.classList.toggle('hidden');

            // First time opening: mark notifications as read
            if (!notifDropdown.classList.contains('hidden') &&
                unreadCountInit > 0 &&
                notifDropdown.dataset.read === '0' &&
                csrfToken) {

                fetch(markReadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                }).then(() => {
                    notifDropdown.dataset.read = '1';
                    if (notifUnreadText) {
                        notifUnreadText.textContent = '0 unread';
                    }
                    if (notifDot) {
                        notifDot.remove();
                    }
                }).catch(() => {
                    // ignore UI error
                });
            }
        });

        document.addEventListener('click', (e) => {
            if (!notifButton.contains(e.target) && !notifDropdown.contains(e.target)) {
                notifDropdown.classList.add('hidden');
            }
        });
    }

    if (notifClearButton && clearUrl && csrfToken) {
        notifClearButton.addEventListener('click', () => {
            if (notifClearButton.disabled) {
                return;
            }

            notifClearButton.disabled = true;

            fetch(clearUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
            }).then(() => {
                if (notifUnreadText) {
                    notifUnreadText.textContent = '0 unread';
                }
                if (notifDot) {
                    notifDot.remove();
                }
                if (notifList) {
                    notifList.innerHTML = '';
                    notifList.classList.add('hidden');
                }
                if (notifEmptyState) {
                    notifEmptyState.classList.remove('hidden');
                }
            }).catch(() => {
                notifClearButton.disabled = false;
            });
        });
    }
</script>

@endsection
