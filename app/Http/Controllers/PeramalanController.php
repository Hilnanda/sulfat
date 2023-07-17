<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Peramalan;
use App\Models\Permintaan;
use DivisionByZeroError;
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
        $peramalan = [];
        $label = [];
        $aktual = [];
        $hasil = [];
        $total = 0;
        $mad = 0;
        $mse = 0;
        $mape = 0;

        

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

                }
            } catch (DivisionByZeroError $e) {
                Alert::error('Gagal', 'Terdapat pembagian 0');
            } catch (Exception $e) {
                Alert::error('Gagal', 'Data yang dihitung Kurang tepat');
            }
        } else {
            Alert::error('Gagal', 'Data yang dihitung kosong');
            return back();
        }
        return view('peramalan', compact('avgs', 'total', 'mad', 'mse', 'mape'));
    }

    

    public function hapusPeramalan()
    {
        Alert::success('Berhasil', 'Data peramalan berhasil dikosongkan');
        return back();
    }
}
