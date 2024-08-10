<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Books;
use App\Models\Borrows;
use Illuminate\Http\Request;

class BorrowsController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:api']);
        $this->middleware('isOwner')->only('index');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $borrows = Borrows::with(['user', 'book'])->get();

        if ($borrows->isEmpty()) {
            return response()->json([
                'message' => 'data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil ditampilkan',
            'data' => $borrows
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'load_date' => 'required|date',
            'borrow_date' => 'required|date',
            'book_id' => 'required|exists:books,id|uuid',
            'user_id' => 'required|exists:users,id|uuid',
        ]);
        $getUser = auth()->user();

        $previousBorrow = Borrows::where('user_id', $getUser->id)->first();

        if ($previousBorrow) {
            // Jika ada peminjaman sebelumnya, tambahkan stok buku lama
            $previousBook = Books::find($previousBorrow->book_id);
            if ($previousBook) {
                $previousBook->stok += 1;
                $previousBook->save();
            }
        }

        $book = Books::find($request->book_id);
  
        if ($book && $book->stok > 0) {
            // Kurangi stok buku
            $book->stok -= 1;
            $book->save();

            // Update atau buat entri peminjaman
            Borrows::updateOrCreate(
                ['user_id' => $getUser->id],
                [
                    'load_date' => $request['load_date'],
                    'borrow_date' => $request['borrow_date'],
                    'book_id' => $request['book_id'],
                    'user_id' => $request['user_id'],
                ]
            );

            $data = Borrows::where('user_id', $getUser->id)->first();

            return response()->json([
                'message' => 'Peminjaman berhasil dibuat/diubah',
                'data' => $data
            ]);
        } else {
            $book->stok += 1;
            return response()->json([
                'message' => 'Stok buku tidak tersedia',
                // 'data' => $book
            ], 400);
        }
    }

}
