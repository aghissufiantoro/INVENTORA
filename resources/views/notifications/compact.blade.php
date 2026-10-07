@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Compact -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="bi bi-bell-fill"></i> Notifikasi Stok</h3>
            <p class="text-muted mb-0 small">
                <span class="badge bg-primary">{{ $unreadCount }}</span> belum dibaca
                @if($criticalCount > 0)
                    · <span class="badge bg-danger">{{ $criticalCount }}</span> kritis
                @endif
            </p>
        </div>
        <div class="btn-toolbar gap-2">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('notifications.index') }}" 
                    class="btn {{ !request('status') ? 'btn-dark' : 'btn-outline-dark' }}">Semua</a>
                <a href="{{ route('notifications.index', ['status' => 'unread']) }}" 
                    class="btn {{ request('status') == 'unread' ? 'btn-dark' : 'btn-outline-dark' }}">Unread</a>
                <a href="{{ route('notifications.index', ['status' => 'read']) }}" 
                    class="btn {{ request('status') == 'read' ? 'btn-dark' : 'btn-outline-dark' }}">Read</a>
            </div>
            
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-success"><i class="bi bi-check-all"></i></button>
                </form>
            @endif
            
            <form action="{{ route('notifications.check-stock') }}" method="POST">
                @csrf
                <button class="btn btn-sm btn-info text-white"><i class="bi bi-arrow-clockwise"></i></button>
            </form>
        </div>
    </div>

    <!-- Notifications Table Style -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40"></th>
                        <th>Produk</th>
                        <th width="150">Status</th>
                        <th width="100" class="text-center">Stok</th>
                        <th width="150">Waktu</th>
                        <th width="150" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        <tr class="{{ !$notification['is_read'] ? 'table-primary' : '' }}">
                            <td class="text-center" style="font-size: 24px;">
                                {{ $notification['icon'] }}
                            </td>
                            <td>
                                <strong>{{ $notification['product_name'] }}</strong>
                                <br>
                                <small class="text-muted">{{ Str::limit($notification['message'], 80) }}</small>
                            </td>
                            <td>
                                @php
                                    $badgeColors = [
                                        'danger' => 'danger',
                                        'critical' => 'warning',
                                        'warning' => 'info',
                                        'info' => 'secondary'
                                    ];
                                    $badgeColor = $badgeColors[$notification['severity']] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">
                                    {{ strtoupper($notification['severity']) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <strong class="{{ $notification['current_stock'] <= 0 ? 'text-danger' : '' }}">
                                    {{ $notification['current_stock'] }}
                                </strong>
                                <br>
                                <small class="text-muted">unit</small>
                            </td>
                            <td>
                                <small>{{ $notification['time_ago'] }}</small>
                                @if(!$notification['is_read'])
                                    <br><span class="badge bg-primary">Baru</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    @if(!$notification['is_read'])
                                        <form action="{{ route('notifications.mark-read', $notification['id']) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-primary" title="Tandai Dibaca">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('product.edit', $notification['product_id']) }}" 
                                        class="btn btn-sm btn-success" title="Lihat Produk">
                                        <i class="bi bi-box"></i>
                                    </a>
                                    
                                    <form action="{{ route('notifications.destroy', $notification['id']) }}" method="POST"
                                        onsubmit="return confirm('Hapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-3 mb-0">Tidak ada notifikasi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($total > $perPage)
        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">
                Menampilkan {{ count($notifications) }} dari {{ $total }} notifikasi
            </small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    @if($page > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ route('notifications.index', array_merge(request()->query(), ['page' => $page - 1])) }}">
                                ‹
                            </a>
                        </li>
                    @endif
                    
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                    
                    @if($page * $perPage < $total)
                        <li class="page-item">
                            <a class="page-link" href="{{ route('notifications.index', array_merge(request()->query(), ['page' => $page + 1])) }}">
                                ›
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    @endif
</div>
@endsection