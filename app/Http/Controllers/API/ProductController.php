<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $req)
    {
        $id = $req->input('id');
        $limit = $req->input('limit');
        $name = $req->input('name');
        $description = $req->input('description');
        $tags = $req->input('tags');
        $categories = $req->input('categories');
        $price_from = $req->input('price_from');
        $price_to = $req->input('price_to');

        if($id){
            $product = Product::with(['category', 'galleries'])->find($id);

            if($product){
                return ResponseFormatter::success(
                    $product,
                    'Data produk berhasil diambil'
                );
            }else{
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
            }
        }

        $product = Product::with(['category', 'galleries']);

        if($name){
            $product->where('name', 'like', '%' . $name . '%');
        }

        if($description){
            $product->where('description', 'like', '%' . $description . '%');
        }

        if($tags){
            $product->where('tags', 'like', '%' . $tags . '%');
        }

        if($price_from){
            $product->where('price', '>=', $price_from);
        }

        if($price_to){
            $product->where('price', '<=', $price_to);
        }

        if($categories){
            $product->where('price', $categories);
        }

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data produk berhasil diambil'
        );

    }
    
}
