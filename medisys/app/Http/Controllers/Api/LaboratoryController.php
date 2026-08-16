<?php

namespace App\Http\Controllers\Api;

use App\Models\Laboratory;
use Illuminate\Http\Request;

class LaboratoryController extends FacilityAccountController
{
    protected function modelClass(): string
    {
        return Laboratory::class;
    }

    protected function role(): string
    {
        return 'lab';
    }

    protected function codePrefix(): string
    {
        return 'LAB';
    }

    protected function label(): string
    {
        return 'Laboratory';
    }

    protected function storeRules(): array
    {
        return ['specialization' => 'nullable|string|max:255'];
    }

    protected function updateRules(): array
    {
        return ['specialization' => 'nullable|string|max:255'];
    }

    public function index()
    {
        return $this->facilityIndex();
    }

    public function store(Request $request)
    {
        return $this->facilityStore($request);
    }

    public function show(Laboratory $laboratory)
    {
        return $this->facilityShow($laboratory);
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        return $this->facilityUpdate($request, $laboratory);
    }

    public function destroy(Laboratory $laboratory)
    {
        return $this->facilityDestroy($laboratory);
    }
}
