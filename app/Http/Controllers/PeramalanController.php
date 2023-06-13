<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Peramalan;
use App\Models\Permintaan;
use Exception;
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
        $peramalan = Peramalan::whereNotNull('peramalan')->get();
        $label = [];
        $aktual = [];
        $hasil = [];
        $total = 0;
        $mad = 0;
        $mse = 0;
        $mape = 0;

        if (count($peramalan) > 0) {
            foreach ($peramalan as $key => $p) {
                $label[] = $this->tgl_indo($p->periode->nama_periode);
            }

            foreach ($peramalan as $key => $p) {
                $aktual[] = $p->permintaan;
            }

            foreach ($peramalan as $key => $p) {
                $hasil[] = $p->peramalan;
            }
        }

        return view('peramalan', compact('peramalan', 'aktual', 'hasil', 'label', 'total', 'mad', 'mse', 'mape'));
    }

    public function store(Request $request)
    {
        request()->validate([
            'periode' => 'required',
            'metode' => 'required',
            'periode_awal' => 'required',
        ]);
        $period = 0;




        switch ($request->periode) {
            case '1minggu':
                $period = 7;
                break;
            case '2minggu':
                $period = 14;
                break;
            case '1bulan':
                $period = 30;
                break;
            default:
                'Periode Tidak ditemukan';
        }

        $laporan = Laporan::select('date', 'sales', 'category')->where('category', $request->category)
            ->whereDate('date', '>=', $request->periode_awal)->take($period)->get()->toArray();

            // dd($laporan);

        if ($laporan) {
            $avgs = [];

            $total = 0;
            $mad = 0;
            $mse = 0;
            $mape = 0;
            $avgs = [];

            try {
                for ($i = 0; $i < $period; $i++) {
                    $subset = array_slice($laporan, $i, $period);
                    $avg = array_sum(array_column($subset, 'sales')) / $period;
                    $forecast = round($avg, 1);
                    $round = round($forecast, 0);
                    
                    // dd(array_column($subset, 'sales'));

                    array_push($laporan, ['date' => date('Y-m-d', strtotime($laporan[$i]['date'])), 'sales' => $round]);

                    $error1 = $laporan[$i]["sales"] - $round;
                    $error2 = abs($error1);
                    $error_pangkat = pow($error1, 2);
                    $error_persen = $error2 / $round * 100;
                    $error_persen = round($error_persen, 2);
                    $total += $round;

                    $avgs[] = [
                        'date' =>  date('Y-m-d', strtotime($laporan[$i]['date'] . "+" . $period . " days")),
                        'sales' => $round,
                        'forecast' => $forecast,
                        'round' => $round,
                        'error1' => $error1,
                        'error2' => $error2,
                        'error_pangkat' => $error_pangkat,
                        'error_persen' => $error_persen,
                        'category' => $laporan[$i]['category'],
                    ];

                    $mad = array_sum(array_column($avgs, 'error2')) / $period;
                    $mse = array_sum(array_column($avgs, 'error_pangkat')) / $period;
                    $mape = array_sum(array_column($avgs, 'error_persen')) / $period;
                    $mad = round($mad, 3);
                    $mse = round($mse, 3);
                    $mape = round($mape, 3);
                    // $total = array_sum($avgs[0]['sales']);
                    
                }
            } catch (Exception $e) {
                Alert::error('Gagal', 'Data yang dihitung Kurang tepat');
                // return back();
            }
        } else {
            Alert::error('Gagal', 'Data yang dihitung kosong');
            return back();
        }
        // Tampilkan hasil SMA, Forecast, dan Round
        // foreach ($avgs as $avg) {
        //     echo "t: {$avg['date']}, Sales: {$avg['sales']}, Forecast: {$avg['forecast']}, Round: {$avg['round']}, 
        //     Error: {$avg['error1']}, [Error]: {$avg['error2']}, [Error^2]: {$avg['error_pangkat']}, [% Error]: {$avg['error_persen']}\n";
        //     echo "<br>";
        // }
        return view('peramalan', compact('avgs', 'total', 'mad', 'mse', 'mape'));
    }

    public function store1(Request $request)
    {
        request()->validate([
            'periode' => 'required',
            'metode' => 'required',
            'periode_awal' => 'required',
            'periode_akhir' => 'required',
        ]);

        $periode_awal = date('Y-m-d', strtotime('01-' . $request->periode_awal));
        $periode_akhir = date('Y-m-d', strtotime('01-' . $request->periode_akhir));

        $permintaan = Permintaan::whereHas('periode', function ($query) use ($periode_awal, $periode_akhir) {
            return $query->whereBetween('nama_periode', [$periode_awal, $periode_akhir]);
        })->get();

        if (count($permintaan) > 0) {
            if (request('metode') == 1) {
                $this->moving_average($request->periode, $request->periode_awal, $request->periode_akhir);
            }

            if (request('metode') == 2) {
                if ($request->periode != count($request->weighted)) {
                    Alert::error('Gagal', 'Ada input weighted yang belum diisi');
                    return back();
                }

                $this->weighted_moving_average($request->periode, $request->weighted, $request->periode_awal, $request->periode_akhir);
            }

            Alert::success('Berhasil', 'Data peramalan berhasil dihitung ulang');
            return back();
        } else {
            Alert::error('Gagal', 'Data yang dicetak kosong');
            return back();
        }
    }

    private function moving_average($periode, $periode_awal, $periode_akhir)
    {
        $periode_awal = date('Y-m-d', strtotime('01-' . $periode_awal));
        $periode_akhir = date('Y-m-d', strtotime('01-' . $periode_akhir));

        Peramalan::truncate();

        $permintaan = Permintaan::whereHas('periode', function ($query) use ($periode_awal, $periode_akhir) {
            return $query->whereBetween('nama_periode', [$periode_awal, $periode_akhir]);
        })->get();

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

        $jumlah = 0;
        $index = count($permintaan) - 1;

        for ($j = 0; $j < $periode; $j++) {
            $jumlah += $permintaan[$index - $j]->jumlah_permintaan;
        }

        $bulan_depan = $jumlah / $periode;

        $peramalan = Peramalan::orderBy('id', 'desc')->first();
        $peramalan->update([
            'bulan_depan' => round($bulan_depan),
        ]);
    }

    public function weighted_moving_average($periode, $weighted = [], $periode_awal, $periode_akhir)
    {
        $periode_awal = date('Y-m-d', strtotime('01-' . $periode_awal));
        $periode_akhir = date('Y-m-d', strtotime('01-' . $periode_akhir));

        Peramalan::truncate();
        $permintaan = Permintaan::whereHas('periode', function ($query) use ($periode_awal, $periode_akhir) {
            return $query->whereBetween('nama_periode', [$periode_awal, $periode_akhir]);
        })->get();

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
                $jumlah += $permintaan[$index - $j]->jumlah_permintaan * $weighted[$j];
            }

            $moving_average = $jumlah / array_sum($weighted);
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

        $jumlah = 0;
        $index = count($permintaan) - 1;

        for ($j = 0; $j < $periode; $j++) {
            $jumlah += $permintaan[$index - $j]->jumlah_permintaan * $weighted[$j];
        }

        $bulan_depan = $jumlah / array_sum($weighted);

        $peramalan = Peramalan::orderBy('id', 'desc')->first();
        $peramalan->update([
            'bulan_depan' => round($bulan_depan),
        ]);
    }

    public function hapusPeramalan()
    {
        // Peramalan::truncate();
        Alert::success('Berhasil', 'Data peramalan berhasil dikosongkan');
        return back();
    }
}
