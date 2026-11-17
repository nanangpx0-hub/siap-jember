@extends('layouts.app')

@section('content')
@php($backUrl = auth()->user()->role === 'admin' ? route('jadwal') : route('pewawancara.jadwal.show', $jadwal))
<div x-data="penilaianForm()">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">Input Nilai Wawancara</h3>
            <div class="text-muted">Peserta: <strong>{{ $jadwal->pesertaSeleksi->user->name ?? '-' }}</strong> ({{ $jadwal->pesertaSeleksi->user->sobat_id ?? '' }}) — {{ optional($jadwal->tanggal)->format('d M Y') }} {{ substr($jadwal->jam_mulai,0,5) }}-{{ substr($jadwal->jam_selesai,0,5) }}</div>
        </div>
        <a href="{{ $backUrl }}" class="btn btn-default">{{ auth()->user()->role === 'admin' ? 'Kembali ke jadwal' : 'Kembali ke detail' }}</a>
    </div>

    <div class="card card-success card-outline">
        <div class="card-body">
            <form method="POST" action="{{ route('wawancara.update', $jadwal) }}">
                @csrf
                <h5 class="mb-3">Nilai Multi-Aspek (0–100)</h5>
                @foreach($kriteria as $k)
                    @php($val = old('skor.'.$k->id, optional($existing->get($k->id))->skor))
                    @php($qList = $pertanyaan->get($k->id, collect()))
                    <div class="form-group border-bottom pb-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <label class="font-weight-bold mb-0">{{ $k->nama_aspek }}</label>
                                <div class="text-muted small">Bobot: {{ $k->bobot_persen }}%</div>
                                @if($k->deskripsi)
                                    <details class="mt-1">
                                        <summary class="text-info small" style="cursor:pointer"><i class="fas fa-question-circle mr-1"></i>Rubrik penilaian aspek</summary>
                                        <div class="border rounded bg-light p-2 mt-1 small">{!! nl2br(e($k->deskripsi)) !!}</div>
                                    </details>
                                @endif
                            </div>
                            <span class="badge badge-secondary">Skor</span>
                        </div>

                        @if($qList->isNotEmpty())
                            @foreach($qList as $p)
                                @php($jwb = optional($existingJawaban->get($p->id)))
                                @php($prevPil = old('pilihan.'.$p->id, $jwb->pilihan))
                                @php($initSkor = collect($p->pilihan ?? [])->firstWhere('teks', $prevPil)['skor'] ?? (old('skor_q.'.$p->id, $jwb->skor ?? 0)))
                                <div class="border rounded p-2 mb-2">
                                    <div class="font-weight-bold mb-1 small">{{ $loop->iteration }}. {{ $p->teks }}
                                        <span class="badge badge-light border">bobot {{ $p->bobot }}</span>
                                        <span class="badge badge-light border">{{ $p->jenis }}</span>
                                    </div>
                                    @if($p->jenis === 'naratif')
                                        <textarea name="jawaban[{{ $p->id }}]" rows="2" class="form-control form-control-sm mb-1" placeholder="Ringkasan jawaban peserta (opsional)...">{{ old('jawaban.'.$p->id, $jwb->jawaban) }}</textarea>
                                        <div class="d-flex align-items-center">
                                            <label class="mr-2 mb-0 small text-muted">Skor jawaban 0–100:</label>
                                            <input type="number" name="skor_q[{{ $p->id }}]" min="0" max="100" step="1" required class="form-control form-control-sm" style="max-width:120px"
                                                   x-model.number="qScores[{{ $p->id }}]" @input="recalc()"
                                                   value="{{ (int) $initSkor }}" placeholder="0 - 100">
                                        </div>
                                    @else
                                        @foreach($p->pilihan ?? [] as $opsi)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pilihan[{{ $p->id }}]"
                                                       id="pilihan_{{ $p->id }}_{{ $loop->iteration }}"
                                                       value="{{ $opsi['teks'] }}"
                                                       @checked($prevPil === $opsi['teks'])
                                                       @change="qScores[{{ $p->id }}] = {{ (int) $opsi['skor'] }}; recalc()">
                                                <label class="form-check-label small" for="pilihan_{{ $p->id }}_{{ $loop->iteration }}">{{ $opsi['teks'] }} <span class="text-muted">({{ $opsi['skor'] }})</span></label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                            <div class="small text-muted">Skor aspek (otomatis, rata-rata tertimbang bobot pertanyaan): <strong x-text="aspectScore({{ $k->id }})"></strong> / 100</div>
                        @else
                            <input type="number" name="skor[{{ $k->id }}]" min="0" max="100" step="1" required
                                   class="form-control" style="max-width:240px"
                                   x-model.number="scores[{{ $k->id }}]" @input="recalc()"
                                   value="{{ $val !== null ? $val : '' }}" placeholder="0 - 100">
                        @endif
                    </div>
                @endforeach

                <div class="callout callout-info">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="mr-4 mb-2 mb-md-0">Nilai Akhir (bobot, preview): <strong class="h4 mb-0" x-text="finalScore.toFixed(2)">{{ $penilaian->nilai_akhir ?? '0.00' }}</strong></div>
                        <div class="text-muted small">Skor aspek &amp; nilai akhir final dihitung ulang oleh server saat submit.</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Rekomendasi</label>
                        @php($rek = old('rekomendasi', $penilaian->rekomendasi ?? 'dipertimbangkan'))
                        <select name="rekomendasi" required class="form-control">
                            <option value="sangat_direkomendasikan" @selected($rek==='sangat_direkomendasikan')>Sangat Direkomendasikan</option>
                            <option value="direkomendasikan" @selected($rek==='direkomendasikan')>Direkomendasikan</option>
                            <option value="dipertimbangkan" @selected($rek==='dipertimbangkan')>Dipertimbangkan</option>
                            <option value="tidak_direkomendasikan" @selected($rek==='tidak_direkomendasikan')>Tidak Direkomendasikan</option>
                        </select>
                    </div>
                    <div class="form-group col-md-7">
                        <label>Catatan Pewawancara</label>
                        <textarea name="catatan" rows="3" class="form-control" placeholder="Catatan hasil wawancara (mis. catatan administratif khusus wanita)...">{{ old('catatan', $penilaian->catatan ?? '') }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i>Simpan Nilai & Penilaian</button>
                <a href="{{ $backUrl }}" class="btn btn-default ml-1">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function penilaianForm() {
    return {
        scores: {
            @foreach($kriteria as $k)
                {{ $k->id }}: {{ (int) old('skor.'.$k->id, optional($existing->get($k->id))->skor ?? 0) }},
            @endforeach
        },
        weights: {
            @foreach($kriteria as $k)
                {{ $k->id }}: {{ (float) $k->bobot_persen }},
            @endforeach
        },
        hasQuestions: {
            @foreach($kriteria as $k)
                {{ $k->id }}: {{ $pertanyaan->get($k->id, collect())->isNotEmpty() ? 'true' : 'false' }},
            @endforeach
        },
        qScores: {
            @foreach($pertanyaan->flatten() as $p)
                @php($jwb = optional($existingJawaban->get($p->id)))
                @php($prevPil = old('pilihan.'.$p->id, $jwb->pilihan))
                @php($initSkor = $p->jenis === 'naratif'
                    ? (int) old('skor_q.'.$p->id, $jwb->skor ?? 0)
                    : (collect($p->pilihan ?? [])->firstWhere('teks', $prevPil)['skor'] ?? (int) ($jwb->skor ?? 0)))
                {{ $p->id }}: {{ $initSkor }},
            @endforeach
        },
        qWeights: {
            @foreach($pertanyaan->flatten() as $p)
                {{ $p->id }}: {{ (float) $p->bobot }},
            @endforeach
        },
        qKriteria: {
            @foreach($pertanyaan->flatten() as $p)
                {{ $p->id }}: {{ $p->kriteria_penilaian_id }},
            @endforeach
        },
        aspectScores: {},
        finalScore: 0,
        init() { this.recalc(); },
        recalc() {
            let total = 0;
            for (const id in this.weights) {
                let s = 0;
                if (this.hasQuestions[id]) {
                    let num = 0, den = 0;
                    for (const pid in this.qWeights) {
                        if (String(this.qKriteria[pid]) !== String(id)) continue;
                        const w = parseFloat(this.qWeights[pid] || 1);
                        den += w;
                        num += (parseFloat(this.qScores[pid] || 0)) * w;
                    }
                    s = den > 0 ? num / den : 0;
                    this.aspectScores[id] = Math.round(s * 100) / 100;
                } else {
                    s = parseFloat(this.scores[id] || 0);
                }
                total += s * (parseFloat(this.weights[id] || 0) / 100);
            }
            this.finalScore = Math.round(total * 100) / 100;
        },
        aspectScore(id) { return this.aspectScores[id] ?? 0; }
    }
}
</script>
@endpush
