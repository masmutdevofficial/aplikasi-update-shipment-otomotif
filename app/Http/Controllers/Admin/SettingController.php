<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\VendorAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'vendorAccessMode' => VendorAccess::mode(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vendor_access_mode' => ['required', Rule::in(VendorAccess::modes())],
        ], [
            'vendor_access_mode.required' => 'Pilih mode akses vendor.',
            'vendor_access_mode.in' => 'Mode akses vendor tidak valid.',
        ]);

        VendorAccess::setMode($data['vendor_access_mode']);

        $label = $data['vendor_access_mode'] === VendorAccess::MODE_ACTIVE
            ? 'Aktif'
            : 'Maintenance';

        return redirect()->route('admin.settings.index')
            ->with('success', "Mode akses vendor berhasil diubah menjadi {$label}.");
    }
}
