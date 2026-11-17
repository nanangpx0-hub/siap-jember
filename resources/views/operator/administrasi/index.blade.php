@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Berkas Penyelenggaraan (/administrasi - Operator)</h3>
    <a href="{{ route('operator.dashboard') }}" class="btn btn-default btn-sm">Dashboard</a>
</div>

<div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title">Upload Berkas (disimpan privat)</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('operator.administrasi.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Kegiatan</label>
                    <select name="kegiatan_id" required class="form-control">
                        @foreach($kegiatanList as $k)<option value="{{ $k->id }}" @selected(($kegiatanId ?? 0)==$k->id)>{{ $k->nama_kegiatan }} ({{ $k->tahun }})</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label>Kategori</label>
                    <select name="kategori" required class="form-control">
                        @foreach($kategori as $kat)<option value="{{ $kat }}">{{ $kat }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-12"><label>Judul</label><input name="judul" required class="form-control"></div>
                <div class="form-group col-12"><label>File</label><input type="file" name="file" required class="form-control-file"></div>
            </div>
            <button class="btn btn-primary"><i class="fas fa-upload mr-1"></i>Upload</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Dokumen</h3>
        <div class="card-tools">
            <form method="GET" action="{{ route('operator.administrasi') }}" class="form-inline">
                <select name="kegiatan_id" class="form-control form-control-sm mr-2"><option value="">Semua</option>@foreach($kegiatanList as $k)<option value="{{ $k->id }}" @selected(($kegiatanId ?? '')==$k->id)>{{ $k->nama_kegiatan }}</option>@endforeach</select>
                <button class="btn btn-sm btn-default">Tampilkan</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead><tr><th>Kegiatan</th><th>Kategori</th><th>Judul</th><th>File</th><th>Uploader</th><th></th></tr></thead>
                <tbody>
                @forelse($dokumen as $d)
                    <tr>
                        <td>{{ $d->kegiatan->nama_kegiatan ?? '-' }}</td>
                        <td><span class="badge badge-light">{{ $d->kategori }}</span></td>
                        <td>{{ $d->judul }}</td>
                        <td>{{ $d->original_filename }} <span class="text-muted">({{ number_format($d->file_size/1024,1) }} KB)</span></td>
                        <td>{{ $d->uploader->name ?? '-' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('operator.administrasi.download', $d) }}" class="btn btn-xs btn-info">Unduh</a>
                            <form method="POST" action="{{ route('operator.administrasi.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Hapus dokumen?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Hapus</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted p-3">Belum ada dokumen.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">{{ $dokumen->links() }}</div>
</div>
@endsection
