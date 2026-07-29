<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendingVin;
use Illuminate\View\View;

class PendingVinController extends Controller
{
    public function index(): View
    {
        $pendingVins = PendingVin::with('vendor.user')
            ->latest('scan_date')
            ->latest()
            ->get();

        return view('admin.pending-vins.index', compact('pendingVins'));
    }
}
