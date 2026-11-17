@extends('layouts.app')

@section('content')
<div class="row justify-content-center"><div class="col-lg-9">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Undangan / Pemanggilan Seleksi</h3>
        <a href="{{ route('peserta.dashboard') }}" class="btn btn-default btn-sm">Dashboard</a>
    </div>

    @if(!$kegiatan)
        <div class="card"><div class="card-body text-muted">Belum ada kegiatan aktif.</div></div>
    @elseif(!$pesertaSeleksi)
        <div class="card"><div class="card-body text-muted">Anda tidak terdaftar sebagai peserta terpilih pada kegiatan aktif ini.</div></div>
    @else
        <div class="card card-outline card-primary mb-3">
            <div class="card-header text-center">
                <div class="text-muted small">Badan Pusat Statistik Kabupaten Jember</div>
                <h4 class="mb-0">Surat Pemanggilan / Undangan Seleksi</h4>
                <div>{{ $kegiatan->nama_kegiatan }} ({{ $kegiatan->tahun }})</div>
            </div>
            <div class="card-body">
                <p>Yth. <strong>{{ auth()->user()->name }}</strong> (SOBAT ID: {{ auth()->user()->sobat_id ?? '-' }}),</p>
                <p>Berdasarkan hasil seleksi administrasi, Anda <strong>terpilih dan diundang</strong> untuk mengikuti tahapan seleksi <strong>{{ $kegiatan->nama_kegiatan }}</strong>.</p>
                <dl class="row">
                    <dt class="col-sm-4">Periode Kegiatan</dt><dd class="col-sm-8">{{ optional($kegiatan->tanggal_mulai)->format('d M Y') ?? '-' }} s/d {{ optional($kegiatan->tanggal_selesai)->format('d M Y') ?? '-' }}</dd>
                    <dt class="col-sm-4">Status Undangan Saat Ini</dt><dd class="col-sm-8"><span class="badge badge-info">{{ $pesertaSeleksi->status_undangan }}</span></dd>
                    @if($pesertaSeleksi->waktu_konfirmasi)
                    <dt class="col-sm-4">Waktu Konfirmasi</dt><dd class="col-sm-8">{{ $pesertaSeleksi->waktu_konfirmasi->format('d M Y H:i') }}</dd>
                    @endif
                </dl>
                <p class="text-muted small mb-0">Mohon melakukan konfirmasi kehadiran (RSVP) di bawah ini dan menyiapkan dokumen yang diperlukan.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Konfirmasi Kehadiran (RSVP)</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('peserta.undangan.rsvp') }}">
                    @csrf
                    <div class="form-group">
                        <div class="custom-control custom-radio mb-2">
                            <input class="custom-control-input" type="radio" id="hadir" name="status_undangan" value="hadir" {{ $pesertaSeleksi->status_undangan === 'hadir' ? 'checked' : '' }} required>
                            <label for="hadir" class="custom-control-label font-weight-normal">Saya <strong>bersedia hadir</strong> mengikuti seleksi.</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="tidak_hadir" name="status_undangan" value="tidak_hadir" {{ $pesertaSeleksi->status_undangan === 'tidak_hadir' ? 'checked' : '' }} required>
                            <label for="tidak_hadir" class="custom-control-label font-weight-normal">Saya <strong>tidak dapat hadir</strong> (berhalangan).</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check mr-1"></i>Simpan Konfirmasi</button>
                </form>
            </div>
        </div>
    @endif
</div></div>
@endsection
