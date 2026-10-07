<?php

namespace App\Observers;

use App\Models\Pos;

class PosObserver
{
    public function created(Pos $pos)
    {
        // Stock movement dicatat oleh PosController saat checkout.
        // Observer tidak boleh mencatat movement lagi untuk menghindari double recording.
    }
}
