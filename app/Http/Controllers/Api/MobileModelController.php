<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Http\Resources\ModuleResource;
use App\Models\Users;
use App\Http\Resources\UsersResource;

class MobileModelController extends Controller
{
    public function users()
    {
        return UsersResource::collection(Users::all());
    }

    public function modules()
    {
        return ModuleResource::collection(Module::all());
    }
}
