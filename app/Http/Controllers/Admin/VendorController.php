<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVendorRequest;
use App\Http\Requests\Admin\UpdateVendorRequest;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function __construct(
        protected VendorService $vendorService,
    ) {}

    /**
     * Display a listing of vendors.
     */
    public function index(Request $request): View
    {
        $vendors = $this->vendorService->getAllVendors();

        return view('admin.vendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create(): View
    {
        $availableUsers = $this->vendorService->getAvailableVendorUsers();
        $positions = Vendor::positions();

        return view('admin.vendors.create', compact('availableUsers', 'positions'));
    }

    /**
     * Store a newly created vendor.
     */
    public function store(StoreVendorRequest $request): RedirectResponse
    {
        $this->vendorService->createVendor(
            $request->validated(),
            $request->user()->id,
        );

        return redirect()
            ->route('admin.vendors.index')
            ->with('success', 'Vendor berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a vendor.
     */
    public function edit(Vendor $vendor): View
    {
        $vendor->load('user');
        $positions = Vendor::positions();

        return view('admin.vendors.edit', compact('vendor', 'positions'));
    }

    /**
     * Update the specified vendor.
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->vendorService->updateVendor(
            $vendor,
            $request->validated(),
            $request->user()->id,
        );

        return redirect()
            ->route('admin.vendors.index')
            ->with('success', 'Vendor berhasil diperbarui.');
    }

    /**
     * Remove the specified vendor.
     */
    public function destroy(Vendor $vendor): RedirectResponse
    {
        $this->vendorService->deleteVendor($vendor);

        return redirect()
            ->route('admin.vendors.index')
            ->with('success', 'Vendor berhasil dihapus.');
    }
}
