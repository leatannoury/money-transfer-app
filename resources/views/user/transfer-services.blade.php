@extends('layouts.app', ['noNav' => true])

@section('content')
<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Send Money - Transferly</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
 
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#000000",
            "background-light": "#f7f7f7",
            "background-dark": "#191919"
          },
          fontFamily: { display: "Manrope" },
        },
      },
    }
  </script>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100">
<div class="flex h-screen">


    @include('components.user-sidebar')

    <main class="flex-1 overflow-y-auto p-8 lg:p-12">
        
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Transfer Services</h1>
            @include('components.user-notification-center')
        </header>

<section class="mb-10 p-6 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
    <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Search Filters</h2>

    <!-- FILTER FORM -->
    <form id="filtersForm" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Destination -->
    <div>
        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Destination Country</label>
        <select name="destination" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-200 rounded-md"
                onchange="this.form.submit()">
            <option value="">Any</option>
            @foreach($countries as $country)
                <option value="{{ $country }}" {{ request('destination') == $country ? 'selected' : '' }}>{{ $country }}</option>
            @endforeach
        </select>
    </div>

   

            <!-- Payout Method -->
<div class="flex-1 min-w-[200px]">
    <label for="payout_method" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
        Payout Method
    </label>
    <select name="payout_method" id="payout_method" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white"
        onchange="this.form.submit()">
       
        <option value="any">Any Payout Method</option>
        
        @foreach($payoutMethods as $method)
            <option 
                value="{{ $method }}" 
                {{ request('payout_method') == $method ? 'selected' : '' }}>
                {{ ucfirst($method) }}
            </option>
        @endforeach
        
    </select>
</div>

            <!-- Speed -->
         <div class="flex-1 min-w-[200px]">
    <label for="speed" class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
        Speed
    </label>
   <select name="speed" id="speed" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-primary text-gray-900 dark:text-white"
        onchange="this.form.submit()">
   
            <option value="any">Any Speed</option>
        
        @foreach($speeds as $speed)
            <option 
                value="{{ $speed }}" 
                {{ request('speed') == $speed ? 'selected' : '' }}>
                {{ ucfirst($speed) }}
            </option>
        @endforeach
        </select>
</div>

            <!-- Fee Range -->
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Fee Range</label>
                <input type="range" name="fee_max" min="0" max="50"
                       value="{{ request('fee_max',25) }}"
                       class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg cursor-pointer"
                       onchange="document.getElementById('filtersForm').submit()">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>$0</span><span>$50</span>
                </div>
            </div>


            <div class="flex-1 min-w-[200px]">
            <label class="flex items-start gap-3">
                <input type="checkbox" name="promotions" value="1"
                       {{ request('promotions') ? 'checked' : '' }}
                       onchange="document.getElementById('filtersForm').submit()">
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Promotions Filter</span>
                    <p class="text-gray-500 dark:text-gray-400">Only show services with active promotions.</p>
                </div>
            </label>
            </div>

                    <div class="flex-1 min-w-[200px]">
            <a href="{{ route('user.transfer-services') }}" 
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Reset Filters
            </a>
        </div>


        </div>

       



    </form>
</section>

<section>
<h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Results ({{ $services->count() }})</h2>
<div class="space-y-4">
@foreach ($services as $service)
<div class="bg-white dark:bg-gray-900 p-6 rounded-lg border border-gray-200 dark:border-gray-800 grid grid-cols-2 lg:grid-cols-6 gap-6 items-center">

    <div class="col-span-2 lg:col-span-1">
        <p class="text-xs text-gray-500">Service</p>
        <p class="font-semibold">{{ ucfirst($service->destination_type) }} Payout</p>
    </div>

    <div>
        <p class="text-xs text-gray-500">Speed</p>
        <p class="font-medium">{{ ucfirst($service->speed) }}</p>
    </div>

    <div>
        <p class="text-xs text-gray-500">Fee</p>
        <p class="font-medium">${{ number_format($service->fee, 2) }}</p>
    </div>

<div class="col-span-2 lg:col-span-1">
    <p class="text-xs text-gray-500">Exchange Rate</p>
<p class="font-medium">
    1 USD = {{ rtrim(rtrim(number_format($service->exchange_rate, 4), '0'), '.') }} {{ $service->destination_currency }}
</p>
</div>

    <div class="col-span-2 lg:col-span-1">
        @if ($service->promotion_active)
            <p class="text-green-600 text-sm font-medium">{{ $service->promotion_text }}</p>
        @else
            <p class="text-gray-400 text-sm">—</p>
        @endif
    </div>

    <div class="col-span-2 lg:col-span-1">
        <a href="{{ route('user.transfer', [
        'transfer_service_id' => $service->id,
        'payout' => $service->payout_method
    ]) }}"
           class="w-full block text-center bg-black text-white py-2.5 px-4 rounded-md text-sm font-semibold hover:bg-gray-800 transition">
            Use this service
        </a>
    </div>

</div>
@endforeach

</div>
</section>
    </main>

</div>
@endsection

<script>
    function setFilter(name, value) {
        document.getElementById(name).value = value;
        document.getElementById('filtersForm').submit();
    }
</script>