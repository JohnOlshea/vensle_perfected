<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['categoryType', 'subcategories'])->get();
        return response()->json(['categories' => $categories]);
    }

	public function getCategorySubcategories(Category $category)
	{
	    $subcategories = $category->subcategories;
	    return response()->json($subcategories);
	}    

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'category_type_id' => 'required|exists:category_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $extension = $imageFile->getClientOriginalExtension();
            $imageName = Str::random(32) . '.' . $extension;
            $imageFile->move('uploads/', $imageName);
        }

        $category = Category::create([
            'name' => $request->name,
            'category_type_id' => $request->category_type_id,
            'image' => $imageName,
        ]);

        return response()->json(['category' => $category], 201);
    }

    public function createSubcategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $extension = $imageFile->getClientOriginalExtension();
            $imageName = Str::random(32) . '.' . $extension;
            $imageFile->move('uploads/', $imageName);
        }

        $subcategory = Subcategory::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'image' => $imageName,
        ]);

        return response()->json(['subcategory' => $subcategory], 201);
    }

    public function updateSubcategory(Request $request, subcategory $subcategory)
    {
	    $this->validate($request, [
		'name' => 'required|string|max:255',
		'category_id' => 'nullable|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
	    ]);

	    $subcategory->name = $request->input('name');

	    if ($request->has('category_id')) {
		$newCategoryId = $request->input('category_id');
		$subcategory->category_id = $newCategoryId;
	    }


	//TODO: remove old image if exist
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $extension = $imageFile->getClientOriginalExtension();
            $imageName = Str::random(32) . '.' . $extension;
            $imageFile->move('uploads/', $imageName);
	    $subcategory->image = $imageName;
        }
	    $subcategory->save();

	    return response()->json($subcategory);	    
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_type_id' => 'required|exists:category_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = $category->image;

	//TODO: remove old image if exist
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $extension = $imageFile->getClientOriginalExtension();
            $imageName = Str::random(32) . '.' . $extension;
            $imageFile->move('uploads/', $imageName);
        }

        $category->update([
            'name' => $request->name,
            'category_type_id' => $request->category_type_id,
            'image' => $imageName,
        ]);

        return response()->json(['category' => $category], 200);
    }

public function destroy(Category $category)
{
    try {
        $category->delete();
        return response()->json(null, 204);
    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Category not found'], 404);
    } catch (\Exception $e) {
        Log::error('Error deleting category: ' . $e->getMessage());
        return response()->json(['error' => 'Internal Server Error'], 500);
    }	
}

public function deleteSubcategory(Subcategory $subcategory)
{
    $subcategory->delete();

    return response()->json(null, 204); // No Content
}    
}
