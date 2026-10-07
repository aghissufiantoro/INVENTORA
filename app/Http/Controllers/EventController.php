<?php
namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return view('kalender.index', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'warna' => 'nullable|string'
        ]);

        Event::create($request->all());
        return redirect()->route('kalender.index')->with('success', 'Event berhasil ditambahkan.');
    }

    public function update(Request $request, Event $kalender)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'warna' => 'nullable|string'
        ]);

        $kalender->update($request->all());
        return redirect()->route('kalender.index')->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $kalender)
    {
        $kalender->delete();
        return redirect()->route('kalender.index')->with('success', 'Event berhasil dihapus.');
    }

    public function getNotifications()
    {
        $today = Carbon::today();

        $upcoming = Event::whereDate('tanggal_mulai', '>=', $today)
                        ->whereDate('tanggal_mulai', '<=', $today->copy()->addDays(3))
                        ->orderBy('tanggal_mulai', 'asc')
                        ->get();

        $past = Event::whereDate('tanggal_selesai', '<', $today)->get();

        return response()->json([
            'upcoming' => $upcoming,
            'past' => $past
        ]);
    }
}
