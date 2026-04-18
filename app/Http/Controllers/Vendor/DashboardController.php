<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ScanHistory;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $vendor = $user->vendor;

        $todayScans = 0;
        $totalScans = 0;
        $recentScans = collect();

        if ($vendor && $vendor->position) {
            $todayScans = ScanHistory::where('user_id', $user->id)
                ->whereDate('scan_date', today())
                ->count();

            $totalScans = ScanHistory::where('user_id', $user->id)->count();

            $recentScans = ScanHistory::where('user_id', $user->id)
                ->latest('scan_date')
                ->limit(5)
                ->get();
        }

        return view('vendor.dashboard', compact('vendor', 'todayScans', 'totalScans', 'recentScans'));
    }
}
