@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Master Mitra & Peserta Terpilih</h3>
    <a href="{{ route('operator.dashboard') }}" class="btn btn-default btn-sm">Dashboard</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Import Master Mitra Kepka 2026</h3></div>
            <div class="card-body">
                <p class="text-muted small">Upsert <code>users</code> + <code>mitra_profiles</code>. Password awal = <code>sobat_id</code>, <code>must_change_password = true</code> (untuk akun baru).</p>
                <form method="POST" action="{{ route('operator.mitra.import-master') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group"><input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-control-file"></div>
                    <button class="btn btn-primary">Import Master Mitra</button>
                </form>
                @if(session('import_master_summary'))
                    @php($s = session('import_master_summary'))
                    <div class="alert alert-light border mt-3 mb-0">
                        <div class="font-weight-bold mb-1">Ringkasan Import Master Mitra</div>
                        <ul class="mb-1 pl-3">
                            <li>Baru: {{ $s['success'] }}</li>
                            <li>Diperbarui: {{ $s['updated'] }}</li>
                            <li>Dilewati: {{ $s['skipped'] }}</li>
                        </ul>
                        @if(!empty($s['errors']))
                            <div class="font-weight-bold">Rincian ({{ count($s['errors']) }}):</div>
                            <ul class="text-danger small pl-3 mb-0" style="max-height:190px;overflow:auto">
                                @foreach($s['errors'] as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-success card-outline">
            <div class="card-header"><h3 class="card-title">Import Peserta Terpilih (Kegiatan Aktif)</h3></div>
            <div class="card-body">
                <p class="text-muted small mb-1">Kegiatan aktif: <strong>{{ $kegiatanAktif->nama_kegiatan ?? 'Tidak ada' }}</strong> {{ $kegiatanAktif ? '(' . $kegiatanAktif->tahun . ')' : '' }}</p>
                <p class="text-muted small">Kolom: <code>SOBAT ID</code> dan/atau <code>Email</code>. Sistem mencocokkan ke <code>users</code> lalu membuat <code>peserta_seleksi</code>.</p>
                <a href="{{ route('operator.mitra.template-peserta') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-download mr-1"></i>Unduh Template Excel Peserta Terpilih</a>
                <form method="POST" action="{{ route('operator.mitra.import-peserta') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group"><input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-control-file"></div>
                    <button class="btn btn-success" {{ $kegiatanAktif ? '' : 'disabled' }}>Import Peserta Terpilih</button>
                </form>
                @if(session('import_peserta_summary'))
                    @php($p = session('import_peserta_summary'))
                    <div class="alert alert-light border mt-3 mb-0">
                        <div class="font-weight-bold mb-1">Ringkasan Import Peserta Terpilih {{ session('kegiatan_nama') ? '(' . session('kegiatan_nama') . ')' : '' }}</div>
                        <ul class="mb-1 pl-3">
                            <li>Berhasil ditambahkan: {{ $p['success'] }}</li>
                            <li>Dilewati: {{ $p['skipped'] }}</li>
                        </ul>
                        @if(!empty($p['errors']))
                            <div class="font-weight-bold">Rincian ({{ count($p['errors']) }}):</div>
                            <ul class="text-danger small pl-3 mb-0" style="max-height:190px;overflow:auto">
                                @foreach($p['errors'] as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Master Mitra</h3></div>
    <div class="card-body">
        <form method="GET" action="{{ route('operator.mitra.index') }}" class="mb-3">
            <div class="form-row align-items-end">
                <div class="form-group col-md-4">
                    <label>Cari (nama/SOBAT ID/email)</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Kata kunci..." />
                </div>
                <div class="form-group col-md-3">
                    <label>Kecamatan</label>
                    <select name="kecamatan" class="form-control">
                        <option value="">Semua</option>
                        @foreach($kecamatanOptions as $kec)
                            <option value="{{ $kec }}" @selected(($filters['kecamatan'] ?? '') == $kec)>{{ $kec }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Berpengalaman CAPI</label>
                    <select name="berpengalaman_capi" class="form-control">
                        <option value="">Semua</option>
                        @foreach(['Ya','Tidak'] as $opt)
                            <option value="{{ $opt }}" @selected(($filters['berpengalaman_capi'] ?? '') == $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <button class="btn btn-primary btn-block mb-md-0 mb-2">Filter</button>
                    <a href="{{ route('operator.mitra.index') }}" class="btn btn-default btn-block">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr>
                    <th>Nama</th><th>SOBAT ID</th><th>Email</th><th>No. Telp</th><th>Kecamatan</th><th>CAPI</th><th>Pendidikan</th>
                </tr></thead>
                <tbody>
                    @forelse($mitra as $m)
                        <tr>
                            <td>{{ $m->name }}</td>
                            <td>{{ $m->sobat_id }}</td>
                            <td>{{ $m->email }}</td>
                            <td>{{ $m->no_telp }}</td>
                            <td>{{ $m->mitraProfile->alamat_kecamatan ?? '-' }}</td>
                            <td>{{ $m->mitraProfile->berpengalaman_capi ?? '-' }}</td>
                            <td>{{ $m->mitraProfile->pendidikan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted p-3">Belum ada data mitra.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $mitra->links() }}</div>
    </div>
</div>
@endsection
