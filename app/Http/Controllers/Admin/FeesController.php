<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class FeesController extends Controller
{
    public function index()
    {
        // The admin is the first user with role = admin
        $admin = User::role('Admin')->first();

        return view('admin.fees.index', compact('admin'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'commission' => 'required|numeric|min:0|max:100',
        ]);

        $admin =User::role('Admin')->first();
        $admin->commission = $request->commission;
        $admin->save();

        return back()->with('success', 'Transaction fee updated!');
    }
}
