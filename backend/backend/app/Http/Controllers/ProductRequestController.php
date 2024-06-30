<?php

namespace App\Http\Controllers;

use App\Models\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ProductRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->only(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $product_requests = ProductRequest::orderBy('created_at', 'desc')
                    ->paginate(10);

            return response()->json($product_requests);
        } catch (\Exception $e) {
            Log::error('Error fetching product requests: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not needed for API
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric',
                'address' => 'required|string',
                'phone_number' => 'required|string',
                'description' => 'required|string',
                'type' => 'required|string',
                'ratings' => 'nullable|numeric|min:0|max:5',
                'sold' => 'nullable|integer|min:0',
                'views' => 'nullable|integer|min:0',
                'specification_ids' => 'required|array',
		//'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
		//'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
		//'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);


	    /**
	    if($image = $request->file('image'))
	    {
		    $extension = $image->getClientOriginalExtension();
		    $imageName = date('YmdHis') . "." . $extension;
		    $destination = "uploads/images/";
		    $image->move($destination, $imageName);
		    $validatedData['profile_image'] = $imageName
	    }
	    


	    if ($request->hasFile('image'));
	    {
	    	$image = $request->file('image');
		$destinationPath = storage_path('images');
		$fileName = str_random(6).time() . "." . $image->getClientOriginalExtension();
		$image->move($destinationPath, $fileName);

		$banner = new Banner();
		$banner->web_banner_profile = $fileName;
		$banner->save();
		//return response()->json...
	    }*/
 	    
            $product_request = ProductRequest::create($validatedData);
            $product_request->category()->associate($request->category_id)->save();

            //Attach specifications: adds new entries to the pivot table
            $product_request->specifications()->attach($request->specification_ids);

            return response()->json($product, 201);
        } catch (ValidationException $e) {
                return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error storing product request: ' . $e->getMessage());

	    return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\int  $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function show(string $id)
    {
        try {
            $product_request = ProductRequest::findOrFail($id);
            return response()->json($product_request);
        } catch (\Exception $e) {
            Log::error('Error fetching product request: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric',
                'address' => 'required|string',
                'phone_number' => 'required|string',
                'description' => 'required|string',
                'type' => 'required|string',
                'ratings' => 'nullable|numeric|min:0|max:5',
                'sold' => 'nullable|integer|min:0',
                'views' => 'nullable|integer|min:0',
                'specification_ids' => 'required|array',
            ]);




/**
		    ------------
		    if($request->hasfile('profile_image'))
		    {
			    $imageName = time() . '.' . $request->profile_image->extension();

			    $destination = 'public/images/'.$request->profile_image;

			    $request->profile_image->storeAs('public/images', $imageName);
			    if($product->profile_image)
			    {
				    $delete_destination = 'public/images/'.
				    Storage::delete('public/images/'.);
			    }
			    else
			    {

			    }
		    }
		    ------------

-----------
		            $student->course = $request->input('course');

        if($request->hasfile('profile_image'))
        {
            $destination = 'uploads/students/'.$student->profile_image;
            if(File::exists($destination))
            {
                File::delete($destination);
            }
            $file = $request->file('profile_image');
            $extention = $file->getClientOriginalExtension();
            $filename = time().'.'.$extention;
            $file->move('uploads/students/', $filename);
            $student->profile_image = $filename;
        }

        $student->update();
------------	    
        $fileName = '';

        if ($request->hasFile('image')) {
          $fileName = time() . '.' . $request->image->extension();
          $request->image->storeAs('public/images', $fileName);
          if ($user->image) {
            Storage::delete('public/images/' . $user->image);
          }
        } else {
          $fileName = $user->image;
	}
        $user->password = bcrypt($request->input('password'));
        $user->image = $fileName;
        $user->save();	
-----------	   
    $imageName = '';
    if ($request->hasFile('file')) {
      $imageName = time() . '.' . $request->file->extension();
      $request->file->storeAs('public/images', $imageName);
      if ($post->image) {
        Storage::delete('public/images/' . $post->image);
      }
    } else {
      $imageName = $post->image;
    }

    $postData = ['title' => $request->title, 'category' => $request->category, 'content' => $request->content, 'image' => $imageName];

    ----------
	    

-----------
	if ($image = $request->file('image')) {
            $destinationPath = 'images/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $input['image'] = "$profileImage";
        }else{
            unset($input['image']);
        }    
-------------




*/

            $product_request->update($validatedData);

            // Update category for product
            $product_request->category()->associate($request->category_id)->save();

            //Sync specifications: update the pivot table for specifications
            $product_request->specifications()->sync($request->specification_ids);

            return response()->json($product);
        } catch (\Exception $e) {
            Log::error('Error updating product request: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {

/**
	    
---------
        $student = Student::find($id);
        $destination = 'uploads/students/'.$student->profile_image;
        if(File::exists($destination))
        {
            File::delete($destination);
        }
        $student->delete();
--------
        if($product->image){
            Storage::delete('public/images/' . $user->image);
        }
        $product->delete();
--------	    
    Storage::delete('public/images/' . $post->image);
    $post->delete();
    return redirect('/post')->with(['message' => 'Post deleted successfully!', 'status' => 'info']);
---------	
*/


        try {
	    $product_request = ProductRequest::findOrFail($id);
	    $product_request->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting product request: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
