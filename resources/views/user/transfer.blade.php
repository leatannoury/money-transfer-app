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

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto">
    <header class="flex justify-end items-center p-6 border-b border-gray-200 dark:border-gray-800">
      <div class="flex items-center gap-4">
        <button class="relative text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
          <span class="material-symbols-outlined !text-2xl">notifications</span>
          <span class="absolute top-0 right-0 w-2 h-2 bg-primary rounded-full"></span>
        </button>
      </div>
    </header>

    <div class="p-8">
      <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-gray-100 text-center">
  Send Money
</h1>


     @if($errors->any())
  <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
    <ul class="list-disc pl-5">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if(session('error'))
  <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">
    {{ session('error') }}
  </div>
@endif


      @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
          {{ session('success') }}
        </div>
      @endif

      <div class="max-w-lg mx-auto bg-white dark:bg-zinc-900/50 p-8 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <form method="POST" action="{{ route('user.transfer.send') }}">
          @csrf
          <div class="space-y-6">
            <!-- Beneficiary Selection -->
            @if($beneficiaries->count() > 0)
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Select from Saved Beneficiaries</label>
              <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">people</span>
                <select 
                  id="beneficiary-select"
                  class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
                         rounded-lg bg-gray-50 dark:bg-gray-800 
                         focus:ring-2 focus:ring-primary 
                         text-gray-900 dark:text-white">
                  <option value="">-- Select a beneficiary (optional) --</option>
                  @foreach($beneficiaries as $beneficiary)
                    @php
                      // Try to find user by phone to get email
                      $user = null;
                      if ($beneficiary->phone_number) {
                          $user = \App\Models\User::where('phone', $beneficiary->phone_number)->first();
                      }
                      $email = $user ? $user->email : '';
                    @endphp
                    <option 
                      value="{{ $beneficiary->id }}"
                      data-phone="{{ $beneficiary->phone_number ?? '' }}"
                      data-email="{{ $email }}"
                      data-name="{{ $beneficiary->full_name }}">
                      {{ $beneficiary->full_name }} 
                      @if($beneficiary->phone_number)
                        ({{ $beneficiary->phone_number }})
                      @endif
                    </option>
                  @endforeach
                </select>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Select a saved beneficiary to auto-fill their information</p>
            </div>
            @endif

            <!-- Identifier -->
    <div class="flex items-center gap-6">
  <label class="flex items-center gap-2 cursor-pointer">
    <input 
      type="radio" 
      name="search_type" 
      value="email"
      id="search_type_email"
      {{ old('search_type', 'email') == 'email' ? 'checked' : '' }}
      class="text-primary focus:ring-primary border-gray-300 dark:border-gray-600 dark:bg-gray-700"
    />
    <span class="text-sm">Email</span>
  </label>

  <label class="flex items-center gap-2 cursor-pointer">
    <input 
      type="radio" 
      name="search_type" 
      value="phone"
      id="search_type_phone"
      {{ old('search_type') == 'phone' ? 'checked' : '' }}
      class="text-primary focus:ring-primary border-gray-300 dark:border-gray-600 dark:bg-gray-700"
    />
    <span class="text-sm">Phone</span>
  </label>
</div>

       

        <!-- Email Field -->
<div id="email-field">
  <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Email</label>
  <div class="relative">
    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">mail</span>
<input 
  type="email" 
  name="email" 
  id="email-input"
  placeholder="Enter receiver's email"
  value="{{ old('email') }}"
  class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
         rounded-lg bg-gray-50 dark:bg-gray-800 
         focus:ring-2 focus:ring-primary 
         text-gray-900 dark:text-white">

         @error('email')
  <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror


  </div>
</div>

<!-- Phone Field -->
<div id="phone-field" style="display:none;">
  <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Phone</label>
  <div class="relative">
    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">phone</span>
<input 
  type="text" 
  name="phone" 
  id="phone-input"
  placeholder="Enter receiver's phone number"
  value="{{ old('phone') }}"
  class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
         rounded-lg bg-gray-50 dark:bg-gray-800 
         focus:ring-2 focus:ring-primary 
         text-gray-900 dark:text-white">

         @error('phone')
  <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror


  </div>
</div>

<!-- Amount -->
<div>
  <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Amount</label>
  <div class="relative">
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
<input 
  type="number" 
  step="0.01" 
  name="amount" 
  placeholder="0.00" 
  required
  value="{{ old('amount') }}"
  class="w-full pl-7 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 
         rounded-lg bg-gray-50 dark:bg-gray-800 
         focus:ring-2 focus:ring-primary 
         text-gray-900 dark:text-white font-semibold">

@error('amount')
  <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror



  </div>
</div>


            <!-- Submit -->
            <button type="submit"
              class="w-full bg-primary text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center gap-2 hover:opacity-90 transition-opacity focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-background-dark">
              <span>Send</span>
              <span class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
          </div>
        </form>

<br>




      </div>
    </div>
  </main>
</div>

<script>
  // Toggle between Email and Phone inputs
  document.querySelectorAll('input[name="search_type"]').forEach(radio => {
      radio.addEventListener('change', function() {
          if (this.value === 'email') {
              document.getElementById('email-field').style.display = 'block';
              document.getElementById('phone-field').style.display = 'none';
          } else {
              document.getElementById('email-field').style.display = 'none';
              document.getElementById('phone-field').style.display = 'block';
          }
      });
  });

  // Toggle visibility after page load if old() exists
  window.addEventListener('DOMContentLoaded', function() {
      const selectedType = "{{ old('search_type', 'email') }}";
      if (selectedType === 'phone') {
          document.getElementById('email-field').style.display = 'none';
          document.getElementById('phone-field').style.display = 'block';
      }
  });

  // Handle beneficiary selection
  const beneficiarySelect = document.getElementById('beneficiary-select');
  if (beneficiarySelect) {
      beneficiarySelect.addEventListener('change', function() {
          const selectedOption = this.options[this.selectedIndex];
          
          if (this.value === '') {
              // Clear fields if no beneficiary selected
              document.getElementById('email-input').value = '';
              document.getElementById('phone-input').value = '';
              return;
          }
          
          const phone = selectedOption.getAttribute('data-phone');
          const email = selectedOption.getAttribute('data-email');
          const name = selectedOption.getAttribute('data-name');
          
          // If beneficiary has phone number, use phone mode
          if (phone && phone.trim() !== '') {
              // Switch to phone mode
              document.getElementById('search_type_phone').checked = true;
              document.getElementById('search_type_phone').dispatchEvent(new Event('change'));
              
              // Populate phone field
              document.getElementById('phone-input').value = phone;
          } else if (email && email.trim() !== '') {
              // If no phone but has email, use email mode
              document.getElementById('search_type_email').checked = true;
              document.getElementById('search_type_email').dispatchEvent(new Event('change'));
              
              // Populate email field
              document.getElementById('email-input').value = email;
          } else {
              // If neither phone nor email, show message
              alert('This beneficiary does not have contact information. Please enter their email or phone number manually.');
          }
      });
  }

</script>
</body>
</html>
@endsection
