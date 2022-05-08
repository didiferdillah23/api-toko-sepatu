<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function all(Request $req)
    {
        $id = $req->input('id');
        $limit = $req->input('limit');
        $name = $req->input('name');
        $show_products = $req->input('show_products');

        if($id){
            $category = ProductCategory::with('products')->find($id);

            if($category){
                return ResponseFormatter::success(
                    $category,
                    'Data kategori berhasil diambil'
                );
            }else{
                return ResponseFormatter::error(
                    null,
                    'Data kategori tidak ada',
                    404
                );
            }
        }

        $category = ProductCategory::query();

        if($name){
            $category->where('name', 'like', '%' . $name . '%');
        }

        if($show_products){
            $category->with('products');
        }

        return ResponseFormatter::success(
            $category->paginate($limit),
            'Data kategori berhasil diambil'
        );

    }
}
