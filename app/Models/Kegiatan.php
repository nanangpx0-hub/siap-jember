<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'tahun',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'pengumuman_dipublikasikan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_active' => 'boolean',
            'pengumuman_dipublikasikan' => 'boolean',
        ];
    }

    public function pesertaSeleksi(): HasMany
    {
        return $this->hasMany(PesertaSeleksi::class);
    }

    public function tugasPanitia(): HasMany
    {
        return $this->hasMany(TugasPanitia::class);
    }

    public function jadwalWawancara(): HasMany
    {
        return $this->hasMany(JadwalWawancara::class);
    }

    public function kriteriaPenilaian(): HasMany
    {
        return $this->hasMany(KriteriaPenilaian::class);
    }

    public function dokumenPenyelenggaraan(): HasMany
    {
        return $this->hasMany(DokumenPenyelenggaraan::class);
    }

    public function pertanyaanWawancara(): HasMany
    {
        return $this->hasMany(PertanyaanWawancara::class);
    }

    /**
     * Inisialisasi 4 kriteria penilaian default (bobot 25% tiap aspek)
     * beserta 17 pertanyaan wawancara default untuk kegiatan ini.
     * Idempoten: aman dipanggil ulang (seed database / kegiatan baru).
     */
    public function seedDefaultKriteriaDanPertanyaan(): void
    {
        $defaults = [
            [
                'nama_aspek' => 'Komunikasi',
                'bobot_persen' => 25,
                'urutan' => 1,
                'deskripsi' => "RUBRIK ASPEK (skor aspek dihitung otomatis dari jawaban per pertanyaan):\n"
                    . "80-100 : keempat indikator terpenuhi, komunikasi lancar dan persuasif\n"
                    . "60-79  : lancar namun kurang persuasif\n"
                    . "40-59  : canggung, namun masih bisa dipahami\n"
                    . "0-39   : sulit berkomunikasi / tidak ramah\n"
                    . "Untuk pertanyaan naratif, beri skor 0-100 sesuai kualitas jawaban (semakin tinggi semakin baik).",
            ],
            [
                'nama_aspek' => 'Pengalaman Lapangan',
                'bobot_persen' => 25,
                'urutan' => 2,
                'deskripsi' => "RUBRIK ASPEK (skor aspek dihitung otomatis dari jawaban per pertanyaan):\n"
                    . "80-100 : pernah menjadi petugas BPS minimal 1 kegiatan dan paham wilayah tugasnya\n"
                    . "60-79  : pernah survei non-BPS / survei lain, paham wilayah\n"
                    . "40-59  : belum pernah survei, namun paham wilayah dan siap belajar\n"
                    . "0-39   : tanpa pengalaman dan tidak paham wilayah\n"
                    . "Untuk pertanyaan naratif, beri skor 0-100 sesuai kualitas jawaban (semakin tinggi semakin baik).",
            ],
            [
                'nama_aspek' => 'Penguasaan Teknologi / CAPI',
                'bobot_persen' => 25,
                'urutan' => 3,
                'deskripsi' => "RUBRIK ASPEK (skor aspek dihitung otomatis dari jawaban per pertanyaan):\n"
                    . "80-100 : pernah memakai CAPI/FASIH dan mampu menangani kendala teknis dasar\n"
                    . "60-79  : terbiasa smartphone/aplikasi pengolahan data, belum pernah CAPI\n"
                    . "40-59  : memiliki HP layak, membutuhkan pendampingan intensif\n"
                    . "0-39   : tidak memiliki / tidak mampu menggunakan smartphone\n"
                    . "Untuk pertanyaan naratif, beri skor 0-100 sesuai kualitas jawaban (semakin tinggi semakin baik).",
            ],
            [
                'nama_aspek' => 'Komitmen & Integritas',
                'bobot_persen' => 25,
                'urutan' => 4,
                'deskripsi' => "RUBRIK ASPEK (skor aspek dihitung otomatis dari jawaban per pertanyaan):\n"
                    . "80-100 : komitmen tinggi, jawaban mendetail, siap penuh selama periode lapangan\n"
                    . "60-79  : komitmen baik, ada kesibukan lain yang tidak mengganggu\n"
                    . "40-59  : komitmen sedang, kesibukan lain cukup banyak\n"
                    . "0-39   : kesibukan lain dominan / komitmen lemah\n"
                    . "Untuk pertanyaan naratif, beri skor 0-100 sesuai kualitas jawaban (semakin tinggi semakin baik).\n"
                    . "CATATAN ADMINISTRATIF (di luar skor): khusus calon wanita, status kehamilan dicatat sebagai pertimbangan penugasan lapangan pada kolom Catatan, bukan komponen skor.",
            ],
        ];

        $kriteriaIdByAspek = [];
        foreach ($defaults as $k) {
            $kriteria = KriteriaPenilaian::updateOrCreate(
                ['kegiatan_id' => $this->id, 'nama_aspek' => $k['nama_aspek']],
                [
                    'bobot_persen' => $k['bobot_persen'],
                    'urutan' => $k['urutan'],
                    'deskripsi' => $k['deskripsi'],
                ]
            );
            $kriteriaIdByAspek[$k['nama_aspek']] = $kriteria->id;
        }

        // Bank pertanyaan wawancara per aspek. jenis: binier/kategori (skor dari pilihan) atau naratif (skor manual pewawancara).
        $questions = [
            'Komunikasi' => [
                ['teks' => '(Observasi) Apakah calon tampil rapi, sopan, dan ramah (senyum, salam, sapa)?', 'jenis' => 'binier', 'bobot' => 1, 'pilihan' => [['teks' => 'YA', 'skor' => 100], ['teks' => 'TIDAK', 'skor' => 40]]],
                ['teks' => '(Observasi) Apakah calon berbicara lancar dan mudah dipahami responden?', 'jenis' => 'binier', 'bobot' => 1, 'pilihan' => [['teks' => 'YA', 'skor' => 100], ['teks' => 'TIDAK', 'skor' => 40]]],
                ['teks' => '(Simulasi) Coba sampaikan tujuan wawancara ini seolah Anda menyapa responden di rumahnya.', 'jenis' => 'naratif', 'bobot' => 2, 'pilihan' => null],
                ['teks' => 'Bagaimana cara Anda menyampaikan pertanyaan sensitif (mis. pendapatan) agar responden mau menjawab jujur?', 'jenis' => 'naratif', 'bobot' => 1, 'pilihan' => null],
            ],
            'Pengalaman Lapangan' => [
                ['teks' => 'Ceritakan pengalaman Anda dalam kegiatan survei/sensus BPS sebelumnya — nama kegiatan, peran, dan wilayah tugas.', 'jenis' => 'naratif', 'bobot' => 2, 'pilihan' => null],
                ['teks' => 'Sebutkan karakteristik ekonomi/usaha dominan di wilayah (desa/kecamatan) Anda.', 'jenis' => 'naratif', 'bobot' => 1, 'pilihan' => null],
                ['teks' => 'Pernahkah menghadapi responden yang menolak (PR)? Bagaimana cara Anda mengatasinya?', 'jenis' => 'naratif', 'bobot' => 1, 'pilihan' => null],
                ['teks' => 'Pernahkah bekerja dengan daftar isian atau alat elektronik saat enumerasi?', 'jenis' => 'naratif', 'bobot' => 1, 'pilihan' => null],
            ],
            'Penguasaan Teknologi / CAPI' => [
                ['teks' => 'Apakah Anda memiliki smartphone Android pribadi yang layak dan aktif? (syarat FASIH/CAPI)', 'jenis' => 'binier', 'bobot' => 2, 'pilihan' => [['teks' => 'YA', 'skor' => 100], ['teks' => 'TIDAK', 'skor' => 20]]],
                ['teks' => 'Pernahkah menggunakan aplikasi FASIH/CAPI atau aplikasi enumerasi lain? Sebutkan pengalamannya.', 'jenis' => 'naratif', 'bobot' => 2, 'pilihan' => null],
                ['teks' => '(Simulasi, bila perangkat tersedia) Coba isi formulir singkat pada aplikasi latihan / login FASIH.', 'jenis' => 'naratif', 'bobot' => 1, 'pilihan' => null],
                ['teks' => 'Bagaimana langkah Anda jika aplikasi CAPI error atau data gagal terkirim di tengah pekerjaan lapangan?', 'jenis' => 'naratif', 'bobot' => 1, 'pilihan' => null],
            ],
            'Komitmen & Integritas' => [
                ['teks' => 'Apa aktivitas/pekerjaan Anda sehari-hari? Adakah kesibukan lain selama periode kerja lapangan kegiatan ini?', 'jenis' => 'kategori', 'bobot' => 2, 'pilihan' => [['teks' => 'Tidak ada kesibukan lain', 'skor' => 100], ['teks' => 'Ada, tetapi tidak mengganggu', 'skor' => 70], ['teks' => 'Sangat sibuk', 'skor' => 30]]],
                ['teks' => 'Bagaimana Anda menjamin pekerjaan selesai tepat waktu?', 'jenis' => 'kategori', 'bobot' => 2, 'pilihan' => [['teks' => 'MENDETAIL (punya target harian, lapor rutin, menata jadwal)', 'skor' => 100], ['teks' => 'CUKUP', 'skor' => 70], ['teks' => 'SINGKAT', 'skor' => 40]]],
                ['teks' => 'Bagaimana Anda memastikan data yang dikumpulkan sesuai kondisi sebenarnya? (indikator: klarifikasi jawaban, observasi ulang, mengikuti SOP)', 'jenis' => 'naratif', 'bobot' => 2, 'pilihan' => null],
                ['teks' => 'Apakah Anda bisa mengendarai motor dan memiliki SIM C aktif?', 'jenis' => 'binier', 'bobot' => 1, 'pilihan' => [['teks' => 'YA', 'skor' => 100], ['teks' => 'TIDAK', 'skor' => 20]]],
                ['teks' => 'Bersedia ditempatkan di wilayah mana saja di Kabupaten Jember?', 'jenis' => 'binier', 'bobot' => 1, 'pilihan' => [['teks' => 'YA', 'skor' => 100], ['teks' => 'TIDAK', 'skor' => 30]]],
            ],
        ];

        foreach ($questions as $aspek => $list) {
            foreach ($list as $i => $q) {
                PertanyaanWawancara::updateOrCreate(
                    [
                        'kegiatan_id' => $this->id,
                        'kriteria_penilaian_id' => $kriteriaIdByAspek[$aspek],
                        'teks' => $q['teks'],
                    ],
                    [
                        'jenis' => $q['jenis'],
                        'pilihan' => $q['pilihan'],
                        'bobot' => $q['bobot'],
                        'urutan' => $i + 1,
                    ]
                );
            }
        }
    }
}
