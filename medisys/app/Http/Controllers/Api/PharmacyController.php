<?php

namespace App\Http\Controllers\Api;

use App\Models\Pharmacy;
use Illuminate\Http\Request;

class PharmacyController extends FacilityAccountController
{
    protected function modelClass(): string
    {
        return Pharmacy::class;
    }

    protected function role(): string
    {
        return 'pharmacy';
    }

    protected function codePrefix(): string
    {
        return 'PHARM';
    }

    protected function label(): string
    {
        return 'Pharmacy';
    }

    protected function storeRules(): array
    {
        return ['manager_name' => 'nullable|string|max:255'];
    }

    protected function updateRules(): array
    {
        return ['manager_name' => 'nullable|string|max:255'];
    }

    public function index()
    {
        return $this->facilityIndex();
    }

    public function store(Request $request)
    {
        return $this->facilityStore($request);
    }

    public function show(Pharmacy $pharmacy)
    {
        return $this->facilityShow($pharmacy);
    }

    public function update(Request $request, Pharmacy $pharmacy)
    {
        return $this->facilityUpdate($request, $pharmacy);
    }

    public function destroy(Pharmacy $pharmacy)
    {
        return $this->facilityDestroy($pharmacy);
    }
}
