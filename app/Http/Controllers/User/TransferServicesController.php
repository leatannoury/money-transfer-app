<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Services\CurrencyService;
use App\Models\AgentNotification;
use App\Models\PaymentMethod;
use App\Models\FakeCard;
use App\Models\FakeBankAccount;
use App\Models\TransferService;

class TransferServicesController extends Controller
{


public function index(Request $request)
{
    // Get filtered services
    $query = TransferService::query();

    if ($request->destination) {
        $query->where('destination_country', $request->destination);
    }

    if ($request->payout_method && $request->payout_method !== 'any') {
        $query->where('destination_type', $request->payout_method);
    }



    if ($request->speed && $request->speed !== 'any') {
        $query->where('speed', $request->speed);
    }

    if ($request->fee_max !== null) {
        $query->where('fee', '<=', $request->fee_max);
    }

    if ($request->promotions === "1") {
        $query->where('promotion_active', true);
    }

    // NEW LOGIC: Get all services, then group by destination country and type.
    // Order by fee ascending to select the cheapest service as the representative.
    $allServices = $query
        ->orderBy('fee', 'asc')
        ->get();

    // Group services by destination country and destination type, and take the first (cheapest) of each group.
    $services = $allServices
        ->groupBy(function ($service) {
            return $service->destination_country . '|' . $service->destination_type;
        })
        ->map(function ($group) {
            return $group->first(); // Take the representative service (cheapest)
        })
        ->values();


    // Get dynamic options
    $countries = TransferService::select('destination_country')
        ->distinct()
        ->orderBy('destination_country')
        ->pluck('destination_country');

    $payoutMethods = TransferService::select('destination_type')
        ->distinct()
        ->orderBy('destination_type')
        ->pluck('destination_type');



    $speeds = TransferService::select('speed')
        ->distinct()
        ->orderBy('speed')
        ->pluck('speed');

    // Make sure 'sourceTypes' is removed from the compact call
    return view('user.transfer-services', compact(
        'services',
        'countries',
        'payoutMethods',
        'speeds',
        'request'
    ));
}

}
