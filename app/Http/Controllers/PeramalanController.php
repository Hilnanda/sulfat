<?php

namespace App\Http\Controllers;

use App\Models\Peramalan;
use App\Models\Permintaan;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class PeramalanController extends Controller
{
    private function tgl_indo($tanggal)
    {
        $bulan = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        $pecahkan = explode('-', $tanggal);

        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun

        return $bulan[(int) $pecahkan[1]] . ' ' . $pecahkan[0];
    }

    public function index()
    {
        $peramalan = Peramalan::all();
        $label = [];
        $aktual = [];
        $hasil = [];

        if (count($peramalan) > 0) {
            foreach ($peramalan as $p) {
                $label[] = $this->tgl_indo($p->periode->nama_periode);
            }

            foreach ($peramalan as $p) {
                $aktual[] = $p->permintaan;
            }

            foreach ($peramalan as $p) {
                $hasil[] = $p->peramalan;
            }
        }

        return view('peramalan', compact('peramalan', 'aktual', 'hasil', 'label'));
    }

    public function store(Request $request)
    {
        if (request('metode') == 1) {
            $this->moving_average($request->periode);
        }

        Alert::success('Berhasil', 'Peramalan berhasil ditambahkan');
        return back();
    }

    private function moving_average($periode)
    {
        Peramalan::truncate();
        $permintaan = Permintaan::get();
        $periode = intval($periode);

        for ($i = 0; $i <= $periode - 1; $i++) {
            Peramalan::create([
                'id_periode' => $permintaan[$i]->id_periode,
                'id_permintaan' => $permintaan[$i]->id,
                'permintaan' => $permintaan[$i]->jumlah_permintaan,
            ]);
        }

        for ($i = $periode; $i < count($permintaan); $i++) {
            $jumlah = 0;

            $index = $i - 1;

            for ($j = 0; $j < $periode; $j++) {
                $jumlah += $permintaan[$index - $j]->jumlah_permintaan;
            }

            $moving_average = $jumlah / $periode;
            $moving_average = intval($moving_average);
            $error = $permintaan[$i]->jumlah_permintaan - $moving_average;
            $error = intval($error);
            $map = abs($error) / $permintaan[$i]->jumlah_permintaan * 100;
            $map = round($map);

            Peramalan::create([
                'id_periode' => $permintaan[$i]->id_periode,
                'id_permintaan' => $permintaan[$i]->id,
                'permintaan' => $permintaan[$i]->jumlah_permintaan,
                'peramalan' => $moving_average,
                'error' => $error,
                'mad' => abs($error),
                'mse' => pow($error, 2),
                'mape' => $map,
            ]);
        }
    }
}
