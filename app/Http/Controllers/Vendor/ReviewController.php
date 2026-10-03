<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;

class ReviewController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;
        $reviews = ProductReview::where('vendor_id', $vendor->id)
            ->with('product')
            ->latest()
            ->paginate(20);

        return view('vendor.reviews.index', compact('reviews'));
    }
}
