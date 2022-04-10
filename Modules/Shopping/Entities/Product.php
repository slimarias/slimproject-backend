<?php

namespace Modules\Shopping\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;

class Product extends Model
{
    use HasFactory,Sluggable;
    protected $table = "shopping__products";

    protected $fillable = ["name","slug","description","sku","quantity","price","discount","date_available","added_by_id","category_id","parent_id","manufacturer_id","related_ids","sort_order","status"];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }
}
