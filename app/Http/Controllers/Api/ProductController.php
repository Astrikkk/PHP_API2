<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getAll(Request $request): \Illuminate\Http\JsonResponse
    {
        $id=$request->query("categoryId");
        $items = Product::with(["category","product_images"])
            ->where("category_id", "=",$id)->get();
        return response()->json($items) ->header('Content-Type', 'application/json; charset=utf-8');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category_id' => 'required|exists:categories,id'
        ]);
        $product = Product::create($validatedData);

        if ($request->hasFile('images')) {
            $images = $request->file("images");
            $i = 0;
            foreach ($images as $file) {
                $fileName = $this->saveImage($file);
                ProductImage::create([
                    'name' => $fileName,
                    'priority' => $i++,
                    'product_id' => $product->id,
                ]);
            }
        }
        return response()->json($product, 201);
    }



    public function destroy(int $id)
    {
        $product = Product::find($id);
        $product->delete();
        return response()->json(['message' => 'Category and associated images deleted successfully']);
    }
}
