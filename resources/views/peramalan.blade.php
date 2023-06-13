@extends('layouts.app')
@section('peramalan')
    @php
    function tgl_indo($tanggal)
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

        return $pecahkan[2] . ' ' . $bulan[(int) $pecahkan[1]] . ' ' . $pecahkan[0];    }
    @endphp
    <div class="row mb-2 mt-4">
        <div class="col">
            <div class="row">
                <div class="col mb-3">
                    <h3>Prediksi</h3>
                </div>
                @if (auth()->user()->jabatan == 'manager')
                    <div class="col mb-3">
                        <button type="button" class="btn btn-primary d-inline float-right" data-toggle="modal"
                            data-target="#tambahModal">
                            Hitung Ulang Prediksi
                        </button>
                        <a href="{{ url('hapus-peramalan') }}" class="btn btn-info d-inline float-right mr-2">
                            Kosongkan Hasil Peramalan
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- @if (count($avgs) > 0)
        <div class="row mb-3">
            <div class="col mb-3">
                <div class="card">
                    <div class="card-header text-center">
                        <h6 class="card-title">Prediksi Permintaan Bulan Depan</h6>
                    </div>
                    <div class="card-body text-center">
                        <span class="font-weight-bold">
                            {{ number_format($peramalan[count($peramalan) - 1]->bulan_depan, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="card-footer text-center"> --}}
                        {{-- text miring --}}
                        {{-- <span class="text-muted">
                            <i>Hasil peramalan diatas, adalah hasil peramalan permintaan untuk bulan depan yang bisa
                                dijadikan
                                acuan dalam pengambilan keputusan dalam menentukan jumlah produksi.</i>
                        </span>
                    </div>
                </div>
            </div> --}}
            {{-- <div class="col mb-3">
                <div class="card">
                    <div class="card-body">
                        <canvas id="myChart" width="auto" height="150%"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif --}}

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-stripped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Sales</th>
                                <th>Forecast</th>
                                <th>Round</th>
                                <th>Error</th>
                                <th>[Error]</th>
                                <th>Error^2</th>
                                <th>% Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($avgs))
                            @forelse ($avgs as $item)
                                <tr>
                                    <td class="">{{ $item['category'] }}</td>
                                    <td>{{ tgl_indo($item['date']) }}</td>
                                    <td class="">{{ $item['sales'] }}</td>
                                    <td class="">{{ $item['forecast'] }}</td>
                                    <td class="">{{ $item['round'] }}</td>
                                    <td class="">{{ $item['error1'] }}</td>
                                    <td class="">{{ $item['error2'] }}</td>
                                    <td class="">{{ $item['error_pangkat'] }}</td>
                                    <td class="">{{ $item['error_persen'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Data belum dihitung</td>
                                </tr>
                            @endforelse
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                
                                <td id="error" colspan="2"></td>
                                <td id="total">{{ $total }}</td>
                                <td id="mse" colspan="3"></td>
                                <td id="MAD">{{ $mad }}</td>
                                <td id="MSE">{{ $mse }}</td>
                                <td id="MAPE">{{ $mape }}</td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td id="ave_error" colspan="2"></td>
                                <td id="total">Total</td>
                                <td id="ave_mse" colspan="3"></td>
                                <td id="MAD">MAD</td>
                                <td id="MSE">MSE</td>
                                <td id="MAPE">MAPE</td>
                            </tr>
                            
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahModalLabel">Hitung Peramalan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('peramalan.store') }}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label>Metode</label>
                            <select type="text" class="form-control" name="metode">
                                {{-- <option value="">Pilih Metode</option> --}}
                                <option value="1">Simple Moving Average</option>
                                {{-- <option value="2">Weighted Moving Average</option> --}}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Periode</label>
                            <select type="text" class="form-control" name="periode">
                                <option value="">Pilih Periode</option>
                                <option value="1minggu"> 1 Minggu</option>
                                <option value="2minggu"> 2 Minggu</option>
                                <option value="1bulan"> 1 Bulan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Periode Awal Peramalan</label>
                            <input type="date" class="form-control" name="periode_awal">
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select type="text" class="form-control" name="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="UHT">UHT</option>
                                <option value="Fresh Milk">Fresh Milk</option>
                                <option value="Beans">Beans</option>
                                {{-- <option value="2">Weighted Moving Average</option> --}}
                            </select>
                        </div>
                        <div id="tampung"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Hitung</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        $(document).ready(function() {

            $("select[name='metode']").change(function() {
                var metode = $(this).val();
                var periode = $("select[name='periode']").val();
                if (metode != '' && periode != '') {
                    if (metode == 2) {
                        var html = '';
                        if (periode <= 3) {
                            html += `
                                <div class="form-group">
                                <label>Weighted Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>
                            <div class="form-group">
                                <label>Weighted 2 Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>
                            <div class="form-group">
                                <label>Weighted 3 Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>`;
                        } else {
                            html += `
                                <div class="form-group">
                                <label>Weighted Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>
                            <div class="form-group">
                                <label>Weighted 2 Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>
                            <div class="form-group">
                                <label>Weighted 3 Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>`;

                            for (var i = 4; i <= periode; i++) {
                                html += `
                                    <div class="form-group">
                                    <label>Weighted ${i} Bulan Lalu</label>
                                    <input type="number" class="form-control" name="weighted[]">
                                </div>
                                `;
                            }
                        }
                        $('#tampung').html(html);
                    }
                } else {
                    $('#tampung').empty();
                }
            });

            $("select[name='periode']").change(function() {
                var metode = $("select[name='metode']").val();
                var periode = $(this).val();
                if (metode != '' && periode != '') {
                    if (metode == 2) {
                        var html = '';
                        if (periode <= 3) {
                            html += `
                                <div class="form-group">
                                <label>Weighted Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>
                            <div class="form-group">
                                <label>Weighted 2 Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>
                            <div class="form-group">
                                <label>Weighted 3 Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>`;
                        } else {
                            html += `
                                <div class="form-group">
                                <label>Weighted Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>
                            <div class="form-group">
                                <label>Weighted 2 Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>
                            <div class="form-group">
                                <label>Weighted 3 Bulan Lalu</label>
                                <input type="number" class="form-control" name="weighted[]">
                            </div>`;

                            for (var i = 4; i <= periode; i++) {
                                html += `
                                    <div class="form-group">
                                    <label>Weighted ${i} Bulan Lalu</label>
                                    <input type="number" class="form-control" name="weighted[]">
                                </div>
                                `;
                            }
                        }
                        $('#tampung').html(html);
                    }
                } else {
                    $('#tampung').empty();
                }
            });
        });

        function fungsiEdit(data) {
            var data = JSON.parse(data);
            var tanggal = data.nama_peramalan.split('-');
            $('#ubahModal').modal('show');
            $('#ubahModal form').attr('action', '{{ url('peramalan') }}/' + data.id);
            $('#ubahModal form input[name=nama_peramalan]').val(tanggal[1] + '-' + tanggal[0]);
            $(".datepicker").datepicker("update", tanggal[1] + '-' + tanggal[0]);
        }
    </script>

    
@endsection
