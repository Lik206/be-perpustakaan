<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class BooksController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:api', 'isOwner'])->except(['index', 'show']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Books::with('category')->get();

        if ($books->isEmpty()) {
            return response()->json([
                'message' => 'data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil ditampilkan',
            'data' => $books
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'summary' => 'required',
            'image' => 'mimes:jpg,jpeg,png',
            'stok' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            
            $uploadResult = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'books/images'
            ]);

            $path = $uploadResult->getSecurePath();
            $publicId = $uploadResult->getPublicId();
        }

        $createBook = [
            'title' => $request->title,
            'summary' => $request->summary,
            'image' => $path,
            'stok' => $request->stok,
            'category_id' => $request->category_id,
        ];
        
        Books::create($createBook);

        return response()->json([
            'message' => 'Data berhasil ditambahkan',
            'url' => $publicId
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $book = Books::with('listBorrows')->find($id);

        if (is_null($book)) {
            return response()->json([
                'message' => "data berdasarkan id: $id tidak ditemukan"
            ], 404);
        }

        return response()->json([
            'message' => "Data detail ditampilkan",
            'data' => $book
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'summary' => 'required',
            'image' => 'mimes:jpg,jpeg,png',
            'stok' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $book = Books::find($id);

        if (is_null($book)) {
            return response()->json([
                'message' => "Data berdasarkan id: $id tidak ditemukan"
            ], 404);
        }

        if ($request->hasFile('image')) {
            if ($book->image) {
                $oldImage = basename($book->image);
                $deleteImg = explode('.', $oldImage);

                Cloudinary::destroy('books/images/' . $deleteImg[0]);
            }

            $uploadResult = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'books/images'
            ]);
    
            $path = $uploadResult->getSecurePath();
    
            $book->image = $path;
        }


        $book->title = $request->title;
        $book->summary = $request->summary;
        $book->stok = $request->stok;
        $book->category_id = $request->category_id;

        $book->save();

        return response()->json([
            'message' => 'Data berhasil di update',
            'url' => $book->image
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $book = Books::find($id);

        if (is_null($book)) {
            return response()->json([
                'message' => "data berdasarkan id: $id tidak ditemukan"
            ], 404);
        }

        if ($book->image) {
            $oldImage = basename($book->image);
            $deleteImg = explode('.', $oldImage);

            Cloudinary::destroy('books/images/'.$deleteImg[0]);
        }

        $book->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }
}
