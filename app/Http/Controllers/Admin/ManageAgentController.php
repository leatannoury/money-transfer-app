<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class ManageAgentController extends Controller
{

public function  manageAgents(){
    $totalUsers = User::role('agent')->count();
       $users = User::role('agent')->select('id','name','email','phone','status','city','commission')->get();
    return view('admin.ManageAgent.manageAgent', compact('totalUsers', 'users'));

 
}

public function banAgent($id)
{
  $user = User::with('roles')->findOrFail($id);
    // Only allow banning regular users (not admins or agents)
        $user->status = 'banned';
        $user->save();
        return redirect()->back()->with('success', "{$user->name} has been banned successfully.");
    

}
public function activateAgent($id)
{
  $user = User::with('roles')->findOrFail($id);
    // Only allow banning regular users (not admins or agents)
        $user->status = 'active';
        $user->save();
        return redirect()->back()->with('success', "{$user->name} has been actviated successfully.");
}

// Show Add User Form
public function addAgentForm() {
    return view('admin.manageAgent.addAgent');
}

// Handle Form Submission
public function storeAgent(Request $request) {
   $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:6',
        'phone' => 'required|string|max:20',
        'city' => 'required|string|max:20',  
        'commission' => 'required|numeric|min:0',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password,
        'phone' => $request->phone,
        'city'=>$request->city,
        'commission'=>$request->commission,
        'status' => 'active',
    ]);

    $user->assignRole('agent'); 

    return redirect()->route('admin.agents')->with('success', 'User added successfully.');
}


public function editAgentForm($id)
{
    $user = User::findOrFail($id);
    return view('admin.manageAgent.editAgent', compact('user'));
}

public function updateAgent(Request $request, $id)
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

    return redirect()->route('admin.agents')->with('success', "{$user->name} updated successfully.");
}


}
