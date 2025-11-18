<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Platform Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1, h2 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        p { margin: 4px 0; }
    </style>
</head>
<body>
    <h1>Platform Report</h1>
    <h2>{{ date('Y-m-d') }}</h2>

    <h2>Users</h2>
    <table>
<tr>
    <th>Role</th>
    <th>Total</th>
    <th>Active</th>
    <th>Banned</th>
    <th>New This Month</th>
</tr>
<tr>
    <td>Users</td>
    <td>{{ $totalUsers }}</td>
    <td>{{ $activeUsers }}</td>
    <td>{{ $bannedUsers }}</td>
    <td>{{ $newUsers }}</td>
</tr>
<tr>
    <td>Agents</td>
    <td>{{ $totalAgents }}</td>
    <td>{{ $activeAgents }}</td>
    <td>{{ $bannedAgents }}</td>
    <td>{{ $newAgents }}</td>
</tr>
<tr>
    <td>Admins</td>
    <td>{{ $totalAdmins }}</td>
    <td>*</td>
    <td>*</td>
    <td>*</td>
</tr>
    </table>
    <p><strong>Total Platform Balance:</strong> ${{ number_format($totalBalance, 2) }}</p>

    <h2>Transactions</h2>
    <table>
        <tr>
            <th>Status</th>
            <th>Count</th>
        </tr>
        <tr><td>Total Transactions</td><td>{{ $totalTransactions }}</td></tr>
        <tr><td>Completed</td><td>{{ $completedTransactions }}</td></tr>
        <tr><td>Failed</td><td>{{ $failedTransactions }}</td></tr>
        <tr><td>In Progress</td><td>{{ $inProgressTransactions }}</td></tr>
    </table>
    <p><strong>Total Amount Transferred:</strong> ${{ number_format($totalAmount, 2) }}</p>

    <h2>Reviews / Feedback</h2>
    <p>Total Approved Reviews: {{ $totalReviews }}</p>
    <p>Average Rating: {{ number_format($averageRating, 1) }}</p>

    <table>
        <tr>
            <th>User</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Date</th>
        </tr>
        @foreach($recentReviews as $review)
        <tr>
            <td>{{ $review->user->name ?? 'Unknown' }}</td>
            <td>{{ $review->rating }}</td>
            <td>{{ $review->comment }}</td>
            <td>{{ $review->created_at->format('Y-m-d') }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>
