<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\NotificationService;


class ManageAgentController extends Controller
{

public function  manageAgents(Request $request){
    $totalUsers = User::role('Agent')->count();
       $query = User::role('Agent')->select('id','name','email','phone','status','city','commission');
         if ($request->filled('email')) {
        $query->where('email', 'like', "%{$request->email}%");
    }

    
    if ($request->filled('phone')) {
        $query->where('phone', 'like', "%{$request->phone}%");
    }

   
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

   
    $users = $query->paginate(10)->withQueryString();
       
      
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
        'phone' => ['required','string','regex:/^\d{8}$/','unique:users,phone'],
        'city' => 'required|string|max:20',  
        'commission' => 'required|numeric|min:0',
    ], [
        'phone.regex' => 'Enter exactly 8 digits.',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password,
        // Store only the 8 digits (no +961)
        'phone' => $request->phone,
        'city'=>$request->city,
        'commission'=>$request->commission,
        'status' => 'active',
    ]);

    $user->assignRole('Agent'); 

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
        // Lebanese phone: exactly 8 digits (UI shows +961 prefix)
        'phone' => ['required','string','regex:/^\d{8}$/','unique:users,phone,'.$user->id],
        'password' => 'nullable|confirmed|min:6',
    ], [
        'phone.regex' => 'Enter exactly 8 digits.',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    // Store only the 8 digits (no +961)
    $user->phone = $request->phone;

    if ($request->filled('password')) {
        $user->password = $request->password;
    }

    $user->save();

    return redirect()->route('admin.agents')->with('success', "{$user->name} updated successfully.");
}

/**
 * View all agent requests
 */
public function agentRequests(Request $request)
{
    $query = User::where('agent_request_status', 'pending')
        ->whereDoesntHave('roles', function($q) {
            $q->whereIn('name', ['Admin', 'Agent']);
        })
        ->select('id', 'name', 'email', 'phone', 'city', 'agent_request_status', 'created_at');

    // Search filters
    if ($request->filled('email')) {
        $query->where('email', 'like', "%{$request->email}%");
    }

    if ($request->filled('phone')) {
        $query->where('phone', 'like', "%{$request->phone}%");
    }

    if ($request->filled('city')) {
        $query->where('city', 'like', "%{$request->city}%");
    }

    $requests = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
    $totalRequests = User::where('agent_request_status', 'pending')->count();

    return view('admin.ManageAgent.agentRequests', compact('requests', 'totalRequests'));
}

/**
 * Approve agent request
 */
public function approveAgentRequest($id, Request $request)
{
    $user = User::findOrFail($id);

    // Validate that user has a pending request
    if ($user->agent_request_status !== 'pending') {
        return redirect()->back()->with('error', 'This user does not have a pending request.');
    }

    // Validate required fields
    $request->validate([
        'commission' => 'required|numeric|min:0|max:100',
    ], [
        'commission.required' => 'Commission rate is required.',
        'commission.numeric' => 'Commission must be a number.',
        'commission.min' => 'Commission cannot be negative.',
        'commission.max' => 'Commission cannot exceed 100%.',
    ]);

    // Remove User role if it exists, then assign Agent role
    if ($user->hasRole('User')) {
        $user->removeRole('User');
    }
    
    // Assign agent role
    $user->assignRole('Agent');

    // Update user status
    $user->agent_request_status = 'approved';
    $user->commission = $request->commission;
    $user->status = 'active';
    $user->save();

    // Remove this user from any beneficiary lists (they are no longer a regular user)
    Beneficiary::where('beneficiary_user_id', $user->id)->delete();

    NotificationService::notifyAdmins(
        'Agent Request Approved',
        "{$user->name}'s agent request has been approved."
    );

    return redirect()->route('admin.agents.requests')->with('success', "{$user->name}'s agent request has been approved.");
}

/**
 * Reject agent request
 */
public function rejectAgentRequest($id)
{
    $user = User::findOrFail($id);

    // Validate that user has a pending request
    if ($user->agent_request_status !== 'pending') {
        return redirect()->back()->with('error', 'This user does not have a pending request.');
    }

    // Update request status
    $user->agent_request_status = 'rejected';
    $user->save();

    NotificationService::notifyAdmins(
        'Agent Request Rejected',
        "{$user->name}'s agent request has been rejected."
    );

    return redirect()->route('admin.agents.requests')->with('success', "{$user->name}'s agent request has been rejected.");
}

}
