<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Category::withCount('items');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        return response()->json($query->paginate(15));
    }

    public function show(Category $category)
    {
        return response()->json($category->load('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:categories',
            'code' => 'required|unique:categories',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);

        $this->auditService->log('create', 'Category', $category->id, null, $category->toArray(), auth()->id());

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => Rule::unique('categories')->ignore($category->id),
            'code' => Rule::unique('categories')->ignore($category->id),
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $category->toArray();
        $category->update($validated);

        $this->auditService->log('update', 'Category', $category->id, $oldValues, $category->toArray(), auth()->id());

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        if ($category->items()->count() > 0) {
            return response()->json(['message' => 'Cannot delete category with items'], 400);
        }

        $categoryData = $category->toArray();
        $category->delete();

        $this->auditService->log('delete', 'Category', $category->id, $categoryData, null, auth()->id());

        return response()->json(null, 204);
    }
}
