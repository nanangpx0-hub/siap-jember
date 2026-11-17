@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Alokasi Pembagian Tugas (/tugas)</h3>
    <a href="{{ route('dashboard') }}" class="btn btn-default btn-sm">Dashboard</a>
</div>

@if($canManage)
<div class="card card-info card-outline">
    <div class="card-header"><h3 class="card-title">Kegiatan (Tambah / Hapus)</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('tugas.kegiatan.store') }}" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-4"><label>Nama Kegiatan</label><input name="nama_kegiatan" required class="form-control" value="{{ old('nama_kegiatan') }}"></div>
            <div class="form-group col-md-1"><label>Tahun</label><input type="number" name="tahun" required min="1900" max="2100" class="form-control" value="{{ old('tahun', date('Y') + 1) }}"></div>
            <div class="form-group col-md-2"><label>Mulai</label><input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai') }}"></div>
            <div class="form-group col-md-2"><label>Selesai</label><input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai') }}"></div>
            <div class="form-group col-md-2"><label>Deskripsi</label><input name="deskripsi" class="form-control" value="{{ old('deskripsi') }}"></div>
            <div class="form-group col-md-1"><div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input" id="kgtAktif" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}><label class="custom-control-label" for="kgtAktif">Aktif</label></div></div>
            <div class="form-group col-12 mb-0"><button class="btn btn-info">Tambah Kegiatan</button></div>
        </form>
        @if($errors->has('kegiatan'))
            <div class="alert alert-danger mt-3 mb-0">{{ $errors->first('kegiatan') }}</div>
        @endif
        <div class="table-responsive mt-3">
            <table class="table table-sm table-striped mb-0">
                <thead><tr><th>Kegiatan</th><th>Tahun</th><th>Periode</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($kegiatanList as $k)
                    <tr>
                        <td>{{ $k->nama_kegiatan }}</td>
                        <td>{{ $k->tahun }}</td>
                        <td>{{ optional($k->tanggal_mulai)->format('d M Y') ?? '-' }} s.d. {{ optional($k->tanggal_selesai)->format('d M Y') ?? '-' }}</td>
                        <td>{!! $k->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>' !!}</td>
                        <td class="text-right">
                            @if(!$k->is_active)
                                <form method="POST" action="{{ route('tugas.kegiatan.activate', $k) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-xs btn-success">Aktifkan</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('tugas.kegiatan.destroy', $k) }}" class="d-inline" onsubmit="return confirm('Hapus kegiatan ini?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Hapus</button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted p-3">Belum ada kegiatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Kelola Akun Pewawancara</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tugas.pewawancara.store') }}">
                    @csrf
                    <div class="form-group"><label>Nama</label><input name="name" required class="form-control" value="{{ old('name') }}"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" required class="form-control" value="{{ old('email') }}"></div>
                    <div class="form-group"><label>No. Telp</label><input name="no_telp" class="form-control" value="{{ old('no_telp') }}"></div>
                    <div class="form-group"><label>Password Awal</label><input type="text" name="password" required class="form-control" value="{{ old('password') }}"></div>
                    <div class="form-group"><div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="mcp" name="must_change_password" value="1" checked><label class="custom-control-label" for="mcp">Wajib ganti password saat login pertama</label></div></div>
                    <button class="btn btn-primary">Buat Akun Pewawancara</button>
                </form>

                <hr>
                <div class="font-weight-bold mb-1">Import Akun Pewawancara (Excel)</div>
                <p class="text-muted small">Kolom: <code>Nama</code>, <code>Email</code>, opsional <code>No Telp</code> &amp; <code>Password</code>. Jika Password kosong, password awal = email dan wajib diganti saat login pertama.</p>
                <a href="{{ route('tugas.pewawancara.template') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-download mr-1"></i>Unduh Template Excel</a>
                <form method="POST" action="{{ route('tugas.pewawancara.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group"><input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-control-file"></div>
                    <button class="btn btn-success">Import Akun Pewawancara</button>
                </form>
                @if(session('import_pewawancara_summary'))
                    @php($s = session('import_pewawancara_summary'))
                    <div class="alert alert-light border mt-3 mb-0">
                        <div class="font-weight-bold mb-1">Ringkasan Import Akun Pewawancara</div>
                        <ul class="mb-1 pl-3">
                            <li>Akun baru: {{ $s['success'] }}</li>
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
            <div class="card-header"><h3 class="card-title">Plotting Tugas Panitia per Kegiatan</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tugas.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Kegiatan</label>
                        <select name="kegiatan_id" required class="form-control">
                            @foreach($kegiatanList as $k)
                                <option value="{{ $k->id }}" @selected(($kegiatanId ?? 0)==$k->id)>{{ $k->nama_kegiatan }} ({{ $k->tahun }}){{ $k->is_active ? ' - aktif' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Petugas (pewawancara/panitia)</label>
                        <select name="user_id" required class="form-control">
                            <option value="">Pilih...</option>
                            @foreach($pewawancara as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->role }}, {{ $p->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Peran Tugas</label><input name="peran_tugas" required class="form-control" placeholder="Pewawancara / Moderator / Administrasi / Dokumentasi" value="{{ old('peran_tugas') }}"></div>
                    <div class="form-group"><label>Wilayah/Ruang</label><input name="wilayah_atau_ruang" class="form-control" value="{{ old('wilayah_atau_ruang') }}"></div>
                    <div class="form-group"><label>Keterangan</label><textarea name="keterangan" class="form-control">{{ old('keterangan') }}</textarea></div>
                    <button class="btn btn-success">Tambah Plotting</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Tugas</h3>
        <div class="card-tools">
            <form method="GET" action="{{ route('tugas') }}" class="form-inline">
                <select name="kegiatan_id" class="form-control form-control-sm mr-2">
                    <option value="">Semua kegiatan</option>
                    @foreach($kegiatanList as $k)
                        <option value="{{ $k->id }}" @selected(($kegiatanId ?? '')==$k->id)>{{ $k->nama_kegiatan }} ({{ $k->tahun }})</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-default">Tampilkan</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead><tr><th>Kegiatan</th><th>Petugas</th><th>Peran</th><th>Wilayah/Ruang</th><th>Keterangan</th>@if($canManage)<th></th>@endif</tr></thead>
                <tbody>
                    @forelse($tugas as $t)
                    <tr>
                        <td>{{ $t->kegiatan->nama_kegiatan ?? '-' }} ({{ $t->kegiatan->tahun ?? '' }})</td>
                        <td>{{ $t->user->name ?? '-' }} <span class="text-muted">({{ $t->user->role ?? '' }})</span></td>
                        <td>{{ $t->peran_tugas }}</td>
                        <td>{{ $t->wilayah_atau_ruang ?? '-' }}</td>
                        <td>{{ $t->keterangan ?? '-' }}</td>
                        @if($canManage)
                        <td class="text-right">
                            <form method="POST" action="{{ route('tugas.destroy', $t) }}" onsubmit="return confirm('Hapus tugas ini?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Hapus</button></form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="{{ $canManage ? 6 : 5 }}" class="text-center text-muted p-3">Belum ada data tugas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">{{ $tugas->links() }}</div>
</div>
@endsection
