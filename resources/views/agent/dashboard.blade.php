<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 40px;
            color: #333;
        }
        h1, h2 {
            color: #222;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
        form {
            background: white;
            padding: 20px;
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        input, button {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        button {
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #555;
        }
        #map {
            height: 400px;
            margin-top: 20px;
            border-radius: 8px;
            border: 2px solid #ccc;
        }
        .success {
            background-color: #c3f3cb;
            color: #2a662e;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .error {
            background-color: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Agent Dashboard</h1>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    {{-- Agent Info --}}
    @if($agent)
        <h2>My Profile</h2>
        <table>
            <tr><th>ID</th><td>{{ $agent->id }}</td></tr>
            <tr><th>Name</th><td>{{ $agent->name }}</td></tr>
            <tr><th>Email</th><td>{{ $agent->email }}</td></tr>
            <tr><th>Phone</th><td>{{ $agent->phone }}</td></tr>
            <tr><th>City</th><td>{{ $agent->city }}</td></tr>
            <tr><th>Commission</th><td>{{ $agent->commission ? $agent->commission . '%' : 'N/A' }}</td></tr>
            <tr><th>Status</th><td>{{ ucfirst($agent->status ?? 'Active') }}</td></tr>
            <tr><th>Latitude</th><td>{{ $agent->latitude ?? 'N/A' }}</td></tr>
            <tr><th>Longitude</th><td>{{ $agent->longitude ?? 'N/A' }}</td></tr>
            <tr>
                <th>Work Hours</th>
                <td>
                    {{ $agent->work_start_time ?? 'N/A' }} - {{ $agent->work_end_time ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <th>Current Availability</th>
                <td>
                    @if($agent->isCurrentlyAvailable())
                        🟢 Available now
                    @else
                        🔴 Not available
                    @endif
                </td>
            </tr>
        </table>

        {{-- Edit Profile Form --}}
        <h2>Edit Profile</h2>
        <form action="{{ route('agent.updateProfile') }}" method="POST">
            @csrf
            <label>Phone:</label>
            <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}">

            <label>City:</label>
            <input type="text" name="city" value="{{ old('city', $agent->city) }}">

            <label>Commission (%):</label>
            <input type="number" name="commission" step="0.1" min="0" value="{{ old('commission', $agent->commission) }}">

            <label>Work Start Time:</label>
            <input type="time" name="work_start_time" value="{{ old('work_start_time', $agent->work_start_time) }}">

            <label>Work End Time:</label>
            <input type="time" name="work_end_time" value="{{ old('work_end_time', $agent->work_end_time) }}">

            <button type="submit">Update Profile</button>
        </form>

        {{-- Update Location Form --}}
        <h2>Update Location Manually</h2>
        <form action="{{ route('agent.saveLocation') }}" method="POST">
            @csrf
            <label>Latitude:</label>
            <input type="text" name="latitude" value="{{ old('latitude', $agent->latitude) }}" placeholder="e.g. 33.8938">

            <label>Longitude:</label>
            <input type="text" name="longitude" value="{{ old('longitude', $agent->longitude) }}" placeholder="e.g. 35.5018">

            <button type="submit">Save Location</button>
        </form>

        {{-- Map --}}
        <h2>My Location</h2>
        <div id="map"></div>

    @else
        <p>No agent profile found.</p>
    @endif

    {{-- Leaflet.js Map --}}
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
