@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Manajemen Jadwal Wawancara (/jadwal)</h3>
    <a href="{{ route('dashboard') }}" class="btn btn-default btn-sm">Dashboard</a>
</div>

@if($canManage)
<div class="row">
    <div class="col-lg-7">
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Input Manual Jadwal</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('jadwal.store') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-12"><label>Kegiatan</label>
                            <select name="kegiatan_id" required class="form-control">
                                @foreach($kegiatanList as $k)<option value="{{ $k->id }}" @selected(($kegiatanId ?? 0)==$k->id)>{{ $k->nama_kegiatan }} ({{ $k->tahun }})</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group col-12"><label>Peserta Terpilih</label>
                            <select name="peserta_seleksi_id" required class="form-control">
                                <option value="">Pilih peserta...</option>
                                @foreach($peserta as $p)<option value="{{ $p->id }}">{{ $p->user->name ?? '-' }} ({{ $p->user->email ?? '' }} / {{ $p->user->sobat_id ?? '-' }})</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group col-12"><label>Pewawancara</label>
                            <select name="pewawancara_id" required class="form-control">
                                <option value="">Pilih pewawancara...</option>
                                @foreach($pewawancara as $pw)<option value="{{ $pw->id }}">{{ $pw->name }} ({{ $pw->email }})</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6"><label>Tanggal</label><input type="date" name="tanggal" required class="form-control"></div>
                        <div class="form-group col-md-6"><label>Sesi</label><input name="sesi" class="form-control"></div>
                        <div class="form-group col-md-6"><label>Jam Mulai</label><input type="time" name="jam_mulai" required class="form-control"></div>
                        <div class="form-group col-md-6"><label>Jam Selesai</label><input type="time" name="jam_selesai" required class="form-control"></div>
                        <div class="form-group col-md-6"><label>Metode</label><select name="metode" required class="form-control"><option value="luring">Luring</option><option value="daring">Daring</option></select></div>
                        <div class="form-group col-md-6"><label>Lokasi/Link</label><input name="lokasi_atau_link" required class="form-control"></div>
                        <div class="form-group col-12"><label>Catatan</label><textarea name="catatan_jadwal" class="form-control"></textarea></div>
                    </div>
                    <button class="btn btn-primary">Simpan Jadwal</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-success card-outline">
            <div class="card-header"><h3 class="card-title">Import Jadwal Wawancara (Excel)</h3></div>
            <div class="card-body">
                <p class="text-muted small">Kegiatan aktif: <strong>{{ $kegiatanAktif->nama_kegiatan ?? 'Tidak ada' }}</strong>. Mapping: peserta terpilih (sobat_id/email) ke pewawancara (email), tanggal, jam, sesi, metode, lokasi/link.</p>
                <a href="{{ route('jadwal.template') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-download mr-1"></i>Unduh Template Jadwal</a>
                <form method="POST" action="{{ route('jadwal.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group"><input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-control-file"></div>
                    <button class="btn btn-success" {{ $kegiatanAktif ? '' : 'disabled' }}>Import Jadwal</button>
                </form>
                @if(session('import_jadwal_summary'))
                    @php($s = session('import_jadwal_summary'))
                    <div class="alert alert-light border mt-3 mb-0">
                        <ul class="mb-1 pl-3"><li>Berhasil: {{ $s['success'] }}</li><li>Diperbarui: {{ $s['updated'] ?? 0 }}</li><li>Dilewati: {{ $s['skipped'] }}</li></ul>
                        @if(!empty($s['errors']))<ul class="text-danger small pl-3 mb-0" style="max-height:160px;overflow:auto">@foreach($s['errors'] as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Jadwal</h3>
        <div class="card-tools">
            <form method="GET" action="{{ route('jadwal') }}" class="form-inline">
                <select name="kegiatan_id" class="form-control form-control-sm mr-2"><option value="">Semua</option>@foreach($kegiatanList as $k)<option value="{{ $k->id }}" @selected(($kegiatanId ?? '')==$k->id)>{{ $k->nama_kegiatan }}</option>@endforeach</select>
                <button class="btn btn-sm btn-default">Tampilkan</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead><tr><th>Tanggal/Jam</th><th>Peserta</th><th>Pewawancara</th><th>Metode</th><th>Lokasi/Link</th><th>Sesi</th><th>Nilai</th>@if($canManage)<th></th>@endif</tr></thead>
                <tbody>
                @forelse($jadwal as $j)
                    <tr>
                        <td>{{ optional($j->tanggal)->format('Y-m-d') }}<div class="small text-muted">{{ $j->jam_mulai }}-{{ $j->jam_selesai }}</div></td>
                        <td>{{ $j->pesertaSeleksi->user->name ?? '-' }}<div class="small text-muted">{{ $j->pesertaSeleksi->user->email ?? '' }}</div></td>
                        <td>{{ $j->pewawancara->name ?? '-' }}</td>
                        <td><span class="badge badge-{{ $j->metode==='daring'?'info':'secondary' }}">{{ $j->metode }}</span></td>
                        <td style="word-break:break-all">{{ $j->lokasi_atau_link }}</td>
                        <td>{{ $j->sesi ?? '-' }}</td>
                        <td>
                            @if($j->penilaian)
                                <span class="badge badge-success mr-1">{{ $j->penilaian->nilai_akhir }}</span>
                            @else
                                <span class="badge badge-secondary mr-1">Belum dinilai</span>
                            @endif
                            @if(auth()->user()->role === 'superadmin' || $j->pewawancara_id === auth()->id())
                                <a href="{{ route('pewawancara.jadwal.show', $j) }}" class="btn btn-xs btn-outline-info mr-1">Detail &amp; CV</a>
                                <a href="{{ route('wawancara.edit', $j) }}" class="btn btn-xs btn-outline-primary">Input Nilai</a>
                            @endif
                        </td>
                        @if($canManage)<td><form method="POST" action="{{ route('jadwal.destroy',$j) }}" onsubmit="return confirm('Hapus jadwal?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Hapus</button></form></td>@endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $canManage?8:7 }}" class="text-center text-muted p-3">Belum ada jadwal.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">{{ $jadwal->links() }}</div>
</div>
@endsection
