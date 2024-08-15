<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post; // Corrected the model name to singular
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['posts'] = Post::all();
        return response()->json([
            'status' => "true",
            'message' => 'All Post data',
            'data' => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'image' => 'required|image', // Fixed validation rule for image
                'description' => 'required',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => "Validation error",
                'errors' => $validateUser->errors()->all(),
            ], 401);
        }

        $img = $request->file('image'); // Changed to use file() method
        $ext = $img->getClientOriginalExtension();
        $imageName = time() . '.' . $ext;
        $img->move(public_path('/uploads'), $imageName);

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Post created successfully',
            'post' => $post,
        ], 200);
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
        )->where('id', $id)->first(); // Use first() instead of get() to get a single record

        return response()->json([
            'status' => 'true',
            'message' => 'Your Single Post',
            'data' => $data,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'image' => 'nullable|image', // Changed validation rule for image
                'description' => 'required',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => "Validation error",
                'errors' => $validateUser->errors()->all(),
            ], 401);
        }

        $post = Post::find($id); // Use find() to get the specific post

        if ($post) {
            $path = public_path('/uploads');
        
            if ($request->hasFile('image')) {
                if (!empty($post->image) && file_exists($path . '/' . $post->image)) {
                    unlink($path . '/' . $post->image);
                }

                $img = $request->file('image');
                $ext = $img->getClientOriginalExtension();
                $imageName = time() . '.' . $ext;
                $img->move($path, $imageName);

                $post->image = $imageName;
            }

            $post->title = $request->title;
            $post->description = $request->description;
            $post->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Post updated successfully',
                'post' => $post,
            ], 200);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Post not found',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::find($id); // Find the post by ID

        if ($post) {
            $path = public_path('/uploads') . '/' . $post->image;
            if (file_exists($path)) {
                unlink($path);
            }

            $post->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Your post has been removed',
            ], 200);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Post not found',
            ], 404);
        }
    }
}