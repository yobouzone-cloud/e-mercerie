<div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle position-relative" 
            type="button" 
            id="notificationsDropdown" 
            data-bs-toggle="dropdown" 
            aria-expanded="false">
        <i class="fa-solid fa-bell"></i>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="width: 350px;">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
        </li>
        
        @forelse(auth()->user()->notifications->take(10) as $notification)
            <li>
                <div class="dropdown-item {{ $notification->read_at ? '' : 'bg-light' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="mb-1 small">{{ $notification->data['message'] ?? 'Notification' }}</p>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        @if(!$notification->read_at)
                            <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="ms-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Marquer comme lu">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                    @if(isset($notification->data['url']))
                        <div class="mt-2">
                            <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-primary">
                                Voir
                            </a>
                        </div>
                    @endif
                </div>
            </li>
            @if(!$loop->last)
                <li><hr class="dropdown-divider"></li>
            @endif
        @empty
            <li class="dropdown-item text-center text-muted">
                <i class="fa-solid fa-bell-slash fa-2x mb-2"></i>
                <p>Aucune notification</p>
            </li>
        @endforelse
        
        @if(auth()->user()->notifications->count() > 10)
            <li>
                <div class="dropdown-item text-center">
                    <a href="{{ route('notifications.index') }}" class="text-primary">
                        Voir toutes les notifications
                    </a>
                </div>
            </li>
        @endif
    </ul>
</div>