@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="flex h-screen">
  <!-- Sidebar -->
  @include('components.agent-sidebar')

  <!-- Main Content -->
  <div class="flex-1 overflow-y-auto">
    <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
      <h1 class="text-2xl font-bold text-black dark:text-white">Agent Dashboard</h1>

      <div class="flex items-center gap-4">
        {{-- Cash In / Out button --}}
        <a href="{{ route('agent.cash.menu') }}"
           class="inline-flex items-center px-5 py-2.5 rounded-full bg-black text-white text-sm font-semibold
                  hover:bg-gray-900 dark:bg-white dark:text-black dark:hover:bg-gray-100 transition">
          Cash In / Out
        </a>

        <a href="{{ route('agent.edit.profile') }}"
           class="inline-flex items-center px-5 py-2.5 rounded-full border border-gray-300
                  bg-white text-sm font-semibold text-gray-800 shadow-sm
                  hover:bg-gray-100 hover:border-gray-400
                  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black
                  dark:bg-transparent dark:text-gray-100 dark:border-gray-600
                  dark:hover:bg-gray-800 dark:hover:border-gray-500">
          Edit Profile
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
               data-read="{{ ($unreadCount ?? 0) > 0 ? '0' : '1' }}">
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
                  <li @class([
                        'border-b border-gray-100 dark:border-gray-800 last:border-0',
                        'bg-black/5 dark:bg-white/5' => !$n->is_read,
                    ])>
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
          {{-- =======================
               Agent Balance (multi-currency)
             ======================= --}}
          <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 mb-8 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
              {{-- Left: label + amount --}}
              <div>
                <p class="text-xs font-semibold tracking-wide text-gray-500 dark:text-gray-400 uppercase">
                  Total Balance
                </p>

                <div class="mt-2 flex items-baseline gap-2">
                  @php
                      $code   = $selectedCurrency ?? ($baseCurrency ?? 'USD');
                      $amount = $displayBalance ?? ($agent->balance ?? 0);
                  @endphp

                  <span class="text-4xl font-extrabold text-black dark:text-white">
                    {{ \App\Services\CurrencyService::format($amount, $code) }}
                  </span>
                </div>

                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                  Base currency:
                  <span class="font-semibold">
                    {{ $baseCurrency ?? 'USD' }}
                    @if(isset($currencies[$baseCurrency ?? 'USD']))
                      – {{ $currencies[$baseCurrency ?? 'USD'] }}
                    @endif
                  </span>.
                  Displaying in:
                  <span class="font-semibold">
                    {{ $code }}
                    @if(isset($currencies[$code]))
                      – {{ $currencies[$code] }}
                    @endif
                  </span>.
                </p>
              </div>

              {{-- Right: currency selector --}}
              <form method="GET" action="{{ route('agent.dashboard') }}" class="w-full md:w-auto">
                <label for="currency" class="sr-only">Currency</label>
                <div class="relative">
                  <select id="currency"
                          name="currency"
                          class="w-full md:w-64 appearance-none rounded-full border border-gray-300 bg-white py-2.5 pl-4 pr-10 text-sm font-medium
                                 text-gray-800 shadow-sm focus:border-black focus:outline-none focus:ring-1 focus:ring-black
                                 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:border-white dark:focus:ring-white"
                          onchange="this.form.submit()">
                    @if(isset($currencies) && is_array($currencies))
                      @foreach($currencies as $cCode => $label)
                        <option value="{{ $cCode }}"
                                {{ $code === $cCode ? 'selected' : '' }}>
                          {{ $cCode }} - {{ $label }}
                        </option>
                      @endforeach
                    @endif
                  </select>
                
                </div>
              </form>
            </div>
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

@endsection
