@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agents Map - Transferly</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
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
  <style>
    #map {
      height: 600px;
      width: 100%;
      border-radius: 0.5rem;
    }
  </style>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100">
<div class="flex h-screen">
  @include('components.user-sidebar')

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto">
    <header class="flex justify-end items-center p-6 border-b border-gray-200 dark:border-gray-800">
      @include('components.user-notification-center')
    </header>

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100">
        Agents Map
      </h1>

      <div class="bg-white dark:bg-zinc-900/50 p-8 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <div id="map"></div>
        
        @if($agents->isEmpty())
          <div class="mt-6 p-4 rounded-lg bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">
            <p class="font-semibold mb-2">No agents with location data are currently available.</p>
            <p class="text-sm mb-3">Agents need to have their city set in their profile to appear on the map.</p>
            
            @if($allAgents->isNotEmpty())
              <div class="mt-4">
                <p class="text-sm font-semibold mb-2">Debug Info - All Active Agents:</p>
                <ul class="text-sm space-y-1">
                  @foreach($allAgents as $agent)
                    <li>
                      • <strong>{{ $agent['name'] }}</strong> 
                      @if($agent['has_location'])
                        <span class="text-green-600">✓ Has location ({{ $agent['latitude'] }}, {{ $agent['longitude'] }})</span>
                      @else
                        <span class="text-red-600">✗ No location data</span>
                        @if($agent['city'])
                          <span class="text-gray-600">(City: {{ $agent['city'] }})</span>
                        @else
                          <span class="text-gray-600">(No city set)</span>
                        @endif
                      @endif
                    </li>
                  @endforeach
                </ul>
                <p class="text-xs mt-3 text-gray-600 dark:text-gray-400">
                  💡 <strong>To fix:</strong> Agents should log in and update their profile with a city name. 
                  The system will automatically fetch their location coordinates.
                </p>
              </div>
            @endif
          </div>
        @else
          <div class="mt-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Available Agents ({{ $agents->count() }})</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
              @foreach($agents as $agent)
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                  <div class="flex items-start justify-between mb-2">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $agent['name'] }}</h3>
                    @if($agent['is_currently_available'])
                      <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        Available
                      </span>
                    @else
                      <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Offline
                      </span>
                    @endif
                  </div>
                  @if($agent['city'])
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                      <span class="material-symbols-outlined text-sm align-middle">location_on</span>
                      {{ $agent['city'] }}
                    </p>
                  @endif
                  @if($agent['phone'])
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      <span class="material-symbols-outlined text-sm align-middle">phone</span>
                      {{ $agent['phone'] }}
                    </p>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>
  </main>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  // Initialize map centered on Lebanon
  var map = L.map('map').setView([33.8938, 35.5018], 8);
  
  // Add OpenStreetMap tile layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '© OpenStreetMap'
  }).addTo(map);

  // Agents data from backend
  var agents = @json($agents);
  
  console.log('Agents data:', agents);
  console.log('Number of agents:', agents.length);

  // Create markers for each agent
  var markers = [];
  agents.forEach(function(agent) {
      // Parse latitude and longitude as floats
      var lat = agent.latitude ? parseFloat(agent.latitude) : null;
      var lng = agent.longitude ? parseFloat(agent.longitude) : null;
      
      if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
          console.log('Adding marker for:', agent.name, 'at', lat, lng);
          
          // Create custom icon based on availability
          var iconColor = agent.is_currently_available ? '#22c55e' : '#6b7280'; // green or gray
          var icon = L.divIcon({
              className: 'custom-marker',
              html: '<div style="background-color: ' + iconColor + '; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4); cursor: pointer;"></div>',
              iconSize: [24, 24],
              iconAnchor: [12, 12]
          });

          var marker = L.marker([lat, lng], {icon: icon}).addTo(map);
          
          // Create popup content
          var popupContent = '<div style="min-width: 180px; padding: 4px;">';
          popupContent += '<b style="font-size: 14px;">' + (agent.name || 'Agent') + '</b><br>';
          if (agent.city) {
              popupContent += '<span style="color: #666; font-size: 12px;">📍 ' + agent.city + '</span><br>';
          }
          if (agent.phone) {
              popupContent += '<span style="color: #666; font-size: 12px;">📞 ' + agent.phone + '</span><br>';
          }
          if (agent.is_currently_available) {
              popupContent += '<span style="color: #22c55e; font-weight: bold; font-size: 12px;">✓ Available Now</span>';
          } else {
              popupContent += '<span style="color: #6b7280; font-size: 12px;">○ Offline</span>';
          }
          popupContent += '</div>';
          
          marker.bindPopup(popupContent);
          markers.push(marker);
      } else {
          console.warn('Skipping agent (no valid location):', agent.name, 'Lat:', lat, 'Lng:', lng);
      }
  });

  // Fit map to show all markers if there are any
  if (markers.length > 0) {
      var group = new L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.1));
      console.log('Map fitted to show', markers.length, 'markers');
  } else {
      console.warn('No markers were added to the map. Check if agents have valid latitude/longitude.');
  }
</script>
</body>
</html>
@endsection

