<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class ManageAgentController extends Controller
{

public function  manageAgents(){
  return view('admin.manageAgent.manageAgent');
}


}
