<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\PersonalBook;

class TransactionController extends Controller
{
    // Fetch Incoming Requests (For Pengajuan Pinjam Dashboard)
    public function incomingRequests(Request $request)
    {
        $transactions = Transaction::with(['book', 'borrower'])
            ->where('owner_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($transactions);
    }

    // Fetch Outgoing Requests (For Riwayat Peminjaman Dashboard)
    public function outgoingRequests(Request $request)
    {
        $transactions = Transaction::with(['book', 'owner'])
            ->where('borrower_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($transactions);
    }

    // Submit a new loan request (From Pinjam page)
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:personal_books,id',
            'titik_temu' => 'required|string',
            'durasi_hari' => 'required|integer|min:1'
        ]);

        $book = PersonalBook::findOrFail($request->book_id);

        if ($book->user_id === $request->user()->id) {
            return response()->json(['message' => 'Tidak bisa meminjam buku sendiri'], 400);
        }

        $transaction = Transaction::create([
            'book_id' => $book->id,
            'borrower_id' => $request->user()->id,
            'owner_id' => $book->user_id,
            'status' => 'pending',
            'tanggal_pinjam_rencana' => now()->toDateString(),
            'tanggal_kembali_rencana' => now()->addDays($request->durasi_hari)->toDateString(),
            'titik_temu' => $request->titik_temu,
        ]);

        $owner = \App\Models\User::find($book->user_id);
        if ($owner) {
            $owner->notify(new \App\Notifications\BorrowRequested($request->user(), $book));
        }

        return response()->json(['message' => 'Pengajuan pinjam berhasil dikirim', 'data' => $transaction]);
    }

    // Update Request Status (Terima / Tolak)
    public function updateStatus(Request $request, Transaction $transaction)
    {
        if ($transaction->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate(['status' => 'required|in:accepted,rejected']);
        
        $transaction->update(['status' => $request->status]);

        // If accepted, update the book status to 'dipinjam'
        if ($request->status === 'accepted') {
            $transaction->book()->update(['status' => 'dipinjam', 'is_available' => false]);
        }

        return response()->json(['message' => 'Status updated', 'data' => $transaction]);
    }

    // Borrower requests to return the book
    public function requestReturn(Request $request, Transaction $transaction)
    {
        if ($transaction->borrower_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!in_array($transaction->status, ['accepted', 'on_loan'])) {
            return response()->json(['message' => 'Hanya buku yang sedang dipinjam yang bisa dikembalikan'], 400);
        }

        $transaction->update(['status' => 'pending_return']);

        $owner = \App\Models\User::find($transaction->owner_id);
        if ($owner) {
            $owner->notify(new \App\Notifications\ReturnRequested($request->user(), $transaction->book));
        }

        return response()->json(['message' => 'Permintaan pengembalian dikirim', 'data' => $transaction]);
    }

    // Owner accepts the return of the book
    public function acceptReturn(Request $request, Transaction $transaction)
    {
        if ($transaction->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($transaction->status !== 'pending_return') {
            return response()->json(['message' => 'Status transaksi tidak valid'], 400);
        }

        $transaction->update([
            'status' => 'returned',
            'tanggal_pengembalian_aktual' => now()->toDateString()
        ]);

        $transaction->book()->update([
            'status' => 'tersedia',
            'is_available' => true
        ]);

        return response()->json(['message' => 'Buku berhasil dikembalikan', 'data' => $transaction]);
    }
}
