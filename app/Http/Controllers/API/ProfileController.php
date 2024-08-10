<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bio' => 'required',
            'age' => 'required|integer',
        ]);
        $getUser = auth()->user();

        Profile::updateOrCreate(
            ['user_id' => $getUser->id],
            [
                'bio' => $request['bio'],
                'age' => $request['age'],
            ]
        );

        $data = Profile::where('user_id', $getUser->id)->first();

        return response()->json([
            'message' => 'Profile berhasil diubah',
            'data' => $data
        ]);
    }
}
