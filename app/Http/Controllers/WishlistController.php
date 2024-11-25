<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart; // Ensure this import is correct
use App\Models\Product; // Import Product model

class WishlistController extends Controller
{
    public function index()
    {
        $items = Cart::instance('wishlist')->content();
        return view('wishlist', compact('items'));
    }
    public function add_to_wishlist(Request $request)
    {
        Cart::instance('wishlist')->add(
            $request->id,
            $request->name, 
            $request->quantity,
            $request->price
        )->associate(Product::class);

        return redirect()->back();
    }
    public function remove_item($rowId)
    {
        Cart::instance('wishlist')->remove($rowId);
        return redirect()->back();
    }
    public function empty_wishlist()
    {
        Cart::instance('wishlist')->destroy();
        return redirect()->back();
    }
}
