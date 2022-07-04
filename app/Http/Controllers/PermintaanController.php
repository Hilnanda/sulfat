<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermintaanRequest;
use App\Models\Periode;
use App\Models\Permintaan;
use RealRashid\SweetAlert\Facades\Alert;

class PermintaanController extends Controller
{
    public function index()
    {
        $permintaan = Permintaan::orderBy('id', 'desc')->get();
        $periode = Periode::orderBy('id', 'desc')->get();

        return view('permintaan', compact('permintaan', 'periode'));
    }

    public function store(PermintaanRequest $request)
    {
        Permintaan::create($request->all());

        Alert::success('Berhasil', 'Permintaan berhasil ditambahkan');
        return back();
    }

    public function show(Permintaan $permintaan)
    {
        return response()->json($permintaan);
    }

    public function update(PermintaanRequest $request, Permintaan $permintaan)
    {
        $permintaan->update($request->all());

        Alert::success('Berhasil', 'Permintaan berhasil diubah');
        return back();
    }

    public function destroy(Permintaan $permintaan)
    {
        $permintaan->delete();

        Alert::success('Berhasil', 'Permintaan berhasil dihapus');
        return back();
    }
}
