<?php

namespace App\Observers;

use App\Models\Retur;

class ReturObserver
{
    public function updated(Retur $retur)
    {
        // Stock movement dicatat oleh ReturController saat approve.
        // Observer tidak boleh mencatat movement lagi untuk menghindari double recording.
    }
}
