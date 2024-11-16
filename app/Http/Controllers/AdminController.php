<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\Category; 
use Illuminate\Http\Request;
use Carbon\Carbon;
use Intervention\Image\Laravel\Facades\Image;




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

    public function add_Brand()
    {
        return view('admin.brand-add');
    }
    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extention;
        $this->GenerateBrandThumbnailsImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('success', 'Brand added successfully');

    }
    public function GenerateBrandThumbnailsImage($image, $imageName){
       
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124, function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
      
    }
    public function categories(){
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view ('admin.categories', compact('categories'));
    }
} 