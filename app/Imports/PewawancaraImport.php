<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PewawancaraImport implements ToCollection, WithHeadingRow
{
    public array $summary = [
        'success' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    /** @var array<string, int> */
    protected array $seenEmails = [];

    public function collection(Collection $rows): void
    {
        $rowNum = 1;
        foreach ($rows as $row) {
            $rowNum++;
            $r = $row instanceof Collection ? $row->toArray() : (array) $row;
            $get = function (array $keys) use ($r) {
                foreach ($keys as $k) {
                    if (array_key_exists($k, $r) && $r[$k] !== null && $r[$k] !== '') {
                        $value = is_string($r[$k]) ? trim($r[$k]) : (string) $r[$k];
                        if ($value !== '') {
                            return $value;
                        }
                    }
                }
                return '';
            };

            $name = $get(['nama', 'name', 'nama_lengkap']);
            $email = strtolower($get(['email', 'alamat_email']));
            $noTelp = $get(['no_telp', 'no_hp', 'telepon']);
            $password = $get(['password', 'password_awal']);

            if ($name === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: Nama kosong atau Email kosong/tidak valid.";
                continue;
            }

            if (isset($this->seenEmails[$email])) {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: Email {$email} duplikat dalam file (sudah di baris {$this->seenEmails[$email]}).";
                continue;
            }
            $this->seenEmails[$email] = $rowNum;

            $existing = User::where('email', $email)->first();
            if ($existing && $existing->role !== 'pewawancara') {
                $this->summary['skipped']++;
                $this->summary['errors'][] = "Baris {$rowNum}: Email {$email} sudah dipakai role {$existing->role}.";
                continue;
            }

            $isNew = ! $existing;
            $data = [
                'name' => $name,
                'role' => 'pewawancara',
                'no_telp' => $noTelp !== '' ? $noTelp : null,
                'is_active' => true,
            ];

            if ($isNew) {
                // Password awal = kolom Password, atau email bila kosong; wajib diganti saat login pertama.
                $data['password'] = Hash::make($password !== '' ? $password : $email);
                $data['must_change_password'] = true;
            }

            User::updateOrCreate(['email' => $email], $data);

            if ($isNew) {
                $this->summary['success']++;
            } else {
                $this->summary['updated']++;
            }
        }
    }
}
