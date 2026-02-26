<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(15));
    }

    public function show(Supplier $supplier)
    {
        return response()->json($supplier->load(['items', 'purchaseOrders']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'code' => 'required|unique:suppliers',
            'email' => 'nullable|email|unique:suppliers',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $supplier = Supplier::create($validated);

        $this->auditService->log('create', 'Supplier', $supplier->id, null, $supplier->toArray(), auth()->id());

        return response()->json($supplier, 201);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'string',
            'code' => Rule::unique('suppliers')->ignore($supplier->id),
            'email' => Rule::unique('suppliers')->ignore($supplier->id),
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $oldValues = $supplier->toArray();
        $supplier->update($validated);

        $this->auditService->log('update', 'Supplier', $supplier->id, $oldValues, $supplier->toArray(), auth()->id());

        return response()->json($supplier);
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->items()->count() > 0 || $supplier->purchaseOrders()->count() > 0) {
            return response()->json(['message' => 'Cannot delete supplier with existing data'], 400);
        }

        $supplierData = $supplier->toArray();
        $supplier->delete();

        $this->auditService->log('delete', 'Supplier', $supplier->id, $supplierData, null, auth()->id());

        return response()->json(null, 204);
    }
}
