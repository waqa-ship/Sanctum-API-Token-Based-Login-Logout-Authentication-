<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Post; // Assuming you want to create posts
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'description' => 'required', // Fixed the email validation rule
                'image' => 'required|mimes:png,jpg,jpeg,gif', // Fixed the 'mimies' typo to 'mimes'
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => "Validation error",
                'errors' => $validateUser->errors()->all(),
            ], 401);
        }

        // Process image upload
        $img = $request->file('image');
        $imageName = time() . '.' . $img->getClientOriginalExtension();
        $img->move(public_path('/uploads'), $imageName);

        // Create a new post (assuming you're creating a post here)
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

    public function login(Request $request)
    {
        // Validate the request data
        $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required',
            ]
        );

        // Check if validation fails
        if ($validateUser->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Authentication failed',
                'errors' => $validateUser->errors()->all(),
            ], 404);
        }

        // Attempt to log in the user
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $authUser = Auth::user();

            // Return success response with the token
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'Token' => $authUser->createToken('API token')->plainTextToken,
                'token_type' => 'bearer',
            ], 200);
        } else {
            // Return failure response if login fails
            return response()->json([
                'status' => 'fail',
                'message' => 'Email and password do not match',
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user(); 
        $user->tokens()->delete(); // Deletes all tokens associated with the user

        return response()->json([
            'status' => "true",
            'message' => 'Logged out successfully',
        ], 200);
    }
}

