<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory;
    protected $table = 'produk';

    protected $guarded = [];

    public function subSolution()
    {
        return $this->belongsTo(SubSolutions::class, 'sub_solution_id');
    }

    public function gambar()
    {
        return $this->hasMany(GambarSubsolution::class, 'subsolution_id');
    }

}
