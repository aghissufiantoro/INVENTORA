@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-1"><i class="bi bi-bell"></i> Notifikasi Stok</h2>
                    <p class="text-muted mb-0">Pantau status stok produk secara real-time</p>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-md-end gap-2 mt-3 mt-md-0">
                        <div class="badge bg-primary fs-6 py-2 px-3">
                            <i class="bi bi-envelope"></i> {{ $unreadCount }} Belum Dibaca
                        </div>
                        @if($criticalCount > 0)
                            <div class="badge bg-danger fs-6 py-2 px-3">
                                <i class="bi bi-exclamation-triangle"></i> {{ $criticalCount }} Kritis
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12 col-md-auto">
                    <div class="btn-group" role="group">
                        <a href="{{ route('notifications.index') }}" 
                            class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="bi bi-list-ul"></i> Semua
                        </a>
                        <a href="{{ route('notifications.index', ['status' => 'unread']) }}" 
                            class="btn {{ request('status') == 'unread' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="bi bi-envelope"></i> Belum Dibaca
                        </a>
                        <a href="{{ route('notifications.index', ['status' => 'read']) }}" 
                            class="btn {{ request('status') == 'read' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="bi bi-envelope-open"></i> Sudah Dibaca
                        </a>
                    </div>
                </div>
                
                <div class="col"></div>
                
                <div class="col-12 col-md-auto">
                    <div class="btn-group" role="group">
                        <form action="{{ route('notifications.check-stock') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info text-white">
                                <i class="bi bi-arrow-clockwise"></i> Cek Stok
                            </button>
                        </form>
                        
                        @if($unreadCount > 0)
                            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-all"></i> Tandai Semua
                                </button>
                            </form>
                        @endif
                        
                        <form action="{{ route('notifications.clear-read') }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus semua notifikasi yang sudah dibaca?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-trash"></i> Hapus Dibaca
                            </button>
                        </form>
                        
                        <form action="{{ route('notifications.clear-all') }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus SEMUA notifikasi?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-circle"></i> Hapus Semua
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Notifications List -->
    @forelse($notifications as $notification)
        <div class="card shadow-sm mb-3 {{ !$notification['is_read'] ? 'border-primary border-2' : '' }}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-9">
                        <!-- Header -->
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span style="font-size: 24px;">{{ $notification['icon'] }}</span>
                            
                            @php
                                $badgeColors = [
                                    'danger' => 'danger',
                                    'critical' => 'warning',
                                    'warning' => 'info',
                                    'info' => 'secondary'
                                ];
                                $badgeColor = $badgeColors[$notification['severity']] ?? 'secondary';
                            @endphp
                            
                            <span class="badge bg-{{ $badgeColor }} text-uppercase">
                                {{ str_replace('_', ' ', $notification['type']) }}
                            </span>
                            
                            <small class="text-muted">{{ $notification['time_ago'] }}</small>
                            
                            @if(!$notification['is_read'])
                                <span class="badge bg-primary">Baru</span>
                            @endif
                        </div>

                        <!-- Title -->
                        <h5 class="card-title mb-2">{{ $notification['title'] }}</h5>

                        <!-- Message -->
                        <p class="card-text text-muted mb-3">{{ $notification['message'] }}</p>

                        <!-- Stock Info -->
                        <div class="d-flex flex-wrap gap-3">
                            <div>
                                <small class="text-muted">Stok Saat Ini:</small>
                                <strong class="{{ $notification['current_stock'] <= 0 ? 'text-danger' : 'text-dark' }}">
                                    {{ $notification['current_stock'] }} unit
                                </strong>
                            </div>
                            @if($notification['reorder_point'])
                                <div>
                                    <small class="text-muted">ROP:</small>
                                    <strong class="text-warning">{{ $notification['reorder_point'] }} unit</strong>
                                </div>
                            @endif
                            @if($notification['safety_stock'])
                                <div>
                                    <small class="text-muted">Safety Stock:</small>
                                    <strong class="text-info">{{ $notification['safety_stock'] }} unit</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-md-3">
                        <div class="d-grid gap-2">
                            @if(!$notification['is_read'])
                                <form action="{{ route('notifications.mark-read', $notification['id']) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-check"></i> Tandai Dibaca
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('product.edit', $notification['product_id']) }}" 
                                class="btn btn-success btn-sm">
                                <i class="bi bi-box"></i> Lihat Produk
                            </a>
                            
                            <form action="{{ route('notifications.destroy', $notification['id']) }}" method="POST"
                                onsubmit="return confirm('Hapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 64px; color: #ccc;"></i>
                <h4 class="mt-3 mb-2">Tidak Ada Notifikasi</h4>
                <p class="text-muted mb-3">Semua stok produk dalam kondisi baik</p>
                <form action="{{ route('notifications.check-stock') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Cek Stok Sekarang
                    </button>
                </form>
            </div>
        </div>
    @endforelse

    <!-- Simple Pagination -->
    @if($total > $perPage)
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                @if($page > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ route('notifications.index', array_merge(request()->query(), ['page' => $page - 1])) }}">
                            <i class="bi bi-chevron-left"></i> Sebelumnya
                        </a>
                    </li>
                @endif
                
                <li class="page-item active">
                    <span class="page-link">
                        Halaman {{ $page }} dari {{ ceil($total / $perPage) }}
                    </span>
                </li>
                
                @if($page * $perPage < $total)
                    <li class="page-item">
                        <a class="page-link" href="{{ route('notifications.index', array_merge(request()->query(), ['page' => $page + 1])) }}">
                            Selanjutnya <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    @endif
</div>

<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
    
    .border-2 {
        border-width: 2px !important;
    }
</style>
@endsection