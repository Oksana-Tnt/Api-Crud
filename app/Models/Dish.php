<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    protected $fillable = ['name', 'description', 'price', 'available', 'img', 'category_id'];
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class)->withTimestamps();
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class)->withTimestamps();
    }
}
