<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user_id = $request->user()->id;
        $profile =User::find($user_id);

        return response()->json([
            'status'=>200,
            'data'=>['user_data'=>$profile],
        ]);
    }
}

