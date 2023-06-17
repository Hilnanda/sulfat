<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Permintaan;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class LaporanController extends Controller
{
    public function index()
    {
        $permintaan = Permintaan::all();
        $laporanUht = Laporan::where('category', 'UHT')->orderBy('date')->get();
        $laporanFm = Laporan::where('category', 'Fresh Milk')->orderBy('date')->get();
        $laporanBeans = Laporan::where('category', 'Beans')->orderBy('date')->get();
        return view('laporan', compact('permintaan','laporanUht','laporanFm','laporanBeans'));
    }

    public function store(Request $request)
    {
        request()->validate([
            'periode_awal' => 'required',
            'periode_akhir' => 'required',
        ]);

        $periode_awal = date('Y-m-d', strtotime('01-' . $request->periode_awal));
        $periode_akhir = date('Y-m-d', strtotime('01-' . $request->periode_akhir));

        $permintaan = Permintaan::whereHas('periode', function ($query) use ($periode_awal, $periode_akhir) {
            return $query->whereBetween('nama_periode', [$periode_awal, $periode_akhir]);
        })->get();

        if (count($permintaan) > 0) {
            $pdf = PDF::loadview('template_laporan', compact('permintaan'));
            return $pdf->download('Laporan.pdf');
        } else {
            Alert::error('Gagal', 'Data yang dicetak kosong');
            return back();
        }

    }

    public function create(Request $request)
    {
        $laporan = new Laporan;
        $laporan->sales = $request->input('sales');
        $laporan->date = $request->input('date');
        $laporan->category = $request->input('category');
        $laporan->save();

        Alert::success('Berhasil', 'Laporan berhasil ditambahkan');
        return back();
    }

    public function destroy($id)
    {
        Laporan::find($id)->delete();
		
	// alihkan halaman ke halaman pegawai

        Alert::success('Berhasil', 'Data berhasil dihapus');
        return back();
    }
}
