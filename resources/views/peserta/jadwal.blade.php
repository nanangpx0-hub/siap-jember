@extends('layouts.app')

@section('content')
<div class="row justify-content-center"><div class="col-lg-10">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Jadwal Wawancara Saya</h3>
        <a href="{{ route('peserta.dashboard') }}" class="btn btn-default btn-sm">Dashboard</a>
    </div>

    @if(!$kegiatan)
        <div class="card"><div class="card-body text-muted">Belum ada kegiatan aktif.</div></div>
    @elseif(!$pesertaSeleksi)
        <div class="card"><div class="card-body text-muted">Anda tidak terdaftar sebagai peserta terpilih pada kegiatan aktif ini.</div></div>
    @else
        <div class="card">
            <div class="card-header"><h3 class="card-title">Kegiatan: <strong>{{ $kegiatan->nama_kegiatan }}</strong> ({{ $kegiatan->tahun }})</h3></div>
            <div class="card-body p-0">
                @if($jadwal->isEmpty())
                    <p class="text-muted p-3 mb-0">Belum ada jadwal wawancara untuk Anda.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead><tr><th>Tanggal</th><th>Jam</th><th>Sesi</th><th>Metode</th><th>Lokasi / Link</th><th>Pewawancara</th><th>Catatan</th></tr></thead>
                            <tbody>
                            @foreach($jadwal as $j)
                                <tr>
                                    <td>{{ optional($j->tanggal)->format('d M Y') }}</td>
                                    <td>{{ substr($j->jam_mulai,0,5) }} - {{ substr($j->jam_selesai,0,5) }}</td>
                                    <td>{{ $j->sesi ?? '-' }}</td>
                                    <td><span class="badge badge-{{ $j->metode === 'daring' ? 'info' : 'secondary' }}">{{ strtoupper($j->metode) }}</span></td>
                                    <td style="word-break:break-all">{{ $j->lokasi_atau_link }}</td>
                                    <td>{{ $j->pewawancara->name ?? '-' }}</td>
                                    <td>{{ $j->catatan_jadwal ?? '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div></div>
@endsection
