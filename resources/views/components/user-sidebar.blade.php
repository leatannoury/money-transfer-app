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
            fontFamily: { display: ["Inter", "sans-serif"] },
          borderRadius: { "xl": "1rem" }
        },
      },
    }
  </script>
  <style>
    #map {
      height: 600px;
      width: 100%;
      border-radius: 0.5rem;
    }
  </style>

<aside class="w-64 flex-shrink-0 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark 
              flex flex-col fixed top-0 left-0 h-screen overflow-y-auto">

  <!-- Logo / Brand -->
  <div class="p-6 flex items-center gap-3 border-b border-border-light dark:border-border-dark mb-6">
    <div class="w-8 h-8 bg-primary rounded-full"></div>
    <span class="font-bold text-xl">Transferly</span>
  </div>

  <!-- Navigation -->
  <nav class="flex-grow p-4">
    <ul class="flex flex-col gap-2">
      <li>
        <a href="{{ route('user.dashboard') }}" 
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 
                  hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
          <span class="material-symbols-outlined">dashboard</span>
          <span>Dashboard</span>
        </a>

        <a href="{{ route('user.transfer-services') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">sync_alt</span>
          <span>Transfer Services</span>
        </a>

        <a href="{{ route('user.transfer') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">north_east</span>
          <span>Send Money</span>
        </a>
                <a href="{{ route('user.transactions') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">receipt_long</span>
          <span>Transactions</span>
        </a>
          <a href="{{ route('user.beneficiary.index') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
        <span class="material-symbols-outlined">people</span>
        <span>Beneficiaries</span>
    </a>
     <a href="{{ route('user.agents-map') }}" class="flex items-center gap-3 p-3 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800">
          <span class="material-symbols-outlined">map</span>
          <span>Agents Map</span>
        </a>
      </li>

      <li>
        <a href="{{ route('user.reviews.index') }}" 
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 
                  hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
          <span class="material-symbols-outlined">rate_review</span>
          <span>Reviews & Rates</span>
        </a>
      </li>

      <li>
        <a href="{{ route('user.chat.index') }}" 
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 
                  hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
          <span class="material-symbols-outlined">chat</span>
          <span>Support Chat</span>
        </a>
      </li>

      <li>
        <a href="{{ route('user.settings') }}" 
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 
                  hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
          <span class="material-symbols-outlined">settings</span>
          <span>Settings</span>
        </a>
      </li>
    </ul>
  </nav>

  <!-- User Info & Logout -->
  <div class="flex items-center gap-3 p-6 border-t border-border-light dark:border-border-dark">
    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>
    <div class="flex flex-col gap-1 flex-1">
      <p class="font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex items-center gap-2 px-3 py-2 mt-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition">
          <img src="{{ asset('images/logout-svgrepo-com.svg') }}" class="h-5 w-5" alt="Logout Icon" />
          <span class="text-sm font-medium">Log Out</span>
        </button>
      </form>
    </div>
  </div>

</aside>
