<?php

namespace Tests\Feature;

use App\Imports\PewawancaraImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PewawancaraImportTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role, string $email, array $extra = []): User
    {
        return User::create(array_merge([
            'name' => $email,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ], $extra));
    }

    public function test_import_membuat_akun_pewawancara_baru(): void
    {
        $import = new PewawancaraImport();
        $import->collection(collect([
            ['nama' => 'Budi Santoso', 'email' => 'budi@example.com', 'no_telp' => '0811111111', 'password' => 'rahasia123'],
            ['nama' => 'Siti Aminah', 'email' => 'SITI@EXAMPLE.COM'],
        ]));

        $this->assertSame(2, $import->summary['success']);
        $this->assertSame(0, $import->summary['skipped']);

        $budi = User::where('email', 'budi@example.com')->firstOrFail();
        $this->assertSame('pewawancara', $budi->role);
        $this->assertSame('Budi Santoso', $budi->name);
        $this->assertTrue(Hash::check('rahasia123', $budi->password));
        $this->assertTrue((bool) $budi->must_change_password);

        $siti = User::where('email', 'siti@example.com')->firstOrFail();
        $this->assertSame('pewawancara', $siti->role);
        $this->assertTrue(Hash::check('siti@example.com', $siti->password));
    }

    public function test_import_memperbarui_pewawancara_dan_melewati_role_lain_serta_duplikat(): void
    {
        $existing = $this->user('pewawancara', 'lama@example.com', ['password' => Hash::make('password-lama')]);
        $operator = $this->user('operator', 'operator@example.com');

        $import = new PewawancaraImport();
        $import->collection(collect([
            ['nama' => 'Nama Baru', 'email' => 'lama@example.com', 'no_telp' => '0812222222'],
            ['nama' => 'Operator', 'email' => 'operator@example.com'],
            ['nama' => 'Duplikat', 'email' => 'DUplikat@example.com'],
            ['nama' => 'Duplikat 2', 'email' => 'duplikat@example.com'],
            ['nama' => '', 'email' => 'kosong@example.com'],
            ['nama' => 'Salah', 'email' => 'bukan-email'],
        ]));

        $this->assertSame(1, $import->summary['success']);
        $this->assertSame(1, $import->summary['updated']);
        $this->assertSame(4, $import->summary['skipped']);
        $this->assertSame(1, User::where('email', 'duplikat@example.com')->count(), 'Email duplikat hanya dibuat sekali.');

        $existing->refresh();
        $this->assertSame('Nama Baru', $existing->name);
        $this->assertSame('0812222222', $existing->no_telp);
        $this->assertTrue(Hash::check('password-lama', $existing->password), 'Password lama tidak boleh tertimpa.');
        $this->assertSame('operator', $operator->role);
    }

    public function test_template_dan_import_hanya_untuk_superadmin_operator(): void
    {
        $pewawancara = $this->user('pewawancara', 'pew@example.com');
        $operator = $this->user('operator', 'op@example.com');

        $this->actingAs($pewawancara)->get('/tugas/pewawancara/template')->assertForbidden();
        $this->actingAs($pewawancara)->post('/tugas/pewawancara/import')->assertForbidden();

        $this->actingAs($operator)->get('/tugas/pewawancara/template')->assertOk();
    }
}
