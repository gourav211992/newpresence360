@if ($logedinUser->hasPermission('menu.' . $menu->alias))
    <li><a class="d-flex align-items-center" href="{{$webBaseUrl}}{{ $menu->menu_link }}">
            <i data-feather="circle"></i>
            <span class="menu-item text-truncate">{{ $menu->name }}</span></a>
    </li>
@endif

@foreach ($menu->childMenus as $childMenu)
    @include('layouts.partials.menu-item', [
        'menu' => $childMenu,
    ])
@endforeach
