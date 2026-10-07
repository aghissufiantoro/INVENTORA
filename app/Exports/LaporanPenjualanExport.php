<?php

namespace App\Exports;

use App\Models\Pos;
use Maatwebsite\Excel\Concerns\FromCollection;

class LaporanPenjualanExport implements FromCollection
{
    public function collection()
    {
        return Pos::all();
    }
}
