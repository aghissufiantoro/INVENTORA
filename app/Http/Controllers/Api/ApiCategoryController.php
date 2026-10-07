<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiCategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Kategori::withCount('products')->latest()->get();

        return $this->successResponse($categories);
    }

    public function show(Kategori $kategori): JsonResponse
    {
        $kategori->load('products');

        return $this->successResponse($kategori);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategoris,nama_kategori',
        ]);

        $kategori = Kategori::create($validated);

        return $this->successResponse($kategori, 'Category created successfully', 201);
    }

    public function update(Request $request, Kategori $kategori): JsonResponse
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategoris,nama_kategori,' . $kategori->id,
        ]);

        $kategori->update($validated);

        return $this->successResponse($kategori, 'Category updated successfully');
    }

    public function destroy(Kategori $kategori): JsonResponse
    {
        if ($kategori->products()->exists()) {
            return $this->errorResponse('Cannot delete category with products', 422);
        }

        $kategori->delete();

        return $this->successResponse(null, 'Category deleted successfully');
    }
}
