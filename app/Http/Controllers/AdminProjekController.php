<?php

namespace App\Http\Controllers;

use App\Models\Klien;
use App\Models\Projek;
use Illuminate\Support\Str;
use App\Models\GambarProjek;
use Illuminate\Http\Request;
use App\Models\KategoriProjek;
use Illuminate\Support\Facades\Storage;

class AdminProjekController extends Controller
{
    public function index()
    {
        $data = [
            'projeks' => Projek::orderBy('waktu', 'desc')->get(),
            'kliens' => Klien::all(),
            'kategoriProjeks' => KategoriProjek::all()
        ];
        return view('Admin.projek.index', $data);
    }

    public function create()
    {
        return view('Admin.projek.create', [
            'kliens' => Klien::all(),
            'kategoriProjeks' => KategoriProjek::all(),
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->file('gambar'));
        //dd($request->all());
        $request->validate([
            'nama_projek' => 'required|string|max:255',
            'klien_id' => 'required|exists:klien,id',
            'kategori_projek_id' => 'required|exists:kategori_projek,id',
            'lokasi' => 'nullable|string',
            'website' => 'nullable|string',
            'deskripsi1' => 'nullable|string',
            'deskripsi2' => 'nullable|string',
            'waktu' => 'nullable|integer|min:0',
            'white_paper' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gambar_proyek' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gambar' => 'nullable|array',
            'gambar.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $thumbnailName = null;
        if ($request->hasFile('thumbnail')) {
            $fileName = time() . '_' . $request->file('thumbnail')->getClientOriginalName();
            $thumbnailPath = $request->file('thumbnail')->storeAs('public/projek_thumbnails', $fileName);
            $thumbnailName = 'projek_thumbnails/' . $fileName;
        }

        $gambar_proyekName = null;
        if ($request->hasFile('gambar_proyek')) {
            $fileName = time() . '_' . $request->file('gambar_proyek')->getClientOriginalName();
            $gambar_proyekPath = $request->file('gambar_proyek')->storeAs('public/projek_gambar_proyeks', $fileName);
            $gambar_proyekName = 'projek_gambar_proyeks/' . $fileName;
        }

        // Buat slug berdasarkan nama_projek
        $slug = Str::slug($request->nama_projek);

        // Cek apakah slug sudah ada, jika ada tambahkan angka
        $count = Projek::where('slug', 'LIKE', "{$slug}%")->count();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }

        $projek = Projek::create([
            'nama_projek' => $request->input('nama_projek'),
            'slug' => $slug,
            'klien_id' => $request->input('klien_id'),
            'kategori_projek_id' => $request->input('kategori_projek_id'),
            'lokasi' => $request->input('lokasi'),
            'website' => $request->input('website'),
            'deskripsi1' => $request->input('deskripsi1'),
            'deskripsi2' => $request->input('deskripsi2'),
            'waktu' => $request->input('waktu'),
            'white_paper' => $request->input('white_paper'),
            'thumbnail' => $thumbnailName,
            'gambar_proyek' => $gambar_proyekName,
        ]);

        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $image) {
                $fileName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('public/projek_images', $fileName);
                $imageName = 'projek_images/' . $fileName;

                GambarProjek::create([
                    'projek_id' => $projek->id,
                    'gambar' => $imageName,
                ]);
            }
        }

        toast('Berhasil menambahkan data!', 'success');
        return redirect()->route('projek.index')->with('success', 'Projek berhasil ditambahkan.');
    }


    public function edit($id)
    {
        $projek = Projek::with('gambar')->findOrFail($id);
        $kliens = Klien::all();
        $kategoriProjeks = KategoriProjek::all();
        return view('Admin.projek.edit', compact('projek', 'kliens', 'kategoriProjeks'));
    }



    public function update(Request $request, $id)
    {
        //dd($request->filled('deleted_images'));
        $projek = Projek::with('gambar')->findOrFail($id);

        $request->validate([
            'nama_projek' => 'required|string|max:255',
            'klien_id' => 'required|exists:klien,id',
            'kategori_projek_id' => 'required|exists:kategori_projek,id',
            'lokasi' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'waktu' => 'nullable|numeric',
            'white_paper' => 'nullable|string',
            'deskripsi1' => 'nullable|string',
            'deskripsi2' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gambar_proyek' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gambar.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->input('nama_projek') !== $projek->nama_projek) {
            $slug = Str::slug($request->input('nama_projek'));

            // Cek apakah slug sudah ada di database
            $count = Projek::where('slug', 'LIKE', "{$slug}%")->where('id', '!=', $id)->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 2);
            }
        } else {
            $slug = $projek->slug; // Gunakan slug lama jika nama_projek tidak berubah
        }

        $projek->update($request->only([
            'nama_projek', 'klien_id', 'kategori_projek_id', 'lokasi', 'website', 'waktu', 'white_paper', 'deskripsi1', 'deskripsi2',
        ]));

        if ($request->hasFile('thumbnail')) {
            if ($projek->thumbnail) {
                Storage::disk('public')->delete($projek->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')->store('projek_thumbnails', 'public');
            $projek->thumbnail = $thumbnailPath;
        }

        if ($request->hasFile('gambar_proyek')) {
            if ($projek->gambar_proyek) {
                Storage::disk('public')->delete($projek->gambar_proyek);
            }
            $gambar_proyekPath = $request->file('gambar_proyek')->store('projek_gambar_proyeks', 'public');
            $projek->gambar_proyek = $gambar_proyekPath;
        }

        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $imagePath = $file->storeAs('projek_images', $fileName, 'public');
                GambarProjek::create([
                    'projek_id' => $projek->id,
                    'gambar' => $imagePath,
                ]);
            }
        }

        $projek->slug = $slug;
        $projek->save();

        toast('Berhasil mengubah data!', 'success');
        return redirect()->back()->with('success', 'Projek berhasil diubah.');
    }

    public function removeImage(Request $request, $id)
    {
        $imageId = $request->input('image_id');
        $projek = Projek::findOrFail($id);
        $image = GambarProjek::find($imageId);
        if ($image && $image->projek_id == $projek->id) {
            Storage::disk('public')->delete($image->gambar);
            $image->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Image not found or not part of the project.'], 404);
    }



    public function destroy($id)
    {
        $projek = Projek::findOrFail($id);
        if ($projek->thumbnail) {
            Storage::disk('public')->delete($projek->thumbnail);
        }
        foreach ($projek->gambar as $image) {
            if (Storage::disk('public')->exists($image->gambar)) {
                Storage::disk('public')->delete($image->gambar);
            }
            $image->delete();
        }
        $projek->delete();
        toast('Berhasil menghapus data!', 'success');
        return redirect()->back();
    }

}
