@extends('layouts.app')

@section('title', 'Semua Notifikasi')

@section('content')
<div class="container mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-bell text-blue-600 mr-3"></i>
                    Notifikasi
                </h1>
                <p class="text-gray-600 mt-1">Kelola semua pemberitahuan Anda</p>
            </div>
            
            @if($notifications->where('read_at', null)->count() > 0)
                <button 
                    id="markAllReadBtn"
                    class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium shadow-md"
                >
                    <i class="fas fa-check-double mr-2"></i>
                    Tandai Semua Dibaca
                </button>
            @endif
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card-friendly overflow-hidden">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $iconColors = [
                    'amber' => 'bg-amber-100 text-amber-600',
                    'blue' => 'bg-blue-100 text-blue-600',
                    'green' => 'bg-green-100 text-green-600',
                    'red' => 'bg-red-100 text-red-600',
                    'orange' => 'bg-orange-100 text-orange-600',
                ];
                $iconColor = $iconColors[$data['icon_color'] ?? 'blue'] ?? 'bg-blue-100 text-blue-600';
            @endphp
            
            <div class="notification-item p-6 border-b border-gray-100 hover:bg-gray-50 transition-colors {{ $notification->read_at ? '' : 'unread' }}"
                 data-notification-id="{{ $notification->id }}">
                <div class="flex gap-4">
                    <!-- Icon -->
                    <div class="w-12 h-12 {{ $iconColor }} rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas {{ $data['icon'] ?? 'fa-bell' }} text-xl"></i>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-bold text-gray-900 text-lg">
                                {{ $data['title'] ?? 'Notifikasi' }}
                            </h3>
                            @if(!$notification->read_at)
                                <span class="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0 mt-1"></span>
                            @endif
                        </div>
                        
                        <p class="text-gray-600 mb-3">
                            {{ $data['message'] ?? '' }}
                        </p>
                        
                        @if(isset($data['transaction_id']))
                            <a href="{{ route('transactions.index') }}" 
                               class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                                <i class="fas fa-arrow-right mr-1"></i>
                                Lihat Detail Peminjaman
                            </a>
                        @endif
                        
                        <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
                            <span>
                                <i class="far fa-clock mr-1"></i>
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                            
                            @if(isset($data['due_date']))
                                <span>
                                    <i class="far fa-calendar mr-1"></i>
                                    Jatuh tempo: {{ \Carbon\Carbon::parse($data['due_date'])->format('d M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col gap-2">
                        @if(!$notification->read_at)
                            <button 
                                class="mark-read-btn p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                                data-notification-id="{{ $notification->id }}"
                                title="Tandai sebagai dibaca"
                            >
                                <i class="fas fa-check"></i>
                            </button>
                        @endif
                        
                        <button 
                            class="delete-notification-btn p-2 text-red-600 hover:bg-red-50 rounded-lg"
                            data-notification-id="{{ $notification->id }}"
                            title="Hapus notifikasi"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-16 text-center">
                <i class="fas fa-bell-slash text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak Ada Notifikasi</h3>
                <p class="text-gray-500">Anda belum memiliki notifikasi apapun</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Mark single notification as read
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            const notificationItem = this.closest('.notification-item');
            
            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationItem.classList.remove('unread');
                    this.remove();
                    
                    // Hide "Mark All Read" button if no unread notifications
                    if (document.querySelectorAll('.notification-item.unread').length === 0) {
                        document.getElementById('markAllReadBtn')?.remove();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Mark all as read
    document.getElementById('markAllReadBtn')?.addEventListener('click', function() {
        if (!confirm('Tandai semua notifikasi sebagai sudah dibaca?')) return;
        
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('unread');
                });
                document.querySelectorAll('.mark-read-btn').forEach(btn => btn.remove());
                this.remove();
                
                // Show success message
                alert('Semua notifikasi telah ditandai sebagai sudah dibaca');
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Delete notification
    document.querySelectorAll('.delete-notification-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Hapus notifikasi ini?')) return;
            
            const notificationId = this.dataset.notificationId;
            const notificationItem = this.closest('.notification-item');
            
            fetch(`/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationItem.style.transition = 'opacity 0.3s, transform 0.3s';
                    notificationItem.style.opacity = '0';
                    notificationItem.style.transform = 'translateX(-100%)';
                    setTimeout(() => notificationItem.remove(), 300);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Mark notification as read when clicked
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons
            if (e.target.closest('button') || e.target.closest('a')) return;
            
            if (this.classList.contains('unread')) {
                const notificationId = this.dataset.notificationId;
                
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.remove('unread');
                        this.querySelector('.mark-read-btn')?.remove();
                    }
                });
            }
        });
    });
</script>
@endpush
@endsection