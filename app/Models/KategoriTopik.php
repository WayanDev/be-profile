<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriTopik extends Model
{
    use HasFactory;
    protected $table = 'kategori_topik';

    protected $guarded = [];

    public function artikels()
    {
        return $this->hasMany(DetailArtikel::class, 'kategori_topik_id');
    }


}
