@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4 text-secondary">
        <i class="bi bi-calendar-event"></i> Kalender Event
    </h4>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-3 shadow-sm bg-light rounded-3">
                <div id='calendar'></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-4 shadow-sm bg-light rounded-3">
                <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle"></i> Tambah Event</h5>
                <form action="{{ route('kalender.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Judul Event</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <input type="color" name="warna" class="form-control form-control-color" value="#0d6efd">
                    </div>
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-save"></i> Simpan Event
                    </button>
                </form>
            </div>

            <div class="card mt-4 p-3 shadow-sm bg-light rounded-3">
                <h6 class="fw-bold mb-2"><i class="bi bi-list-task"></i> Daftar Event</h6>
                <ul class="list-group">
                    @forelse ($events as $event)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <span class="badge me-2" style="background: {{ $event->warna }};">&nbsp;</span>
                                {{ $event->judul }}
                                <small class="text-muted d-block">{{ $event->tanggal_mulai }}</small>
                            </span>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-secondary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal{{ $event->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('kalender.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Hapus event ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </li>

                        {{-- Modal Edit --}}
                        <div class="modal fade" id="editModal{{ $event->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('kalender.update', $event->id) }}" method="POST" class="modal-content">
                                    @csrf @method('PUT')
                                    <div class="modal-header bg-secondary text-white">
                                        <h5 class="modal-title">Edit Event</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Judul Event</label>
                                            <input type="text" name="judul" value="{{ $event->judul }}" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Deskripsi</label>
                                            <textarea name="deskripsi" class="form-control" rows="3">{{ $event->deskripsi }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Mulai</label>
                                            <input type="date" name="tanggal_mulai" value="{{ $event->tanggal_mulai }}" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Selesai</label>
                                            <input type="date" name="tanggal_selesai" value="{{ $event->tanggal_selesai }}" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Warna</label>
                                            <input type="color" name="warna" class="form-control form-control-color" value="{{ $event->warna }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-secondary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <li class="list-group-item text-muted text-center">Belum ada event</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- FullCalendar --}}
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 550,
            locale: 'id',
            events: [
                @foreach ($events as $event)
                {
                    title: '{{ $event->judul }}',
                    start: '{{ $event->tanggal_mulai }}',
                    end: '{{ $event->tanggal_selesai ?? $event->tanggal_mulai }}',
                    color: '{{ $event->warna }}',
                },
                @endforeach
            ],
        });
        calendar.render();
    });
</script>

<style>
    body { background-color: #f1f1f1; }
    .card { border: none; }
    @media (max-width: 992px) {
        .col-lg-8, .col-lg-4 {
            flex: 100%;
            max-width: 100%;
        }
        #calendar {
            margin-bottom: 20px;
        }
    }
</style>
@endsection
