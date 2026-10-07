<?php

namespace App\Observers;

use App\Models\BarangMasuk;

class BarangMasukObserver
{
    public function updated(BarangMasuk $barangMasuk)
    {
        // Stock movement dicatat oleh BarangMasukController saat verify.
        // Observer tidak boleh mencatat movement lagi untuk menghindari double recording.
    }

    public function created(BarangMasuk $barangMasuk)
    {
        // Stock movement dicatat oleh BarangMasukController saat store.
        // Observer tidak boleh mencatat movement lagi untuk menghindari double recording.
    }

    public function deleted(BarangMasuk $barangMasuk)
    {
        // Stock movement dicatat oleh BarangMasukController saat destroy.
        // Observer tidak boleh mencatat movement lagi untuk menghindari double recording.
    }
}
