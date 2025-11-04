@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Transaction History</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>To/From</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($transactions as $txn)
            <tr>
                <td>{{ $txn->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    @if($txn->sender_id == Auth::id())
                        Sent to: {{ $txn->receiver->name }}
                    @else
                        Received from: {{ $txn->sender->name }}
                    @endif
                </td>
                <td>${{ $txn->amount }}</td>
                <td>{{ ucfirst($txn->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
