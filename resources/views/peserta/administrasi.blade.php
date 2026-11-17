@extends('layouts.app')

@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Administrasi Peserta — Upload CV</h3>
        <a href="{{ route('peserta.dashboard') }}" class="btn btn-default btn-sm">Dashboard</a>
    </div>

    @if(!$kegiatan)
        <div class="card"><div class="card-body text-muted">Belum ada kegiatan aktif.</div></div>
    @elseif(!$pesertaSeleksi)
        <div class="card"><div class="card-body text-muted">Anda tidak terdaftar sebagai peserta terpilih pada kegiatan aktif ini.</div></div>
    @else
        <div class="card">
            <div class="card-body">
                <p class="text-muted">Unggah CV dalam format <strong>PDF</strong> (maks. 5 MB). File disimpan secara privat dan hanya dapat diakses oleh Anda.</p>
                <form method="POST" action="{{ route('peserta.administrasi.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="cv">File CV (PDF)</label>
                        <div class="custom-file">
                            <input type="file" name="cv" id="cv" accept="application/pdf,.pdf" required class="custom-file-input">
                            <label class="custom-file-label" for="cv">Pilih file...</label>
                        </div>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-upload mr-1"></i>Upload CV</button>
                </form>
                <hr>
                <h5>CV Tersimpan</h5>
                @if($pesertaSeleksi->cv_path)
                    <p class="mb-1">Status: <span class="badge badge-success">Sudah diunggah</span></p>
                    <p>Waktu unggah: {{ optional($pesertaSeleksi->cv_uploaded_at)->format('d M Y H:i') ?? '-' }}</p>
                    <a href="{{ route('peserta.administrasi.preview') }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye mr-1"></i>Preview CV</a>
                    <a href="{{ route('peserta.administrasi.download') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-download mr-1"></i>Unduh CV</a>
                @else
                    <p class="text-muted mb-0">Belum ada CV yang diunggah.</p>
                @endif
            </div>
        </div>
    @endif
</div></div>
@endsection

@push('scripts')
<script>
document.addEventListener('change', function(e){
  if(e.target && e.target.classList.contains('custom-file-input')){
    var name = e.target.files[0] ? e.target.files[0].name : 'Pilih file...';
    var label = e.target.nextElementSibling; if(label) label.textContent = name;
  }
});
</script>
@endpush
