<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Http\Requests\KategoriRequest;
use Illuminate\Http\Request;

class KategoriProdukController extends Controller
{
    public $pageTitle = 'Kategori Produk';

    public function index()
    {
        $pageTitle = $this->pageTitle;
        $kategori = KategoriProduk::latest()->paginate(10);
        return view('kategori-produk.index', compact('pageTitle', 'kategori'));
    }

    public function store(KategoriRequest $request)
    {
        KategoriProduk::create([
            'nama_kategori' => $request->nama_kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'stok_minimum' => $request->stok_minimum,
        ]);

        return redirect()->route('master-data.kategori-produk.index')
            ->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function update(KategoriRequest $request, $id)
    {
        $kategori = KategoriProduk::findOrFail($id);
        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'stok_minimum' => $request->stok_minimum,
        ]);

        return redirect()->route('master-data.kategori-produk.index')
            ->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $kategori = KategoriProduk::findOrFail($id);
        $kategori->delete();

        return redirect()->route('master-data.kategori-produk.index')
            ->with('success', 'Kategori berhasil dihapus!');
    }
}
