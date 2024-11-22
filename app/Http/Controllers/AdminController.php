<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\Category; 
use Illuminate\Http\Request;
use Carbon\Carbon;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Models\Product;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
       $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact( 'brands'));
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
    public function GenerateBrandThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');
        
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124);
        $img->save($destinationPath.'/'.$imageName);
    }
    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit',compact('brand'));
    }
    
    public function brand_update(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:brands,slug,'.$request->id,
        'image' => 'mimes:png,jpg,jpeg|max:2048',
    ]);

    $brand = Brand::find($request->id);
    if (!$brand) {
        return redirect()->back()->with('error', 'Brand not found.');
    }

    $brand->name = $request->name;
    $brand->slug = Str::slug($request->name);

    if ($request->hasFile('image')) {
        if (File::exists(public_path('uploads/brands').'/'.$brand->image)) {
            File::delete(public_path('uploads/brands').'/'.$brand->image);
        }

        $image = $request->file('image');
        $file_name = Carbon::now()->timestamp.'.'.$image->extension();
        $this->GenerateBrandThumbnailsImage($image, $file_name);
        $brand->image = $file_name;
    }

    $brand->save();
    return redirect()->route('admin.brands')->with('success', 'Brand updated successfully');
}

    

    public function categories(){
        
      $categories = Category::orderBy('id', 'DESC')->paginate(10);
      return view('admin.categories', compact('categories'));

    }
    public function category_add(){
        return view('admin.category-add');
    }
    public function category_store(Request $request)
    {
       
            $request->validate([
                'name' => 'required',
                'slug' => 'required|unique:categories,slug',
                'image' => 'mimes:png,jpg,jpeg|max:2048',
            ]);

                $category = new Category();
                $category->name = $request->name;
                $category->slug = Str::slug($request->name);
                $image = $request->file('image');
                $file_extention = $image->extension();
                $file_name = Carbon::now()->timestamp.'.'.$file_extention;
                $this->GenerateCategoryThumbnailsImage($image, $file_name);
                $category->image = $file_name;
                $category->save();
                return redirect()->route('admin.categories')->with('success', 'Category added successfully');
                
            }

      
    

    public function GenerateCategoryThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124);
        $img->save($destinationPath.'/'.$imageName);
    
    }
    public function category_edit($id)
    {
        
            $category = Category::find($id);
            return view('admin.category-edit', compact('category'));
        
    }
    public function category_update(Request $request){
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug'.$request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $image->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extention;
        $this->GenerateCategoryThumbnailsImage($image, $file_name);
        $category->image = $file_name;
        $category->save();


    }
    public function category_delete($id){
        $category = Category::find($id);
        if(File::exists(public_path('uploads/categories').'/'.$category->image)){
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category has been deleted successfully');
    }

  public function products()
  {
      $products = Product::orderBy('id', 'DESC')->paginate(10);
      return view('admin.products', compact('products'));
  } 
  public function product_add(){
    $categories=Category::select('id','name')->orderBy('name')->get();
    $brands=Brand::select('id','name')->orderBy('name')->get();
    return view('admin.product-add',compact('categories','brands'));
  }
  public function product_store(Request $request)
  {
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:products,slug',
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required',
        'sale_price' => 'required',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required',
        'image' => 'mimes:png,jpg,jpeg|max:2048',
        'category_id' => 'required',
        'brand_id' => 'required'
    ]);
    $product=new Product();
    $product->name=$request->name;
    $product->slug=Str::slug($request->name);
    $product->short_description=$request->short_description;
    $product->description=$request->description;
    $product->regular_price=$request->regular_price;
    $product->sale_price=$request->sale_price;
    $product->SKU=$request->SKU;
    $product->stock_status=$request->stock_status;
    $product->featured=$request->featured;
    $product->quantity=$request->quantity;;
    $product->category_id=$request->category_id;
    $product->brand_id=$request->brand_id;

    $current_timestamp=Carbon::now()->timestamp;

    if ($request->hasFile('image')) 
    {
        $image = $request->file('image');
        $imageName = $current_timestamp.'.'.$image->extension();
        $this->GenerateProductThumbnailImage($image, $imageName);
        $product->image = $imageName;
    }

    $gallery_arr = array();
    $gallery_images="";
    $counter = 1;
    if ($request->hasFile('images')) { 
        $allowedfileExtion=['jpg','png','jpeg'];
        $files=$request->file('images');
        foreach($files as $file){
            $gextension=$file->getClientOriginalExtension();
            $gcheck=in_array($gextension,$allowedfileExtion);
            if($gcheck){
            $gfileName=$current_timestamp.'_'.$counter.'.'.$gextension;
            $this->GenerateProductThumbnailImage($file, $gfileName);
            array_push($gallery_arr,$gfileName);
            $counter= $counter+1;
            }
        }
        $gallery_images=implode(',',$gallery_arr);
        }
        $product->images=$gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with('success', 'Product has been added successfully');
    }
  
    public function GenerateProductThumbnailImage($image, $imageName)
    {
        // Set paths
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
    
        // Create directories if they don't exist
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        if (!File::exists($destinationPathThumbnail)) {
            File::makeDirectory($destinationPathThumbnail, 0755, true);
        }
    
        // Process main image
        $img = Image::read($image->path());
        $img->cover(540, 689, 'top');
        $img->save($destinationPath . '/' . $imageName);
    
        // Create thumbnail from original image
        $thumbnail = Image::read($image->path());
        $thumbnail->cover(540, 689, 'top');
        $thumbnail->save($destinationPathThumbnail . '/' . $imageName);
    
        return true;
    }

  public function product_edit($id)
  {
          $product = Product::find($id);
          $categories = Category::select('id','name')->orderBy('name')->get();
          $brands = Brand::select('id','name')->orderBy('name')->get();
          return view('admin.product-edit',compact('product','categories','brands'));
  }

  public function product_update(Request $request)
  {
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:products,slug'.$request->id,
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required',
        'sale_price' => 'required',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required',
        'image' => 'mimes:png,jpg,jpeg|max:2048',
        'category_id' => 'required',
        'brand_id' => 'required'
    ]);
    $product =Product::find($request->id);
    $product->name=$request->name;
    $product->slug=Str::slug($request->name);
    $product->short_description=$request->short_description;
    $product->description=$request->description;
    $product->regular_price=$request->regular_price;
    $product->sale_price=$request->sale_price;
    $product->SKU=$request->SKU;
    $product->stock_status=$request->stock_status;
    $product->featured=$request->featured;
    $product->quantity=$request->quantity;;
    $product->category_id=$request->category_id;
    $product->brand_id=$request->brand_id;

    $current_timestamp=Carbon::now()->timestamp;

    if ($request->hasFile('image'))
    {
        if(File::exists(public_path('uploads/products').'/'.$product->image))
        {
            File::delete(public_path('uploads/products').'/'.$product->image);
        }
        if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image))
        {
            File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
        }
        $image = $request->file('image');
        $imageName = $current_timestamp.'.'.$image->extension();
        $this->GenerateProductThumbnailImage($image, $imageName);
        $product->image = $imageName;
    }
    
    $gallery = array();
    $gallery_images="";
    $counter = 1;

    if ($request->hasFile('images')) 
    { 
        foreach (explode(',', $product->images) as $ofile) {
            if (File::exists(public_path('uploads/products').'/'.$ofile)) {
                File::delete(public_path('uploads/products').'/'.$ofile);
            }
            if (File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)) {
                File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
            }
        }
        

        $allowedfileExtion=['jpg','png','jpeg'];
        $files=$request->file('images');
        foreach($files as $file){
            $gextension=$file->getClientOriginalExtension();
            $gcheck=in_array($gextension,$allowedfileExtion);
            if($gcheck){
            $gfileName=$current_timestamp.'_'.$counter.'.'.$gextension;
            $this->GenerateProductThumbnailImage($file, $gfileName);
            array_push($gallery_arr =[],$gfileName);
            $counter= $counter+1;
            }
        }
        $gallery_images=implode(',',$gallery_arr);
        $product->images=$gallery_images;
        }
        
        $product->save();
        return redirect()->route('admin.products')->with('status'.'Product has been updated successfully!');
  }

  public function product_detele($id)
  {
    $product = Product::find($id);
    if(File::exists(public_path('uploads/products').'/'.$product->image))
    {
        File::delete(public_path('uploads/products').'/'.$product->image); 
    }
    if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->image))
    {
        File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
    }

    foreach (explode(',', $product->images) as $ofile) {
        if (File::exists(public_path('uploads/products').'/'.$ofile)) {
            File::delete(public_path('uploads/products').'/'.$ofile);
        }
        if (File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)) {
            File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
        }
    }
    
    
    $product->delete();
    return redirect()->route('admin.products')->with('status','Products has been deleted successfully');
    
  }

}