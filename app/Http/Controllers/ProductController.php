<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(){
        return response()->json([
            'message' => "All Product",
            "data"    => Product::all()
        ],200);
    }
    public function store(Request $request){
        if ($request->has('imageurl') && ! $request->has('image')) {
            $request->merge(['image' => $request->input('imageurl')]);
        }
        if ($request->has('imageUrl') && ! $request->has('image')) {
            $request->merge(['image' => $request->input('imageUrl')]);
        }
        if ($request->has('image_url') && ! $request->has('image')) {
            $request->merge(['image' => $request->input('image_url')]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
            'description' => ['required', 'string'],
            'image' => ['required'],
        ];

        if ($request->hasFile('image')) {
            $rules['image'] = ['required', 'image', 'mimes:jpg,png,jpeg', 'max:2048'];
        } else {
            $rules['image'] = ['required', 'string', 'max:2048'];
        }

        $validate = Validator::make($request->all(), $rules);
        if($validate->fails()){
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validate->errors(),
            ], 422);
        }

        $imageUrl = null;
        if($request->hasFile('image')){
            $path = $request->file('image')->store('products','public');
            $imageUrl = asset(Storage::url($path));

            // $path = $request->file('image');
            // $imageName = time().'_'.$path->getClientOriginalExtension();
            // $path->storeAs('products',$imageName,'public');
            // $imageUrl = asset('storage/products/'.$imageName);
        } elseif ($request->filled('image')) {
            $imageUrl = $request->input('image');
        }

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imageUrl,
        ]);
        return response()->json([
            'message' => "Create Success",
            'data' => $product,
            'status' => 201
        ],201);

    }
    public function destroy($id){
        $product = Product::FindOrFail($id);
        $product->delete();
        return response()->json([
            'message' => "Delete Successfully",
            "data" => $product,
        ],200);
    }
    public function edit($id){
        $product = Product::FindOrFail($id);
        return response()->json([
            "message" => "Product Edit ",
            "data"    => $product
        ],200);
    }
    public function update(Request $request,$id){
        if ($request->has('imageurl') && ! $request->has('image')) {
            $request->merge(['image' => $request->input('imageurl')]);
        }
        if ($request->has('imageUrl') && ! $request->has('image')) {
            $request->merge(['image' => $request->input('imageUrl')]);
        }
        if ($request->has('image_url') && ! $request->has('image')) {
            $request->merge(['image' => $request->input('image_url')]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
            'description' => ['required', 'string'],
        ];

        if ($request->hasFile('image')) {
            $rules['image'] = ['sometimes', 'image', 'mimes:jpg,png,jpeg', 'max:2048'];
        } elseif ($request->filled('image')) {
            $rules['image'] = ['sometimes', 'string', 'max:2048'];
        }

        $validate = Validator::make($request->all(), $rules);
        if($validate->fails()){
            return response()->json([
                'message' => 'Validate is not found',
                'data' => $validate->errors()
            ],422);
        }

        $product = Product::FindOrFail($id);

        $imageUrl = $product->image;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products','public');
            $imageUrl = asset(Storage::url($path));
        } elseif ($request->filled('image')) {
            $imageUrl = $request->input('image');
        }

        $product->update([
            'name' => $request->name,
            'price'=> $request->price,
            'description' => $request->description,
            'image' => $imageUrl,
        ]);
        return response()->json([
            'message'=>'Update successfully',
            'data' => $product,
        ],200);
    }
}
