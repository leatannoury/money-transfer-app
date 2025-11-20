@extends('layouts.app', ['noNav' => true])

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Verify Your Email</h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4 text-center">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4 text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" class="space-y-4">
            @csrf
            <div>
                <input 
                    type="text" 
                    name="otp" 
                    placeholder="Enter 6-digit OTP" 
                    required
                    class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <button 
                type="submit"
                class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200"
            >
                Verify OTP
            </button>
        </form>

        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('otp.resend') }}">
                @csrf
                <button 
                    type="submit"
                    class="text-blue-600 font-medium hover:underline"
                >
                    Resend OTP
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
