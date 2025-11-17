@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agent Dashboard - Transferly</title>

  {{-- CSRF for AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Tailwind & Fonts --}}
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

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

  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100">
<div class="flex h-screen">
  <!-- Sidebar -->
  @include('components.agent-sidebar')

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto">
  <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
  <h1 class="text-2xl font-bold text-black dark:text-white">Agent Dashboard</h1>

  <div class="flex items-center gap-4">
    {{-- Cash In / Out button --}}
    <a href="{{ route('agent.cash.form') }}"
       class="inline-flex items-center px-5 py-2.5 rounded-full bg-black text-white text-sm font-semibold
              hover:bg-gray-900 dark:bg-white dark:text-black dark:hover:bg-gray-100 transition">
      Cash In / Out
    </a>

    {{-- Notifications --}}
    <div class="relative">
      <button type="button" id="notifButton"
              class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
        <span class="material-symbols-outlined text-black dark:text-white text-2xl">
          notifications
        </span>

        {{-- Unread dot --}}
        @if(isset($unreadCount) && $unreadCount > 0)
          <span id="notifDot" class="absolute top-2 right-2 inline-flex h-2 w-2 rounded-full bg-red-500"></span>
        @endif
      </button>

      {{-- Dropdown --}}
      <div id="notifDropdown"
           class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50"
           data-read="0">
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
          <div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notifications</span>
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
              <li class="border-b border-gray-100 dark:border-gray-800 last:border-0">
                <a href="{{ route('agent.transactions') }}"
                   class="block px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
                  <div class="font-semibold text-gray-800 dark:text-gray-100">
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


    <div class="p-8">
      <div class="mx-auto max-w-5xl">

        {{-- Flash Messages --}}
        @if(session('success'))
          <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 p-4 rounded-lg mb-6">
            {{ session('success') }}
          </div>
        @elseif(session('error'))
          <div class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 p-4 rounded-lg mb-6">
            {{ session('error') }}
          </div>
        @endif

        @if($agent)
          {{-- Agent Balance --}}
          <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 mb-8 shadow-sm">
            <div class="flex items-center justify-between">
              <h2 class="text-2xl font-bold text-black dark:text-white">Total Balance</h2>
              <span class="text-4xl font-extrabold text-black dark:text-white">
                ${{ number_format($agent->balance ?? 0, 2) }}
              </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              This is your current total balance (including earned commissions).
            </p>
          </div>

          {{-- Profile Info --}}
          <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 mb-8 shadow-sm">
            <h2 class="text-2xl font-bold mb-4">My Profile</h2>
            <div class="grid md:grid-cols-2 gap-4">
              <div><strong>ID:</strong> {{ $agent->id }}</div>
              <div><strong>Name:</strong> {{ $agent->name }}</div>
              <div><strong>Email:</strong> {{ $agent->email }}</div>
              <div><strong>Phone:</strong> {{ $agent->phone }}</div>
              <div><strong>City:</strong> {{ $agent->city }}</div>
              <div><strong>Commission:</strong> {{ $agent->commission ? $agent->commission . '%' : 'N/A' }}</div>
              <div><strong>Status:</strong> {{ ucfirst($agent->status ?? 'Active') }}</div>
              <div><strong>Work Hours:</strong> {{ $agent->work_start_time ?? 'N/A' }} - {{ $agent->work_end_time ?? 'N/A' }}</div>
              <div class="col-span-2">
                <strong>Availability:</strong>
                @if($agent->isCurrentlyAvailable())
                  <span class="text-green-600 font-semibold">🟢 Available Now</span>
                @else
                  <span class="text-red-600 font-semibold">🔴 Not Available</span>
                  <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    @if(!$agent->is_available)
                      • Availability toggle is OFF
                    @endif
                    @if(!$agent->work_start_time || !$agent->work_end_time)
                      • Work hours not set
                    @else
                      • Current time: {{ now()->format('H:i:s') }} | Work hours: {{ $agent->work_start_time }} - {{ $agent->work_end_time }}
                    @endif
                  </div>
                @endif
              </div>
            </div>
          </div>

          {{-- Edit Profile --}}
          <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 mb-8 shadow-sm">
            <h2 class="text-2xl font-bold mb-4">Edit Profile</h2>

            @if($errors->any())
              <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
                <ul class="list-disc pl-5">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form action="{{ route('agent.updateProfile') }}" method="POST" class="space-y-4">
              @csrf
              <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('phone') border-red-500 @enderror">
                @error('phone')
                  <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-2">City</label>
                <input type="text" name="city" value="{{ old('city', $agent->city) }}"
                       placeholder="Enter city name"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('city') border-red-500 @enderror">
                @error('city')
                  <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Location will be automatically updated based on city name</p>
              </div>

              <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-2">Commission (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="commission" value="{{ old('commission', $agent->commission) }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('commission') border-red-500 @enderror">
                @error('commission')
                  <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="mb-4">
                <label class="flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" name="is_available" value="1" {{ old('is_available', $agent->is_available) ? 'checked' : '' }}
                         class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary dark:bg-gray-700 dark:border-gray-600">
                  <span class="text-gray-700 dark:text-gray-300 font-medium">Set myself as available</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-8">When enabled, you'll be shown as available during your work hours</p>
              </div>

              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 dark:text-gray-300 mb-2">Work Start Time</label>
                  <input type="time" name="work_start_time" value="{{ old('work_start_time', $agent->work_start_time ? substr($agent->work_start_time, 0, 5) : '') }}"
                         class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('work_start_time') border-red-500 @enderror">
                  @error('work_start_time')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <div>
                  <label class="block text-gray-700 dark:text-gray-300 mb-2">Work End Time</label>
                  <input type="time" name="work_end_time" value="{{ old('work_end_time', $agent->work_end_time ? substr($agent->work_end_time, 0, 5) : '') }}"
                         class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-4 py-2 @error('work_end_time') border-red-500 @enderror">
                  @error('work_end_time')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <button type="submit" class="bg-black dark:bg:white dark:text:black text-white font-bold py-3 px-6 rounded-full hover:opacity-80 transition-opacity">
                Save Changes
              </button>
            </form>
          </div>

          {{-- Location --}}
          <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 shadow-sm">
            <h2 class="text-2xl font-bold mb-4">My Location</h2>
            @if($agent->latitude && $agent->longitude)
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                📍 Location: {{ $agent->city ?? 'Unknown' }}
                ({{ number_format($agent->latitude, 6) }}, {{ number_format($agent->longitude, 6) }})
              </p>
            @else
              <div class="mb-4 p-3 rounded-lg bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 text-sm">
                <p class="font-semibold mb-1">⚠️ No location data</p>
                <p>To set your location, enter a city name in the "Edit Profile" section above and save. The system will automatically fetch your coordinates.</p>
              </div>
            @endif
            <div id="map" class="h-96 rounded-lg border border-gray-300 dark:border-gray-700 relative"></div>
          </div>
        @else
          <p>No agent profile found.</p>
        @endif
      </div>
    </div>
  </main>
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
  const csrfMeta         = document.querySelector('meta[name=\"csrf-token\"]');
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
          // fail silently on UI
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
</body>
</html>
@endsection
