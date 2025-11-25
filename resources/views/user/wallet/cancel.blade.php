@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="flex min-h-screen">

    @include('components.user-sidebar')

    <div class="flex-1 p-10">

        <div class="max-w-lg mx-auto bg-red-100 border border-red-300 
                    p-8 rounded-lg text-center">
            <h2 class="text-2xl font-bold text-red-800">Payment Cancelled</h2>

            <p class="mt-4 text-lg">
                Your wallet was not funded.
            </p>

            <a href="{{ route('user.wallet.add') }}"
               class="mt-6 inline-block bg-red-600 text-white px-5 py-2 rounded-lg">
               Try Again
            </a>
        </div>

    </div>
</div>
@endsection
