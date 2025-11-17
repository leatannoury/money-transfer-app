<aside class="w-64 bg-background-light dark:bg-background-dark p-6 flex flex-col justify-between border-r border-gray-200 dark:border-gray-800">
    <div>
      <div class="flex items-center gap-3 mb-12">
        <div class="w-8 h-8 bg-primary rounded-full"></div>
        <span class="font-bold text-xl">Transferly</span>
      </div>
      <nav class="flex flex-col gap-2">
        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">dashboard</span>
          <span>Dashboard</span>
        </a>
        <a href="{{ route('user.transactions') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">receipt_long</span>
          <span>Transactions</span>
        </a>
        <a href="{{ route('user.transfer') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">north_east</span>
          <span>Send Money</span>
        </a>
          <a href="{{ route('user.beneficiary.index') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
        <span class="material-symbols-outlined">people</span>
        <span>Beneficiaries</span>
    </a>
     <a href="{{ route('user.agents-map') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">map</span>
          <span>Agents Map</span>
        </a>
        <a href="{{ route('user.reviews.index') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">rate_review</span>
          <span>Reviews & Rates</span>
        </a>

        
  <a href="{{ route('user.chat.index') }}" 
     class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 
            hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
    <span class="material-symbols-outlined">chat</span>
    <span class="text-sm font-medium">Support Chat</span>
  </a>


                <a href="{{ route('user.settings') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
<span class="material-symbols-outlined">settings</span>
<p >Settings</p>
</a>
      </nav>
    </div>

   <div class="flex items-center gap-3">
  <!-- Avatar -->
  <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>

  <!-- User Info and Logout -->
  <div class="flex flex-col gap-2">
    <div>
      <p class="font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
    </div>

    <!-- Logout Button -->
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition">
        <img src="{{ asset('images/logout-svgrepo-com.svg') }}" class="h-5 w-5" alt="Logout Icon" />

        <span class="text-sm font-medium">Log Out</span>
      </button>
    </form>
  </div>
</div>

  </aside>