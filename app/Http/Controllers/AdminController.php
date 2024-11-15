<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name'=> 'required',
            'slug'=> 'required unique:brands,slug',
            'image'=> 'mimes:png,jpg,jpeg|max:2048'
        ]);

$category = new Category();
$category->name = $request->name;
$category->slug = Str::slug($request->name);
$image = $request->file('image');
$file_extention = $request->file('image')->extension();
$file_name = Carbon::now()->timestamp.'.'.$file_extention;
$this->GenerateCategoryThumbnailsImage($image,$file_name);
$category->image =$file_name;
$category->save();
return redirect()->route('admin.categories')->with('status','Category has been added succesfully!');

   }
   public function GenerateCategoryThumbnailsImage($image, $imageName)
    {
      $destinationPath = Public_path('uploads/categories');
      $image = image::read($image->path());
      $image->cover(124,124,"top");
      $image->resize(124,124,function($constraint){    
        $constraint->aspectRatio();
      })->save($destinationPath.'/'.$imageName);
    }
}