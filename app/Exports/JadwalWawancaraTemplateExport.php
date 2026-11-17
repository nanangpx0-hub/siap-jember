<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JadwalWawancaraTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['sobat_id_peserta','email_peserta','email_pewawancara','tanggal','jam_mulai','jam_selesai','sesi','metode','lokasi_atau_link','catatan_jadwal'];
    }

    public function array(): array
    {
        return [['','peserta@example.com','pewawancara@example.com','2027-01-15','08:00','08:30','1','luring','Kantor BPS Jember','Contoh jadwal']];
    }
}
