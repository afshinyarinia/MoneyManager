<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where(function ($query) {
            $query->where('user_id', auth()->id())
                  ->orWhere('is_system', true);
        })->get();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'type' => $request->type,
            'icon' => $request->icon,
            'color' => $request->color,
            'is_system' => false,
        ]);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        Gate::authorize('update', $category);

        $category->update($request->validated());
        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        Gate::authorize('delete', $category);

        if ($category->transactions()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with associated transactions'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
