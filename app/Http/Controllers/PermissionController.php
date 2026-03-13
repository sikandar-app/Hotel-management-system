<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;


class PermissionController extends CommonController
{
    public function index()
    {
        $permissions = Permission::all();
        return $this->sendResponse($permissions, "success");
    }
}
