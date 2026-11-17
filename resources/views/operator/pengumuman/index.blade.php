@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Rekap Nilai & Pengumuman Hasil (/pengumuman - Operator)</h3>
    <a href="{{ route('operator.dashboard') }}" class="btn btn-default btn-sm">Dashboard</a>
</div>

<div class="card card-primary card-outline">
    <div class="card-body">
        <form method="GET" action="{{ route('operator.pengumuman') }}" class="form-inline mb-3 flex-wrap">
            <label class="mr-2">Kegiatan</label>
            <select name="kegiatan_id" class="form-control mr-2 mb-1">
                @foreach($kegiatanList as $k)<option value="{{ $k->id }}" @selected(($kegiatanId ?? 0)==$k->id)>{{ $k->nama_kegiatan }} ({{ $k->tahun }})</option>@endforeach
            </select>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama/email/SOBAT..." class="form-control mr-2 mb-1">
            <select name="status_hasil" class="form-control mr-2 mb-1">
                <option value="">Semua Status</option>
                @foreach(['belum_ditentukan' => 'Belum ditentukan', 'lulus' => 'Lulus', 'tidak_lulus' => 'Tidak lulus', 'cadangan' => 'Cadangan'] as $val => $label)
                    <option value="{{ $val }}" @selected(($filters['status_hasil'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-default mb-1">Tampilkan</button>
            <a href="{{ route('operator.pengumuman.export-rekap', ['kegiatan_id' => $kegiatanId]) }}" class="btn btn-success mb-1 ml-2"><i class="fas fa-file-export mr-1"></i>Export Rekap Nilai (Excel)</a>
        </form>
        @if($kegiatan)
        <div class="d-flex flex-wrap align-items-center mb-3">
            <div class="mr-3">Status publikasi:
                @if($kegiatan->pengumuman_dipublikasikan)
                    <span class="badge badge-success">DIPUBLIKASIKAN</span>
                @else
                    <span class="badge badge-warning">BELUM DIPUBLIKASIKAN</span>
                @endif
            </div>
            <form method="POST" action="{{ route('operator.pengumuman.toggle', $kegiatan) }}">@csrf
                <button class="btn btn-sm {{ $kegiatan->pengumuman_dipublikasikan ? 'btn-warning' : 'btn-success' }}">
                    {{ $kegiatan->pengumuman_dipublikasikan ? 'Sembunyikan Pengumuman' : 'Publikasikan Pengumuman' }}
                </button>
            </form>
        </div>
        @endif

        <hr>
        <h5>Import Hasil Akhir (opsional)</h5>
        <div class="d-flex flex-wrap align-items-center mb-2">
            <a href="{{ route('operator.pengumuman.template-hasil') }}" class="btn btn-outline-secondary btn-sm mr-2 mb-2"><i class="fas fa-download mr-1"></i>Unduh Template Hasil</a>
            <form method="POST" action="{{ route('operator.pengumuman.import-hasil') }}" enctype="multipart/form-data" class="form-inline mb-2">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-control-file mr-2">
                <button class="btn btn-primary btn-sm">Import Hasil</button>
            </form>
        </div>
        @if(session('import_hasil_summary'))
            @php($s = session('import_hasil_summary'))
            <div class="alert alert-light border mb-0">
                <ul class="mb-1 pl-3"><li>Berhasil diperbarui: {{ $s['success'] }}</li><li>Dilewati: {{ $s['skipped'] }}</li></ul>
                @if(!empty($s['errors']))<ul class="text-danger small pl-3 mb-0" style="max-height:160px;overflow:auto">@foreach($s['errors'] as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Rekapitulasi Nilai & Penetapan Kelulusan</h3></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead><tr><th>Peserta</th><th>SOBAT/Email</th><th>Pewawancara</th><th>Nilai Akhir</th><th>Rekomendasi</th><th style="min-width:360px">Status Hasil / Catatan / Aksi</th></tr></thead>
                <tbody>
                @forelse($peserta as $p)
                    <tr>
                        <td>{{ $p->user->name ?? '-' }}</td>
                        <td>{{ $p->user->sobat_id ?? '-' }}<div class="small text-muted">{{ $p->user->email ?? '' }}</div></td>
                        <td>{{ $p->pewawancara_view ?? '-' }}</td>
                        <td>{{ $p->nilai_akhir_view ?? '-' }}</td>
                        <td>{{ $p->rekomendasi_view ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('operator.pengumuman.status', $p) }}" class="form-inline">
                                @csrf
                                <select name="status_hasil" class="form-control form-control-sm mr-1 mb-1">
                                    @foreach(['belum_ditentukan','lulus','tidak_lulus','cadangan'] as $st)
                                        <option value="{{ $st }}" @selected($p->status_hasil==$st)>{{ $st }}</option>
                                    @endforeach
                                </select>
                                <input name="catatan_hasil" value="{{ $p->catatan_hasil }}" placeholder="Catatan hasil" class="form-control form-control-sm mr-1 mb-1" style="min-width:140px">
                                <button class="btn btn-sm btn-primary mb-1">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted p-3">Belum ada peserta seleksi pada kegiatan ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
