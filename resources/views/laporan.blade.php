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

        return $pecahkan[2] . ' ' . $bulan[(int) $pecahkan[1]] . ' ' . $pecahkan[0];
    }
    @endphp
    <div class="row mt-4">
        <div class="col">
            <div class="row">
                <div class="col mb-3">
                    <h3>Laporan</h3>
                </div>
                <div class="col mb-3">
                    <button type="button" class="btn btn-primary d-inline float-right" data-toggle="modal"
                        data-target="#tambahModal">
                        Input Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="col-md-12">
                <nav>
                    <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true" style="color: black">UHT</a>
                        <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false" style="color: black">Fresh Milk</a>
                        <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false" style="color: black">Beans</a>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="card">
                            <div class="card-body table-responsive">
                                <table class="table table-stripped table-bordered w-100">
                                    <thead> 
                                        <tr>
                                            <th>Sales</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                            {{-- <th>Sisa</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($laporanUht as $item)
                                            <tr>
                                                <td>{{ $item->sales }}</td>
                                                <td>{{ tgl_indo($item->date) }}</td>
                                                <td><a href="laporan/delete/{{ $item->id }}" class="btn btn-warning">Hapus</a></td>

                                                {{-- <td>{{ $item->jumlah_impor }}</td>
                                                <td>{{ $item->jumlah_permintaan }}</td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="card">
                            <div class="card-body table-responsive">
                                <table class="table table-stripped table-bordered w-100">
                                    <thead> 
                                        <tr>
                                            <th>Sales</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                            {{-- <th>Sisa</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($laporanFm as $item)
                                            <tr>
                                                <td>{{ $item->sales }}</td>
                                                <td>{{ tgl_indo($item->date) }}</td>
                                                <td><a href="laporan/delete/{{ $item->id }}" class="btn btn-warning">Hapus</a></td>
                                                {{-- <td>{{ $item->jumlah_permintaan }}</td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                        <div class="card">
                            <div class="card-body table-responsive">
                                <table class="table table-stripped table-bordered w-100">
                                    <thead> 
                                        <tr>
                                            <th>Sales</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>

                                            {{-- <th>Permintaan</th>
                                            <th>Sisa</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($laporanBeans as $item)
                                            <tr>
                                                <td>{{ $item->sales }}</td>
                                                <td>{{ tgl_indo($item->date) }}</td>
                                                <td><a href="laporan/delete/{{ $item->id }}" class="btn btn-warning">Hapus</a></td>

                                                {{-- <td>{{ $item->jumlah_impor }}</td>
                                                <td>{{ $item->jumlah_permintaan }}</td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahModalLabel">Input Laporan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('laporan.create') }}" method="POST">
                    <div class="modal-body">
                        @csrf

                        <div class="form-group">
                            <label>Sales</label>
                            <input type="number" class="form-control" min="0" oninput="this.value = 
                            !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null" name="sales">
                        </div>
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="date">
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json"
                },
                order: false
            });

            $('.datepicker').datepicker({
                format: "mm-yyyy",
                startView: "months",
                minViewMode: "months"
            });
        });
    </script>
@endsection
