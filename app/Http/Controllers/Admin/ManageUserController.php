<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class ManageUserController extends Controller
{
public function manageUsers()
{
      $totalUsers = User::role('user')->count();
       $users = User::role('user')->select('id','name','email','phone','status')->get();
    return view('admin.manageUser.manageUser', compact('totalUsers', 'users'));

}


public function banUser($id)
{
  $user = User::with('roles')->findOrFail($id);
    // Only allow banning regular users (not admins or agents)
        $user->status = 'banned';
        $user->save();
        return redirect()->back()->with('success', "{$user->name} has been banned successfully.");
    

}
public function activateUser($id)
{
  $user = User::with('roles')->findOrFail($id);
    // Only allow banning regular users (not admins or agents)
        $user->status = 'active';
        $user->save();
        return redirect()->back()->with('success', "{$user->name} has been actviated successfully.");
}

// Show Add User Form
public function addUserForm() {
    return view('admin.manageUser.addUser');
}

// Handle Form Submission
public function storeUser(Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:6',
        'phone' => 'required|string|max:20',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password,
        'phone' => $request->phone,
        'status' => 'active',
    ]);

    $user->assignRole('user'); 

    return redirect()->route('admin.users')->with('success', 'User added successfully.');
}


public function editUserForm($id)
{
    $user = User::findOrFail($id);
    return view('admin.manageUser.editUser', compact('user'));
}

public function updateUser(Request $request, $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,'.$user->id,
        'phone' => 'required|string|max:20',
        'password' => 'nullable|confirmed|min:6',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;

    if ($request->filled('password')) {
        $user->password = $request->password;
    }

    $user->save();

    return redirect()->route('admin.users')->with('success', "{$user->name} updated successfully.");
}

}
