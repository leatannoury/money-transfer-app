@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Send Money</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('user.transfer.send') }}">
        @csrf
        <div class="mb-3">
            <label>Receiver</label>
            <select name="receiver_id" class="form-control" required>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>
@endsection
