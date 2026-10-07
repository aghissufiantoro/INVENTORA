<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class kategori extends Model
{
    protected $fillable = ['nama'];

    public function products()
    {
        return $this->hasMany(Product::class, 'kategori_id');
    }
}
