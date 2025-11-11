@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agent Dashboard - Transferly</title>

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
  <aside class="w-64 bg-background-light dark:bg-background-dark p-6 flex flex-col justify-between border-r border-gray-200 dark:border-gray-800">
    <div>
      <div class="flex items-center gap-3 mb-12">
        <div class="w-8 h-8 bg-primary rounded-full"></div>
        <span class="font-bold text-xl">Transferly</span>
      </div>
      <nav class="flex flex-col gap-2">
        <a href="{{ route('agent.dashboard') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">dashboard</span>
          <span>Dashboard</span>
        </a>
        <a href="{{ route('agent.transactions') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">receipt_long</span>
          <span>Transactions</span>
        </a>
        <a href="#" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">settings</span>
          <span>Settings</span>
        </a>
      </nav>
    </div>

    <div class="flex items-center gap-3">
  <!-- Avatar -->
  <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>

  <!-- User Info and Logout -->
  <div class="flex flex-col gap-2">
    <div>
      <p class="font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
    </div>

    <!-- Logout Button -->
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition">
        <img src="{{ asset('images/logout-svgrepo-com.svg') }}" class="h-5 w-5" alt="Logout Icon" />

        <span class="text-sm font-medium">Log Out</span>
      </button>
    </form>
  </div>
</div>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto">
    <header class="flex h-20 items-center justify-between border-b border-[#CCCCCC] px-8 dark:border-white/20">
      <h1 class="text-2xl font-bold text-black dark:text-white">Agent Dashboard</h1>
      <div class="relative">
        <span class="material-symbols-outlined text-black dark:text-white text-2xl cursor-pointer">notifications</span>
        <div class="absolute -top-1 -right-1 size-2 rounded-full bg-black dark:bg-white"></div>
      </div>
    </header>

    <div class="p-8">
      <div class="mx-auto max-w-5xl">

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
          <!-- Profile Info -->
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
                @endif
              </div>
            </div>
          </div>

          <!-- Edit Profile -->
          <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 mb-8 shadow-sm">
            <h2 class="text-2xl font-bold mb-4">Edit Profile</h2>
            <form action="{{ route('agent.updateProfile') }}" method="POST" class="space-y-4">
              @csrf
              <div>
                <label class="block text-gray-700 dark:text-gray-300">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
              </div>

              <div>
                <label class="block text-gray-700 dark:text-gray-300">City</label>
                <input type="text" name="city" value="{{ old('city', $agent->city) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
              </div>

              <div>
                <label class="block text-gray-700 dark:text-gray-300">Commission (%)</label>
                <input type="number" step="0.1" name="commission" value="{{ old('commission', $agent->commission) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
              </div>

              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 dark:text-gray-300">Work Start Time</label>
                  <input type="time" name="work_start_time" value="{{ old('work_start_time', $agent->work_start_time) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                  <label class="block text-gray-700 dark:text-gray-300">Work End Time</label>
                  <input type="time" name="work_end_time" value="{{ old('work_end_time', $agent->work_end_time) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
              </div>

              <button type="submit" class="bg-black dark:bg-white dark:text-black text-white font-bold py-3 px-6 rounded-full hover:opacity-80">
                Save Changes
              </button>
            </form>
          </div>

          <!-- Location -->
          <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-8 shadow-sm">
            <h2 class="text-2xl font-bold mb-4">My Location</h2>
            <div id="map" class="h-96 rounded-lg border border-gray-300 dark:border-gray-700"></div>
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
  var map = L.map('map').setView([33.8938, 35.5018], 8);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '© OpenStreetMap'
  }).addTo(map);

  var agent = JSON.parse(`{!! addslashes(json_encode($agent)) !!}`);
  if (agent && agent.latitude && agent.longitude) {
      var marker = L.marker([agent.latitude, agent.longitude]).addTo(map);
      marker.bindPopup("<b>" + (agent.name ?? "My Profile") + "</b><br>" + (agent.city ?? ""));
      map.setView([agent.latitude, agent.longitude], 12);
  }
</script>
</body>
</html>
@endsection
