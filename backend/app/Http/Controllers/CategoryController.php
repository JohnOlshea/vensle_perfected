<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['categoryType', 'subcategories'])->get();
        return response()->json(['categories' => $categories]);
    }

    public function getSubcategories()
    {
        $subcategories = Subcategory::with(['category'])->get();
        return response()->json(['subcategories' => $subcategories]);
    }
    
	public function getCategorySubcategories(Category $category)
	{
	    $subcategories = $category->subcategories;
	    return response()->json($subcategories);
	}

    /**
     * Get all products belonging to a specific category.
     *
     * @param int $category
     * @return \Illuminate\Http\Response
     */
    public function productsByCategory($category)
    {
	try {
            $category = Category::findOrFail($category);

            $products = $category->products()->with(['images', 'displayImage', 'subcategory', 'category', 'user'])->get();

            return response()->json(['products' => $products], 200);
	} catch (\Exception $e) {
	    return response()->json(['error' => $e->getMessage()], 500);
	}	
    }

    /**
     * Get all products belonging to a specific subcategory.
     *
     * @param int $subcategory
     * @return \Illuminate\Http\Response
     */
    public function productsBySubcategory($subcategory)
    {
	    try {
		$subcategory = Subcategory::findOrFail($subcategory);
		
		$products = $subcategory->products()->with(['images', 'displayImage', 'subcategory', 'category', 'user'])->get();
		
		return response()->json(['products' => $products], 200);
	    } catch (\Exception $e) {
		return response()->json(['error' => $e->getMessage()], 500);
	    }	
    }



    /**
     * Get categories by category_type_id.
     *
     * @param int $category_type_id
     * @return \Illuminate\Http\Response
     */
    public function getCategoriesByType($category_type_id)
    {
        try {
            $categories = Category::where('category_type_id', $category_type_id)
                                ->with(['subcategories'])
                                ->get();
    
            if ($categories->isEmpty()) {
                return response()->json(['message' => 'No categories found for the specified category_type_id'], 404);
            }
    
            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get subcategories by category_type_id.
     *
     * @param int $category_type_id
     * @return \Illuminate\Http\Response
     */
    public function getSubcategoriesByType($category_type_id)
    {
        try {
            $subcategories = Subcategory::whereHas('category', function($query) use ($category_type_id) {
                                    $query->where('category_type_id', $category_type_id);
                                })
                                ->with(['category'])
                                ->get();
    
            if ($subcategories->isEmpty()) {
                return response()->json(['message' => 'No subcategories found for the specified category_type_id'], 404);
            }
    
            return response()->json(['subcategories' => $subcategories], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get products by category_type_id.
     *
     * @param int $category_type_id
     * @return \Illuminate\Http\Response
     */
    public function getProductsByType($category_type_id)
    {
        try {
            $products = Product::whereHas('category', function($query) use ($category_type_id) {
                                $query->where('category_type_id', $category_type_id);
                            })
                            ->with(['images', 'displayImage', 'subcategory', 'category', 'user'])
                            ->get();
    
            if ($products->isEmpty()) {
                return response()->json(['message' => 'No products found for the specified category_type_id'], 404);
            }
    
            return response()->json(['products' => $products], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }   



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_type_id' => 'required|exists:category_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nav_menu_image1' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'nav_menu_image2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);        

        $imageName = null;
        $imageNavMenuName1 = null;
        $imageNavMenuName2 = null;
    

        try {
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                $imageName = $this->moveAndStoreFile($imageFile);
            }
    
            if ($request->hasFile('nav_menu_image1')) {
                $imageFile1 = $request->file('nav_menu_image1');
                $imageNavMenuName1 = $this->moveAndStoreFile($imageFile1);
            }
    
            if ($request->hasFile('nav_menu_image2')) {
                $imageFile2 = $request->file('nav_menu_image2');
                $imageNavMenuName2 = $this->moveAndStoreFile($imageFile2);
            }
    
            $category = Category::create([
                'name' => $request->name,
                'category_type_id' => $request->category_type_id,
                'image' => $imageName,
                'nav_menu_image1' => $imageNavMenuName1,
                'nav_menu_image2' => $imageNavMenuName2,
            ]);
    
            return response()->json(['category' => $category], 201);
        } catch (\Exception $e) {
            Log::error('Error uploading files: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to upload files.'], 500);
        }        
        
    }

  


public function createSubcategory(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'nav_menu_image1' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'nav_menu_image2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    $imageName = null;
    $imageNavMenuName1 = null;
    $imageNavMenuName2 = null;

    try {
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = $this->moveAndStoreFile($imageFile);
        }

        if ($request->hasFile('nav_menu_image1')) {
            $imageFile1 = $request->file('nav_menu_image1');
            $imageNavMenuName1 = $this->moveAndStoreFile($imageFile1);
        }

        if ($request->hasFile('nav_menu_image2')) {
            $imageFile2 = $request->file('nav_menu_image2');
            $imageNavMenuName2 = $this->moveAndStoreFile($imageFile2);
        }

        $subcategory = Subcategory::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'image' => $imageName,
            'nav_menu_image1' => $imageNavMenuName1,
            'nav_menu_image2' => $imageNavMenuName2,
        ]);

        return response()->json(['subcategory' => $subcategory], 201);
    } catch (\Exception $e) {
        Log::error('Error uploading files: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to upload files.'], 500);
    }
}

private function moveAndStoreFile($file)
{
    $extension = $file->getClientOriginalExtension();
    $fileName = Str::random(32) . '.' . $extension;
    
    // Move the file to the desired location
    $file->move('uploads/categories/', $fileName);
    
    return $fileName;
}
  
  


public function updateSubcategory(Request $request, Subcategory $subcategory)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'nav_menu_image1' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'nav_menu_image2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }


    $subcategory->name = $request->input('name');

    if ($request->has('category_id')) {
        $subcategory->category_id = $request->input('category_id');
    }

    // Handle file uploads
    try {
        if ($request->hasFile('image')) {
            $imageName = $this->uploadImage($request->file('image'), 'uploads/categories/');
            $subcategory->image = $imageName;
        }

        if ($request->hasFile('nav_menu_image1')) {
            $imageNavMenuName1 = $this->uploadImage($request->file('nav_menu_image1'), 'uploads/categories/');
            $subcategory->nav_menu_image1 = $imageNavMenuName1;
        }

        if ($request->hasFile('nav_menu_image2')) {
            $imageNavMenuName2 = $this->uploadImage($request->file('nav_menu_image2'), 'uploads/categories/');
            $subcategory->nav_menu_image2 = $imageNavMenuName2;
        }

        $subcategory->save();

        return response()->json($subcategory);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to update subcategory.'], 500);
    }
}

private function uploadImage($file, $destinationPath)
{
    $extension = $file->getClientOriginalExtension();
    $fileName = Str::random(32) . '.' . $extension;

    // Move the uploaded file to the desired directory
    $file->move($destinationPath, $fileName);

    return $fileName;
}



    public function update(Request $request, Category $category)
    {
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'category_type_id' => 'required|exists:category_types,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'nav_menu_image1' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'nav_menu_image2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }        

    $category->name = $request->input('name');
    $category->category_type_id = $request->input('category_type_id');

    // Handle file uploads
    try {
        if ($request->hasFile('image')) {
            $imageName = $this->uploadImage($request->file('image'), 'uploads/categories/');
            $category->image = $imageName;
        }

        if ($request->hasFile('nav_menu_image1')) {
            $imageNavMenuName1 = $this->uploadImage($request->file('nav_menu_image1'), 'uploads/categories/');
            $category->nav_menu_image1 = $imageNavMenuName1;
        }

        if ($request->hasFile('nav_menu_image2')) {
            $imageNavMenuName2 = $this->uploadImage($request->file('nav_menu_image2'), 'uploads/categories/');
            $category->nav_menu_image2 = $imageNavMenuName2;
        }

        $category->save();

        return response()->json($category);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to update category.'], 500);
    }


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
        //Log::error('Error deleting category: ' . $e->getMessage());
        return response()->json(['error' => 'Internal Server Error'], 500);
    }	
}

public function deleteSubcategory(Subcategory $subcategory)
{
    $subcategory->delete();

    return response()->json(null, 204);
}    
}
