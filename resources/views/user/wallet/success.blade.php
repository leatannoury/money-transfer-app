@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="flex min-h-screen">

    @include('components.user-sidebar')

    <div class="flex-1 p-10">

        <div class="max-w-lg mx-auto bg-green-100 border border-green-300 
                    p-8 rounded-lg text-center">
            <h2 class="text-2xl font-bold text-green-800">Wallet Funded Successfully!</h2>

            <p class="mt-4 text-lg">
                <strong>${{ $amount }}</strong> has been added to your wallet.
            </p>

            <a href="{{ route('user.dashboard') }}"
               class="mt-6 inline-block bg-green-600 text-white px-5 py-2 rounded-lg">
               Go to Dashboard
            </a>
        </div>

    </div>
</div>
@endsection
