<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Role akhir sesuai permintaan user: superadmin, operator, pewawancara, peserta.
     *
     * Dari kondisi sebelumnya (superadmin, admin, peserta):
     * superadmin -> superadmin (tetap)
     * admin      -> operator
     * peserta    -> peserta (tetap)
     * Role `pewawancara` dipulihkan sebagai role login (akun pewawancara dapat
     * dibuat kembali lewat /tugas -> "Tambah Pewawancara").
     */
    public function up(): void
    {
        $this->setRoles(['superadmin', 'admin', 'operator', 'pewawancara', 'peserta']);

        DB::table('users')->where('role', 'admin')->update(['role' => 'operator']);

        $this->setRoles(['superadmin', 'operator', 'pewawancara', 'peserta']);
    }

    public function down(): void
    {
        $this->setRoles(['superadmin', 'admin', 'operator', 'pewawancara', 'peserta']);

        DB::table('users')->where('role', 'operator')->update(['role' => 'admin']);

        $this->setRoles(['superadmin', 'admin', 'peserta']);
    }

    /** @param array<int, string> $roles */
    protected function setRoles(array $roles): void
    {
        Schema::table('users', function (Blueprint $table) use ($roles) {
            $table->enum('role', $roles)->change();
        });
    }
};
