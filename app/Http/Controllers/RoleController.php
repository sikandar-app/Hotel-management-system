<?php
namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends CommonController
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return $this->sendResponse($roles, "success");
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $role = Role::create(['name' => $request->name,'guard_name' => 'web']);
        
        if(isset($request->permissions)){
            $role->syncPermissions(permissions: $request->permissions);
        }

        return $this->sendResponse($role, "success", 201 );
    }

    public function show($id)
    {
        $role = Role::where('id',$id)->with('permissions')->first();
        return $this->sendResponse($role, "success");
    }

    public function assignPermissions(Request $request, $roleId)
    {
        $request->validate([
            'permissions' => 'required',
        ]);

        $role = Role::where('id',$roleId)->first();

        $role->syncPermissions(permissions: $request->permissions);
        return $this->sendResponse($role, "Permissions assigned successfully");
    }

    public function delete($roleId)
    {
        $role = Role::where('id',$roleId)->first();

        $role->delete();
        return $this->sendResponse($role, "Role deleted successfully");
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->sendError(['errors' => $validator->errors()], "Role deleted successfully", 422);
        }

        $role = Role::where('id', $id)->first();
        
        $role->update($request->only('name'));
        
        // Update permissions
        if ($request->has('permissions')) {
            // Sync permissions
            $role->syncPermissions($request->input('permissions'));
        }
        
        return $this->sendResponse($role, "Role Updated successfully");
    }
}
