<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseCOntroller as BaseCOntroller ;
use App\Http\Controllers\Controller;
use App\Models\Post; // Corrected the model name to singular
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends BaseCOntroller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['posts'] = Post::all();

        return $this->sendResponse($data, "All Post data");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'image' => 'required|mimes:png,jpg,jpeg,gif|max:2048', // Added max size validation
                'description' => 'required',
            ]
        );
    
        if ($validateUser->fails()) {
        
            return $this->sendError('Validation error', $validateUser->errors()->all());
        }
    
        // Check if the image file is present in the request
        if ($request->hasFile('image')) {
            // Get the image file
            $image = $request->file('image');
            
            // Generate a unique file name
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            // Move the image to the desired directory
            $image->move(public_path('upload'), $imageName);
        } else {
            return $this->sendError('Validation error', $validateUser->errors()->all());
        }
    
        // Create the post with the image name
        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName,
        ]);
         return $this->sendResponse($post, "Post created successfully");
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
{
    // Retrieve the post by ID
    $data = Post::select(
        'id',
        'title',
        'description',
        'image'
    )->where('id', $id)->first(); // Changed to first() and fixed the where clause

    // Check if the post exists
    if (!$data) {
        return $this->sendError('Validation error', $validateUser->errors()->all());
    }

    
     return $this->sendResponse($data, "Your Single Post");
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    
    // Validate the incoming request
    $validateUser = Validator::make(
        $request->all(),
        [
            'title' => 'required',
            'image' => 'nullable|mimes:png,jpg,jpeg,gif',
            'description' => 'required',
        ]
    );

    // Return validation errors if any
    if ($validateUser->fails()) {
        return $this->sendError('Validation error', $validateUser->errors()->all());
    }

    // Find the post by ID
    $post = Post::find($id);

    // If post not found, return an error
    if (!$post) {
      
        return $this->sendError('Validation error', $validateUser->errors()->all());
    }

    // Initialize the image name to the existing one
    $imageName = $post->image;

    // Check if a new image has been uploaded
    if ($request->hasFile('image')) {
        // Delete the old image if it exists
        $old_file = public_path('/uploads/' . $post->image);
        if ($post->image && file_exists($old_file)) {
            unlink($old_file);
        }

        // Upload the new image
        $img = $request->file('image');
        $ext = $img->getClientOriginalExtension();
        $imageName = time() . '.' . $ext;
        $img->move(public_path('/uploads'), $imageName);
    }

    // Update the post with the new data
    $post->update([
        'title' => $request->title,
        'description' => $request->description,
        'image' => $imageName,
    ]);

    // Return success response
  
    return $this->sendResponse($post, "Post updated successfully");
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
{
    // Find the post by ID and get the image path
    $post = Post::find($id);

    // If post not found, return an error
    if (!$post) {
        
        return $this->sendError('Validation error', $validateUser->errors()->all());
    }

    // If the post has an associated image, delete the image file
    if ($post->image) {
        $filePath = public_path('/uploads/' . $post->image);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete the post from the database
    $post->delete();

    // Return a success response
    
    return $this->sendResponse($post, "Your post has been removed successfully");

}
}
