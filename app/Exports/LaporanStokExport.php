<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;

class LaporanStokExport implements FromCollection
{
    public function collection()
    {
        return Product::all();
    }
}
