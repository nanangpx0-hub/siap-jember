<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penyederhanaan RBAC: 4 role lama -> 3 role (superadmin, admin, peserta).
     *
     * admin (superuser lama)       -> superadmin
     * operator (pengelola teknis)  -> admin
     * pewawancara (tim penilai)    -> admin (identitas pewawancara tetap tersimpan
     *                                di jadwal_wawancara.pewawancara_id dan
     *                                tugas_panitia.peran_tugas)
     * peserta                      -> peserta (tidak diubah, data 3.4xx akun aman)
     */
    public function up(): void
    {
        // Widen dulu agar nilai lama tidak terbuang saat enum diubah.
        $this->setRoles(['superadmin', 'admin', 'operator', 'pewawancara', 'peserta']);

        DB::table('users')->where('role', 'admin')->update(['role' => 'superadmin']);
        DB::table('users')->whereIn('role', ['operator', 'pewawancara'])->update(['role' => 'admin']);

        $this->setRoles(['superadmin', 'admin', 'peserta']);
    }

    public function down(): void
    {
        $this->setRoles(['superadmin', 'admin', 'operator', 'pewawancara', 'peserta']);

        $mantanSuperadmin = DB::table('users')->where('role', 'superadmin')->pluck('id')->all();

        DB::table('users')->where('role', 'superadmin')->update(['role' => 'admin']);

        // Pewawancara lama dikenali dari jadwal yang masih ditugaskan kepadanya.
        $pewawancaraIds = DB::table('jadwal_wawancara')->distinct()->pluck('pewawancara_id')->all();
        DB::table('users')->where('role', 'admin')->whereIn('id', $pewawancaraIds)->update(['role' => 'pewawancara']);
        DB::table('users')->where('role', 'admin')->whereNotIn('id', $mantanSuperadmin)->update(['role' => 'operator']);

        $this->setRoles(['admin', 'operator', 'pewawancara', 'peserta']);
    }

    /** @param array<int, string> $roles */
    protected function setRoles(array $roles): void
    {
        Schema::table('users', function (Blueprint $table) use ($roles) {
            $table->enum('role', $roles)->change();
        });
    }
};
