<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends CommonController
{
    public function user(Request $request)
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first(); // This returns the first role name
        $permissions = $user->getAllPermissions()->pluck('name'); // Returns an array of permission names

        // Return the authentication token, user, role and permissions
        return $this->sendResponse([
            'user' => $user, // The authenticated user
            'role' => $role, // The role of the authenticated user
            'permissions' => $permissions, // The permissions of the authenticated user
        ], "Fetched user successfully");
    }

    public function index(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'per_page' => 'integer|min:1|max:100', // Validate 'per_page' parameter
            'search' => 'nullable|string|max:255',  // Validate 'search' parameter
        ]);

        $perPage = $request->input('per_page', 10);  // Default to 10 users per page
        $search = $request->input('search', ''); // Get the search query from request

        // Fetch users with roles, applying search filter if provided
        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhereHas('roles', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%'); // Search in roles
                        });
                });
            })
            ->orderBy('created_at', 'desc') // Optional: default sorting
            ->paginate($perPage);  // Paginate results
        return $this->sendResponse($users, "Fetched users successfully");
    }


    public function store(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed', // Ensure password confirmation
            'role_id' => 'required|exists:roles,id', // Ensure role exists
        ]);

        // Create a new user instance
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']); // Hash the password
        $user->save();

        // Assign the role to the user
        $user->roles()->attach($validated['role_id']); // Assuming a many-to-many relationship

        return $this->sendResponse($user, "User created successfully", 201);
    }

    public function delete($userId)
    {
        $user = User::where('id',$userId)->first();
        $user->delete();
        return $this->sendResponse([], "User deleted successfully");
    }

    public function show($id)
    {
        // Find the user by ID or return a 404 response if not found
        $user = User::with('roles')->where('id', $id)->first();
        // Return the user data as a JSON response
        return $this->sendResponse($user, "");
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed', // Ensure password confirmation
            'role_id' => 'required|exists:roles,id', // Assuming you're updating the role
        ]);

        // Find the user by ID
        $user = User::where('id',$id)->first();

        // Update the user's data
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Only update password if it's provided
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        // Save the user
        $user->save();

        // Update user role (assuming roles are managed through relationships)
        $user->roles()->sync([$validated['role_id']]);

        return $this->sendResponse($user, "User updated successfully");
    }
}