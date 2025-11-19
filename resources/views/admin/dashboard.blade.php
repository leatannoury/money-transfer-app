@extends('layouts.app', ['noNav' => true])

@section('content')
 <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#3B82F6",
            "background-light": "#f7f7f7",
            "background-dark": "#191919"
          },
          fontFamily: { display: "Manrope" },
        },
      },
    }
  </script>
 
  <style>
:root {
    --bg-card-light: #ffffff;
    --bg-card-dark: #1f2937;
    --text-light: #f9fafb;
    --text-dark: #111827;
}
.bg-card-light { background-color: var(--bg-card-light); }
.bg-card-dark { background-color: var(--bg-card-dark); }
.text-light { color: var(--text-light); }
.text-dark { color: var(--text-dark); }
</style>

<div class="flex min-h-screen">
<!-- SideNavBar -->
@include('components.admin-sidebar')
<!-- Main Content -->
<div class="flex-1 overflow-y-auto">
<!-- TopNavBar -->
<header class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
  <h2 class="text-text-light dark:text-text-dark text-xl font-bold">Dashboard</h2>
  @include('components.admin-notification-center')
</header>
<div class="flex-1 p-8 overflow-y-auto">
<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
   <div class="flex flex-col gap-2 rounded-xl p-6 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark">
        <p class="text-gray-500 dark:text-gray-400 text-base font-medium">Admin Balance</p>
        <p class="text-text-light dark:text-text-dark text-3xl font-bold">${{ number_format($adminBalance, 2) }}</p>
    </div>
<div class="flex flex-col gap-2 rounded-xl p-6 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark">
<p class="text-gray-500 dark:text-gray-400 text-base font-medium">Total Users</p>
<p class="text-text-light dark:text-text-dark text-3xl font-bold">{{$totalUsers}}</p>

</div>
<div class="flex flex-col gap-2 rounded-xl p-6 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark">
<p class="text-gray-500 dark:text-gray-400 text-base font-medium">Total Agents</p>
<p class="text-text-light dark:text-text-dark text-3xl font-bold">{{$totalAgents}}</p>

</div>
<div class="flex flex-col gap-2 rounded-xl p-6 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark">
<p class="text-gray-500 dark:text-gray-400 text-base font-medium">Total Transactions</p>
<p class="text-text-light dark:text-text-dark text-3xl font-bold">{{$totalTransactions}}</p>
</div>
</div>


<!-- Chart Section -->
<div class="mt-6 p-6 bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark">
    <h3 class="text-text-light dark:text-text-dark text-lg font-bold mb-4">Earnings (Last 24 Hours)</h3>
    <canvas id="revenueChart" class="w-full h-64"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let revenueChart = null;

function fetchAndRenderRevenueChart() {
    fetch('{{ route("admin.dashboard.hourlyFees") }}')
        .then(res => res.json())
        .then(data => {
            const labels = data.map(d => d.hour);
            const totals = data.map(d => d.total_fee);

            const ctx = document.getElementById('revenueChart').getContext('2d');

            if (revenueChart) {
                revenueChart.data.labels = labels;
                revenueChart.data.datasets[0].data = totals;
                revenueChart.update();
            } else {
                revenueChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Earnings ($)',
                            data: totals,
                            backgroundColor: 'rgba(0,0,0,1)',
                            borderColor: 'rgba(0,0,0,1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        });
}

// Initial chart load
fetchAndRenderRevenueChart();
</script>




<!-- SectionHeader & Table -->
<div class="mt-8">
<h2 class="text-text-light dark:text-text-dark text-xl font-bold mb-4">Last Transactions</h2>
<div class="@container">
<div class="overflow-hidden rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
<table class="w-full">
<thead class="bg-background-light dark:bg-background-dark">
<tr>
    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaction ID</th>
    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sender</th>
    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Receiver</th>
    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Currency</th>
    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
</tr>
</thead>
<tbody>
@forelse($transactions as $transaction)
<tr>
    <td class="px-6 py-4">{{ $transaction->id }}</td>
    <td class="px-6 py-4">{{ $transaction->sender?->name ?? 'N/A' }}</td>
    <td class="px-6 py-4">{{ $transaction->receiver?->name ?? 'N/A' }}</td>
    <td class="px-6 py-4">{{ number_format($transaction->amount, 2) }}</td>
    <td class="px-6 py-4">{{ strtoupper($transaction->currency) }}</td>
    <td class="px-6 py-4">
        @php
            $statusColors = [
                'completed' => 'text-green-600 bg-green-100',
                'in_progress' => 'text-yellow-600 bg-yellow-100',
                'failed' => 'text-red-600 bg-red-100',
            ];
        @endphp
        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$transaction->status] ?? 'text-gray-600 bg-gray-100' }}">
            {{ ucfirst($transaction->status) }}
        </span>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No transactions found</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</div>

@endsection