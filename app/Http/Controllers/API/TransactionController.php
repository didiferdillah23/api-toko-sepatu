<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $req)
    {
        $id = $req->input('id');
        $limit = $req->input('limit', 6);
        $status = $req->input('status');

        if($id){
            $transaction = Transaction::with(['items.product'])->find($id);

            if($transaction){
                return ResponseFormatter::success(
                    $transaction,
                    'Data transakasi berhasil diambil'
                );
            }else{
                return ResponseFormatter::success(
                    null,
                    'Data transakasi tidak ada',
                    404
                );
            }
        }

        $transaction = Transaction::with(['items.product'])->where('user_id', Auth::user()->id);

        if($status){
            $transaction->where('status', $status);
        }

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data transaksi berhasil diambil'
        );

    }

    public function checkout(Request $req)
    {
        $req->validate([
            'items' => 'required|array',
            'items.*.id' => 'exists:products,id',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPPED',
        ]);

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'address' => $req->address,
            'total_price' => $req->total_price,
            'shipping_price' => $req->shipping_price,
            'status' => $req->status,
        ]);

        foreach($req->items as $product) {
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transactions_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }

        return ResponseFormatter::success($transaction->load('items.product'), 'Transaksi berhasil');
    }
}
