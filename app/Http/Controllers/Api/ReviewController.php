<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get reviews for a product
     */
    public function index(Product $product)
    {
        return response()->json([
            'reviews' => $product->reviews()
                ->with('customer.user')
                ->latest()
                ->get(),
            'average_rating' => round($product->reviews()->avg('rating') ?? 0, 1),
            'total_reviews' => $product->reviews()->count()
        ]);
    }

    /**
     * Create or update a review
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = auth()->user()->customerProfile;

        if (!$customer) {
            return response()->json([
                'message' => 'Customer profile not found.'
            ], 404);
        }

        $review = Review::updateOrCreate(
            [
                'product_id' => $request->product_id,
                'customer_profile_id' => $customer->id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return response()->json([
            'message' => 'Review saved successfully.',
            'review' => $review
        ], 200);
    }

    /**
     * Get single review
     */
    public function show(Review $review)
    {
        return response()->json(
            $review->load('customer.user')
        );
    }

    /**
     * Update review
     */
    public function update(Request $request, Review $review)
    {
        $customer = auth()->user()->customerProfile;

        if (!$customer || $review->customer_profile_id !== $customer->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->only([
            'rating',
            'comment'
        ]));

        return response()->json([
            'message' => 'Review updated successfully.',
            'review' => $review
        ]);
    }

    /**
     * Delete review
     */
    public function destroy(Review $review)
    {
        $customer = auth()->user()->customerProfile;

        if (!$customer || $review->customer_profile_id !== $customer->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully.'
        ]);
    }
}