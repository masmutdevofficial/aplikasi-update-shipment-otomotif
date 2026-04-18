<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ScanHistory;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        $histories = ScanHistory::where('user_id', $user->id)
            ->latest('scan_date')
            ->get();

        return view('vendor.history.index', compact('histories'));
    }
}
