<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class LaporanController extends Controller
{
    public function index()
    {
        // $permintaan = Permintaan::all();
        $laporanUht = Laporan::where('category', 'UHT')->orderBy('date')->get();
        $laporanFm = Laporan::where('category', 'Fresh Milk')->orderBy('date')->get();
        $laporanBeans = Laporan::where('category', 'Beans')->orderBy('date')->get();
        return view('laporan', compact('laporanUht','laporanFm','laporanBeans'));
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
