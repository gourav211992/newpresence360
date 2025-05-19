@if ($logedinUser->hasPermission('menu.' . $menu->alias))
    <li class="">
        <a class="d-flex align-items-center" href="{{ $webBaseUrl }}{{ $menu->menu_link }}"><span
                class="menu-item text-truncate">{{ $menu->name }}</span></a>
    </li>
@endif

@if ($menu->childMenus->count())
    @foreach ($menu->childMenus as $childMenu)
        @include('layouts.partials.menu-sub-item', [
            'menu' => $childMenu,
            'webBaseUrl' => $webBaseUrl,
        ])
    @endforeach
@endif
