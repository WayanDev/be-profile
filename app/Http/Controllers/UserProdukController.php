<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\User;
use App\Models\Klien;
use App\Models\Pesan;
use App\Models\Produk;
use App\Models\Projek;
use App\Models\Service;
use App\Models\Komponen;
use App\Models\Solutions;
use App\Models\Testimoni;
use Illuminate\Support\Str;
use App\Models\SolusiProduk;
use Illuminate\Http\Request;
use App\Models\BerandaCarousel;

class UserProdukController extends Controller
{
    public function show($slug)
    {
        $produk = Produk::where('slug', $slug)->firstOrFail();

        // Konversi JSON ke array
        $solusiProdukIds = json_decode($produk->solusi_produk_id, true) ?? [];

        // Ambil data solusi produk berdasarkan ID
        $solusiProduk = SolusiProduk::whereIn('id', $solusiProdukIds)->get();
        // dd($produk);
        $komponen = Komponen::where('produk_id', $produk->id)->first();

        return view('User.produk.index', compact('produk', 'solusiProduk','komponen'));
    }


}
