@if ($logedinUser->hasPermission('menu.' . $menu->alias))
    <li>
        <a class="d-flex align-items-center"
            @if (!$menu->childMenus->count()) href="{{ $webBaseUrl }}{{ $menu->menu_link }}" @else href="#" @endif>
            <i data-feather="circle"></i>
            <span class="menu-item text-truncate">{{ $menu->name }}</span>
        </a>

        @if ($menu->childMenus->count())
            <ul class="menu-content loanappsub-menu">
                @foreach ($menu->childMenus as $childMenu)
                    @include('layouts.partials.menu-sub-item', [
                        'menu' => $childMenu,
                        'webBaseUrl' => $webBaseUrl,
                    ])
                @endforeach
            </ul>
        @endif
    </li>
@endif
