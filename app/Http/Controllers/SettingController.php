<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends CommonController
{
    public function index(Request $request)
    {     
        $setting = Setting::get();
        return $this->sendResponse(SettingResource::collection($setting), "Fetched settings successfully");
    }

    public function show(Setting $setting)
    {
        return $this->sendResponse(new SettingResource($setting), "Fetched setting successfully");
    }

    public function update(Request $request, Setting $setting)
    {
        $setting->update($request->all());
        return $this->sendResponse($setting, "Updated setting successfully");
    }
}
