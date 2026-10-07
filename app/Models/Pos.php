<?php
// app/Models/Pos.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pos extends Model
{
    use HasFactory;

    protected $table = 'pos';

    protected $fillable = [
        'invoice_number',
        'tanggal',
        'total_amount',
        'paid_amount',
        'change_amount',
        'user_id'
    ];

    public function details()
    {
        return $this->hasMany(DetailPos::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}