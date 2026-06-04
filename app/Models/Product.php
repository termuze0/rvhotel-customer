<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HotelProfile;
use App\Models\Review;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'price',
        'category',
        'preparation_time',
        'image',
        'is_available',
        'is_featured',
        'ingredients',
        'calories',
    ];

    protected $casts = [
        'price' => 'float',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'calories' => 'integer',
        'preparation_time' => 'integer',
    ];

    protected $appends = [
        'image_url',
        'average_rating',
        'review_count',
    ];

    /**
     * Product belongs to a hotel
     */
    public function hotel()
    {
        return $this->belongsTo(HotelProfile::class, 'hotel_id');
    }

    /**
     * Product has many reviews
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Available products scope
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Featured products scope
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Full image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }

    /**
     * Average rating
     */
    public function getAverageRatingAttribute()
    {
        return (float) round(
            $this->reviews()->avg('rating') ?? 0,
            1
        );
    }

    /**
     * Total reviews count
     */
    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }
}