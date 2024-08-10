<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use Illuminate\Http\Request;

class RolesController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:api', 'isOwner']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Roles::all();

        if ($roles->isEmpty()) {
            return response()->json([
                'message' => 'data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil ditampilkan',
            'data' => $roles
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        Roles::create($request->all());

        return response()->json([
            'message' => "Data berhasil ditambahkan"
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Roles::find($id);

        if (is_null($role)) {
            return response()->json([
                'message' => "data berdasarkan id: $id tidak ditemukan"
            ], 404);
        }

        $request->validate([
            'name' => 'required'
        ]);

        $role->name = $request['name'];

        $role->save();

        return response()->json([
            'message' => 'Data berhasil di update'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Roles::find($id);

        if (is_null($role)) {
            return response()->json([
                'message' => "data berdasarkan id: $id tidak ditemukan"
            ], 404);
        }

        $role->delete();

        return response()->json([
            'message' => "Data berhasil di hapus"
        ]);
    }
}
