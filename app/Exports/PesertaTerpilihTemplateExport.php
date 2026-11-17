<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PesertaTerpilihTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['Email'];
    }

    public function array(): array
    {
        return [
            ['contoh@email.com'],
        ];
    }
}
