<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PewawancaraTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['Nama', 'Email', 'No Telp', 'Password'];
    }

    public function array(): array
    {
        return [
            ['Nama Pewawancara Contoh', 'pewawancara@bpsjember.com', '081234567890', 'password123'],
        ];
    }
}
