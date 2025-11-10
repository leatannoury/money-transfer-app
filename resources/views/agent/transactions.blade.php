<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Transactions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 30px;
            color: #333;
        }
        h1 {
            color: #222;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #f0f0f0;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 14px;
        }
        .btn-accept { background-color: #007bff; }
        .btn-complete { background-color: #28a745; }
        .btn-disabled { background-color: #aaa; cursor: not-allowed; }
        .status {
            font-weight: bold;
            text-transform: capitalize;
        }
        .status.pending { color: #ff9800; }
        .status.in_progress { color: #2196f3; }
        .status.completed { color: #4caf50; }
        .success, .error {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        a {
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>

    <h1>Welcome, {{ $agent->name }}</h1>
    <h2>My Transactions</h2>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    @if($transactions->isEmpty())
        <p>No transactions available.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>Amount</th>
                    <th>Currency</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                    <tr>
                        <td>{{ $tx->id }}</td>
                        <td>{{ $tx->sender->name ?? 'N/A' }}</td>
                        <td>{{ $tx->receiver->name ?? 'N/A' }}</td>
                        <td>{{ $tx->amount }}</td>
                        <td>{{ $tx->currency }}</td>
                        <td class="status {{ $tx->status }}">{{ ucfirst(str_replace('_', ' ', $tx->status)) }}</td>
                        <td>
                            @if($tx->status === 'pending')
                                <form action="{{ route('agent.accept', $tx->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-accept">Accept</button>
                                </form>
                            @elseif($tx->status === 'in_progress' && $tx->agent_id === $agent->id)
                                <form action="{{ route('agent.complete', $tx->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-complete">Complete</button>
                                </form>
                            @else
                                <button class="btn btn-disabled" disabled>No Action</button>
                            @endif
                        </td>
                        <td>{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <br>
    <a href="{{ route('agent.dashboard') }}">⬅ Back to Dashboard</a>

</body>
</html>
