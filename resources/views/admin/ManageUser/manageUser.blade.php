@extends('layouts.app')

@section('content')
<html class="light" lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": { DEFAULT: "#3B82F6" },
            "background-light": "#F9FAFB",
            "background-dark": "#111827",
            "card-light": "#FFFFFF",
            "card-dark": "#1F2937",
            "text-light": "#1F2937",
            "text-dark": "#F9FAFB",
            "border-light": "#E5E7EB",
            "border-dark": "#374151",
            "success": "#10B981",
            "warning": "#F59E0B",
            "error": "#EF4444",
          },
          fontFamily: { display: ["Inter", "sans-serif"] },
          borderRadius: { "xl": "1rem" }
        }
      }
    }
  </script>

  <style>
    .material-symbols-outlined {
      font-variation-settings:
      'FILL' 0,
      'wght' 400,
      'GRAD' 0,
      'opsz' 24
    }
  </style>
</head>

<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark flex flex-col">
      <div class="p-6 flex items-center gap-3 border-b border-border-light dark:border-border-dark">
        <div class="bg-primary text-white p-2 rounded-lg">
          <span class="material-symbols-outlined">dashboard</span>
        </div>
        <h1 class="text-lg font-bold">Admin Panel</h1>
      </div>

      <nav class="flex-grow p-4">
        <ul class="flex flex-col gap-2">
          <li>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">grid_view</span>
              <span class="text-sm font-medium">Dashboard</span>
            </a>
          </li>
          <li>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary/20 text-primary">
              <span class="material-symbols-outlined">group</span>
              <span class="text-sm font-semibold">Users</span>
            </a>
          </li>
          <li>
            <a href="{{ route('admin.manageAgent') }}"  class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">support_agent</span>
              <span class="text-sm font-medium">Agents</span>
            </a>
          </li>
          <li>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
              <span class="material-symbols-outlined">receipt_long</span>
              <span class="text-sm font-medium">Transactions</span>
            </a>
          </li>
        </ul>
      </nav>

      <div class="p-4 border-t border-border-light dark:border-border-dark">
        <div class="flex items-center gap-3">
          <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
               style="background-image:url('https://i.pravatar.cc/100?img=68');"></div>
          <div>
            <h2 class="text-sm font-semibold">Admin User</h2>
            <p class="text-xs text-gray-500">admin@example.com</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
      <header class="flex items-center justify-center border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
        <h2 class="text-xl font-bold">Manage Users</h2>
      </header>

      <div class="flex-1 p-8 overflow-y-auto">
        <!-- Header Actions -->
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold">User List</h3>
          <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Add User</span>
          </button>
        </div>

        <!-- Static Table -->
        <div class="@container">
          <div class="overflow-hidden rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
            <table class="w-full">
              <thead class="bg-background-light dark:bg-background-dark">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                  <td class="px-6 py-4">1</td>
                  <td class="px-6 py-4 font-medium">John Doe</td>
                  <td class="px-6 py-4">john@example.com</td>
                  <td class="px-6 py-4">User</td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold text-success bg-success/10">Active</span>
                  </td>
                  <td class="px-6 py-4 flex gap-2">
                    <button class="text-primary hover:underline">Edit</button>
                    <button class="text-error hover:underline">Delete</button>
                  </td>
                </tr>

                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                  <td class="px-6 py-4">2</td>
                  <td class="px-6 py-4 font-medium">Jane Smith</td>
                  <td class="px-6 py-4">jane@example.com</td>
                  <td class="px-6 py-4">Agent</td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold text-warning bg-warning/10">Inactive</span>
                  </td>
                  <td class="px-6 py-4 flex gap-2">
                    <button class="text-primary hover:underline">Edit</button>
                    <button class="text-error hover:underline">Delete</button>
                  </td>
                </tr>

                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                  <td class="px-6 py-4">3</td>
                  <td class="px-6 py-4 font-medium">Alice Johnson</td>
                  <td class="px-6 py-4">alice@example.com</td>
                  <td class="px-6 py-4">Admin</td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold text-error bg-error/10">Banned</span>
                  </td>
                  <td class="px-6 py-4 flex gap-2">
                    <button class="text-primary hover:underline">Edit</button>
                    <button class="text-error hover:underline">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>
</body>
</html>
@endsection
