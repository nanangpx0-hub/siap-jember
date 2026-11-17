<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HasilSeleksiTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['sobat_id','email','status_hasil','catatan_hasil'];
    }

    public function array(): array
    {
        return [['','peserta@example.com','lulus','Contoh catatan']];
    }
}
