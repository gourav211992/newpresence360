
@php
// use App\Helpers\Helper;
// $user = get_class(Helper::getAuthenticatedUser());
@endphp
{{-- <li class="nav-item dropdown dropdown-notification me-25">
    <a class="nav-link" href="#" data-bs-toggle="dropdown">
        <i class="ficon" data-feather="bell"></i><span
            class="badge rounded-pill bg-danger badge-up count">@if(!empty($user::find(Helper::getAuthenticatedUser()->id)) && !empty($user::find(Helper::getAuthenticatedUser()->id)->unreadNotifications) ) {{ $user::find(Helper::getAuthenticatedUser()->id)->unreadNotifications->count() }} @endif</span>
    </a>
    @if(!empty($user::find(Helper::getAuthenticatedUser()->id)) && !empty($user::find(Helper::getAuthenticatedUser()->id)->unreadNotifications))
    @php
        $hasUnreadNotifications = $user::find(Helper::getAuthenticatedUser()->id)->unreadNotifications->count() > 0;
    @endphp

    <ul class="dropdown-menu dropdown-menu-media dropdown-menu-end" id="notification-list">
        <li class="dropdown-menu-header">
            <div class="dropdown-header d-flex">
                <h4 class="notification-title mb-0 me-auto">Notifications</h4>
                <div class="badge rounded-pill badge-light-primary count2">{{ $user::find(Helper::getAuthenticatedUser()->id)->unreadNotifications->count() }}
                </div>
            </div>
        </li>
        <li class="scrollable-container media-list" id="list_noti">

            @php
                $notifications = $user::find(Helper::getAuthenticatedUser()->id)->notifications;
            @endphp
            @foreach ($notifications as $notification)
            @isset($notification->data)
            @if(!empty($notification->data['description']) || !empty($notification->data['message']))
            <a class="d-flex"
                    href="{{ $notification->read_at == null ? route('notification.read', $notification->id) : '#' }}">
                    <div
                        class="list-item d-flex align-items-start {{ $notification->read_at ? 'read-notification' : 'unread-notification' }}">
                        <div class="me-1">
                            <div class="avatar">
                                <img src="{{ url('app-assets/images/portrait/small/avatar-s-3.jpg') }}"
                                     alt="avatar" width="32" height="32">
                            </div>
                        </div>
                        <div class="list-item-body flex-grow-1">
                            <p class="media-heading">
                                <span class="fw-bolder">{{ $notification->data['title'] }}</span><br>
                                {{ $notification->data['description'] ?? $notification->data['message'] }}
                            </p>
                            <small class="notification-text">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </a>

            @endif
        @endisset

            @endforeach
        </li>
        <li class="dropdown-menu-footer">
            <a class="btn btn-primary w-100 {{ !$hasUnreadNotifications ? 'disabled' : '' }}"
                href="{{ route('notifications.readAll') }}">Read all notifications</a>
        </li>
    </ul>
    @endif
</li> --}}
