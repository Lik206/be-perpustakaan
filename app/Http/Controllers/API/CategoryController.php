<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:api', 'isOwner'])->except(['index','show']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profiles = Categories::all();

        if ($profiles->isEmpty()) {
            return response()->json([
                'message' => 'data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil ditampilkan',
            'data' => $profiles
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

        Categories::create($request->all());

        return response()->json([
            'message' => "Berhasil tambah Category"
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Categories::with('listBooks.Category')->find($id);

        if (is_null($category)) {
            return response()->json([
                'message' => "data berdasarkan id: $id tidak ditemukan"
            ], 404);
        }

        return response()->json([
            'message' => "Detail Data category",
            'data' => $category
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Categories::find($id);

        if (is_null($category)) {
            return response()->json([
                'message' => "data berdasarkan id: $id tidak ditemukan"
            ], 404);
        }

        $request->validate([
            'name' => 'required'
        ]);

        $category->name = $request['name'];

        $category->save();

        return response()->json([
            'message' => 'Data berhasil di update'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Categories::find($id);

        if (is_null($category)) {
            return response()->json([
                'message' => "data berdasarkan id: $id tidak ditemukan"
            ], 404);
        }

        $category->delete();

        return response()->json([
            'message' => "Data berhasil di hapus"
        ]); 
    }
}
