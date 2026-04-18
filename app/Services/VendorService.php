<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorService
{
    /**
     * Get paginated list of vendors with optional search.
     */
    public function getVendors(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Vendor::with('user')->orderBy('vendor_name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('vendor_name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%");
                  });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get all vendors without pagination (for DataTables).
     */
    public function getAllVendors(): \Illuminate\Database\Eloquent\Collection
    {
        return Vendor::with('user')->orderBy('vendor_name')->get();
    }

    /**
     * Get vendor-level users that don't have a vendor record yet.
     */
    public function getAvailableVendorUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('level', 'vendor')
            ->where('is_active', true)
            ->whereDoesntHave('vendor')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new vendor.
     */
    public function createVendor(array $data, string $createdBy): Vendor
    {
        return Vendor::create([
            'user_id' => $data['user_id'],
            'vendor_name' => $data['vendor_name'],
            'position' => $data['position'],
            'created_by' => $createdBy,
            'updated_by' => $createdBy,
        ]);
    }

    /**
     * Update an existing vendor.
     */
    public function updateVendor(Vendor $vendor, array $data, string $updatedBy): Vendor
    {
        $vendor->update([
            'vendor_name' => $data['vendor_name'],
            'position' => $data['position'],
            'updated_by' => $updatedBy,
        ]);

        return $vendor->fresh();
    }

    /**
     * Delete a vendor.
     */
    public function deleteVendor(Vendor $vendor): void
    {
        $vendor->delete();
    }
}
