<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\User;
use App\Models\Klien;
use App\Models\Pesan;
use App\Models\Projek;
use App\Models\Service;
use App\Models\Solutions;
use App\Models\Testimoni;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BerandaCarousel;
use App\Models\KategoriTopik;

class UserPublikasiController extends Controller
{
    //
    function index()
    {
        $data = [
            'kategori_topiks' => KategoriTopik::all(),
        ];

        return view('User.publikasi.index', $data);
    }

    public function show($slug)
    {

        $projek = Projek::whereRaw("LOWER(REPLACE(nama_projek, ' ', '-')) = ?", [Str::lower($slug)])
                    ->with('gambar')
                    ->firstOrFail();
        $data = [
            'kliens' => Klien::orderBy('created_at', 'desc')->get(),
            'carousels' => BerandaCarousel::orderBy('created_at', 'desc')->get(),
            'solutionss' => Solutions::with('subSolutions')->orderBy('created_at', 'asc')->get(),
            'projek' => $projek,
            'testimonis' => Testimoni::where('projek_id', $projek->id)->with('projek.klien')->orderBy('created_at', 'desc')->get(),
        ];
        return view('User.proyek.show', $data);
    }

}
