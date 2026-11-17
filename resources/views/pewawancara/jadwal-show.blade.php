@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h3 class="mb-1">Detail Peserta Wawancara</h3>
        <div class="text-muted">Jadwal: {{ optional($jadwal->tanggal)->format('d M Y') }} {{ substr($jadwal->jam_mulai,0,5) }}-{{ substr($jadwal->jam_selesai,0,5) }} ({{ strtoupper($jadwal->metode) }}) — {{ $jadwal->lokasi_atau_link }}</div>
    </div>
    <div>
        <a href="{{ route('wawancara.edit', $jadwal) }}" class="btn btn-success"><i class="fas fa-edit mr-1"></i>Input/Edit Nilai</a>
        <a href="{{ route('pewawancara.dashboard') }}" class="btn btn-default">Dashboard</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title">Ringkasan Peserta</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Nama</dt><dd class="col-sm-7 font-weight-bold">{{ $peserta->user->name ?? '-' }}</dd>
                    <dt class="col-sm-5 text-muted">SOBAT ID</dt><dd class="col-sm-7">{{ $peserta->user->sobat_id ?? '-' }}</dd>
                    <dt class="col-sm-5 text-muted">Email</dt><dd class="col-sm-7">{{ $peserta->user->email ?? '-' }}</dd>
                    <dt class="col-sm-5 text-muted">No. Telp</dt><dd class="col-sm-7">{{ $peserta->user->no_telp ?? '-' }}</dd>
                    <dt class="col-sm-5 text-muted">Status Undangan</dt><dd class="col-sm-7">{{ $peserta->status_undangan ?? '-' }}</dd>
                </dl>
                <hr>
                <h6>CV Peserta</h6>
                @if($peserta->cv_path)
                    <a href="{{ route('pewawancara.jadwal.cv.preview', $jadwal) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> Preview</a>
                    <a href="{{ route('pewawancara.jadwal.cv.download', $jadwal) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download"></i> Unduh</a>
                @else
                    <p class="text-muted mb-0">Peserta belum mengunggah CV.</p>
                @endif
                @if($jadwal->penilaian)
                <hr>
                <h6>Penilaian Tersimpan</h6>
                <div>Nilai akhir: <strong>{{ $jadwal->penilaian->nilai_akhir }}</strong></div>
                <div>Rekomendasi: <span class="badge badge-info">{{ $jadwal->penilaian->rekomendasi }}</span></div>
                <div class="text-muted small">Submit: {{ optional($jadwal->penilaian->submitted_at)->format('d M Y H:i') }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Profil Mitra (mitra_profiles)</h3></div>
            <div class="card-body">
                @if($profile)
                    @php
                        $fields = [
                            'Posisi' => $profile->posisi,
                            'Status Seleksi 2026' => $profile->status_seleksi_2026,
                            'Posisi Daftar' => $profile->posisi_daftar,
                            'TTL' => $profile->ttl,
                            'Umur' => $profile->umur,
                            'Jenis Kelamin' => $profile->jenis_kelamin,
                            'Status Kawin' => $profile->status_kawin,
                            'Pendidikan' => $profile->pendidikan,
                            'Pekerjaan' => $profile->pekerjaan,
                            'Provinsi' => $profile->alamat_provinsi,
                            'Kab/Kota' => $profile->alamat_kab_kota,
                            'Kecamatan' => $profile->alamat_kecamatan,
                            'Desa/Kel' => $profile->alamat_desa_kel,
                            'Alamat Detail' => $profile->alamat_detail,
                            'Terikat Kontrak KLDI' => $profile->terikat_kontrak_kldi,
                            'Nama KLDI' => $profile->nama_kldi,
                            'Punya Motor' => $profile->punya_motor,
                            'Bisa Motor' => $profile->bisa_motor,
                            'Berpengalaman CAPI' => $profile->berpengalaman_capi,
                            'Punya Android' => $profile->punya_android,
                            'Merk HP' => $profile->merk_hp,
                            'Tipe HP' => $profile->tipe_hp,
                            'RAM HP' => $profile->ram_hp,
                            'Bisa Komputer' => $profile->bisa_komputer,
                            'Punya Laptop' => $profile->punya_laptop,
                            'Pernah Pendataan BPS' => $profile->pernah_pendataan_bps,
                            'Pernah SP' => $profile->pernah_sp,
                            'Pernah ST' => $profile->pernah_st,
                            'Pernah SE' => $profile->pernah_se,
                            'Pernah Susenas' => $profile->pernah_susenas,
                            'Pernah Sakernas' => $profile->pernah_sakernas,
                            'Pernah SBH' => $profile->pernah_sbh,
                            'Status NIK' => $profile->status_nik,
                        ];
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <tbody>
                            @foreach($fields as $label => $value)
                                <tr><th class="text-muted" style="width:40%">{{ $label }}</th><td>{{ $value ?? '-' }}</td></tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Profil mitra belum tersedia.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
