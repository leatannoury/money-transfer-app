@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Welcome, {{ $user->name }}</h2>

    <div class="card mt-3 shadow-sm">
        <div class="card-body text-center">
            <h4>Your Current Balance</h4>
            <h2 class="text-success">${{ number_format($user->balance, 2) }}</h2>
        </div>
    </div>

    <a href="{{ route('user.transfer') }}" class="btn btn-primary mt-4">Send Money</a>
    <a href="{{ route('user.transactions') }}" class="btn btn-secondary mt-4">View History</a>
</div>
@endsection
