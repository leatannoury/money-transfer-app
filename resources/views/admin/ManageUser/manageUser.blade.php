@extends('layouts.app', ['noNav' => true])

@section('content')


<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    
@include('components.admin-sidebar')

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <header class="flex items-center justify-center border-b border-border-light dark:border-border-dark px-8 py-4 bg-card-light dark:bg-card-dark">
        <h2 class="text-xl font-bold">Manage Users</h2>
      </header>

      <div class="flex-1 p-8 overflow-y-auto">
        <!-- Header Actions -->
       <div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">User List</h3>
    <a href="{{ route('admin.users.add') }}">
        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>Add User</span>
        </button>
    </a>
</div>

        <!-- Static Table -->
        <div class="@container">
          <div class="overflow-hidden rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
            <form method="GET" action="{{ url()->current() }}" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 max-w-6xl mx-auto items-end">

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">Email</label>
    <input type="text" name="email" value="{{ request('email') }}" placeholder="example@email.com"
           class="mt-1 block w-full rounded-md border-gray-300 text-center">
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">Phone</label>
    <input type="text" name="phone" value="{{ request('phone') }}" placeholder="961..."
           class="mt-1 block w-full rounded-md border-gray-300 text-center">
  </div>

  <div class="flex flex-col items-center">
    <label class="text-sm font-medium text-center">Status</label>
    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 text-center">
      <option value="">All</option>
      <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
      <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
    </select>
  </div>

  <div class="flex justify-center items-center md:col-span-2">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
      Filter
    </button>
    <a href="{{ url()->current() }}" class="ml-2 px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">
      Reset
    </a>
  </div>

</form>
            <table class="w-full">
              <thead class="bg-background-light dark:bg-background-dark">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone Number</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
                <tbody>
          @forelse($users as $user)
          <tr class="border-t border-border-light dark:border-border-dark">
            <td class="px-6 py-4">{{ $user->name }}</td>
            <td class="px-6 py-4">{{ $user->email }}</td>
            <td class="px-6 py-4"> {{ $user->phone ? '+961 '.$user->phone : 'N/A' }}</td>
            <td class="px-6 py-4">
              @php
                $statusColors = [
                    'active' => 'text-green-600 bg-green-100',
                    'inactive' => 'text-yellow-600 bg-yellow-100',
                    'banned' => 'text-red-600 bg-red-100',
                ];
              @endphp
              <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$user->status] ?? 'text-gray-600 bg-gray-100' }}">
                {{ ucfirst($user->status ?? 'Unknown') }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex gap-2">
                 <a href="{{ route('admin.users.edit',$user->id) }}">
                <button class="px-3 py-1 bg-primary text-white rounded-lg text-xs font-medium hover:bg-blue-600 transition">Edit</button>
                 </a>
              @if($user->status !== 'banned')
            <!-- Ban button -->
            <form action="{{ route('admin.users.ban', $user->id) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to ban {{$user->name}}?');">
                @csrf
                <button type="submit" class="px-3 py-1 bg-error text-white rounded-lg text-xs font-medium hover:bg-red-600 transition">
                    Ban
                </button>
            </form>
        @else
            <!-- Activate button -->
            <form action="{{ route('admin.users.activateUser', $user->id) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to activate {{$user->name}}?');">
                @csrf
                <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded-lg text-xs font-medium hover:bg-green-700 transition">
                    Activate
                </button>
            </form>
        @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found</td>
          </tr>
          @endforelse
        </tbody>
            </table>
            <div class="p-4 flex justify-center">
              {{ $users->links('pagination::tailwind') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
