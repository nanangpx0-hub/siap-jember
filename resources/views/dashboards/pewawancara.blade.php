@extends('layouts.app')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-check mr-1"></i>Dashboard Pewawancara</h3></div>
    <div class="card-body">
        <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Berikut ringkasan alokasi tugas dan jadwal wawancara Anda.</p>
        <a href="{{ route('tugas') }}" class="btn btn-primary mr-2"><i class="fas fa-tasks mr-1"></i>Alokasi Tugas</a>
        <a href="{{ route('jadwal') }}" class="btn btn-outline-primary"><i class="fas fa-calendar-alt mr-1"></i>Jadwal Wawancara</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Alokasi Tugas Saya ({{ $tugas->count() }})</h3></div>
            <div class="card-body p-0">
                @if($tugas->isEmpty())
                    <p class="text-muted p-3 mb-0">Belum ada alokasi tugas.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>Kegiatan</th><th>Peran</th><th>Wilayah/Ruang</th><th>Keterangan</th></tr></thead>
                            <tbody>
                            @foreach($tugas as $t)
                                <tr>
                                    <td>{{ $t->kegiatan->nama_kegiatan ?? '-' }} ({{ $t->kegiatan->tahun ?? '' }})</td>
                                    <td>{{ $t->peran_tugas }}</td>
                                    <td>{{ $t->wilayah_atau_ruang ?? '-' }}</td>
                                    <td>{{ $t->keterangan ?? '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Jadwal Wawancara Terdekat</h3></div>
            <div class="card-body p-0">
                @if($jadwal->isEmpty())
                    <p class="text-muted p-3 mb-0">Belum ada jadwal wawancara.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>Waktu</th><th>Peserta</th><th>Metode</th><th>Status</th><th>Aksi</th></tr></thead>
                            <tbody>
                            @foreach($jadwal as $j)
                                <tr>
                                    <td>{{ optional($j->tanggal)->format('d M Y') }}<div class="small text-muted">{{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }}</div></td>
                                    <td>{{ $j->pesertaSeleksi->user->name ?? '-' }}</td>
                                    <td>{{ strtoupper($j->metode) }}</td>
                                    <td>
                                        @if($j->penilaian)
                                            <span class="badge badge-success">Dinilai ({{ $j->penilaian->nilai_akhir }})</span>
                                        @else
                                            <span class="badge badge-warning">Belum dinilai</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('pewawancara.jadwal.show', $j) }}" class="btn btn-xs btn-outline-primary">Detail</a>
                                        <a href="{{ route('wawancara.edit', $j) }}" class="btn btn-xs btn-primary">Nilai</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
