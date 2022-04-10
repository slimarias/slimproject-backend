<?php

namespace Modules\Shopping\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manufacturer extends Model
{
    use HasFactory,Sluggable;
    protected $table = "shopping__manufacturers";

    protected $fillable = ["title","slug","description","sort_order","status"];

     /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class,'manufacturer_id');
    }

}
