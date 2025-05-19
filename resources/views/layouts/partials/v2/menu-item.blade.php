
<li>
    <a class="d-flex align-items-center" @if (!$menu->childMenus->count()) href="{{ $menu->generateLink($authSessionUser) }}" @else href="#" @endif>
        <i data-feather="circle"></i>
        <span class="menu-item text-truncate">{{ $menu->name }}</span>
    </a>

    @if ($menu->childMenus->count())
        <ul class="menu-content loanappsub-menu">
            @foreach ($menu->childMenus as $childMenu)

                @if($oauthUser->user_type !== 'IAM-SUPER' && ! in_array('menu.'.$childMenu->alias, $oauthPermissions))
                    @continue
                @endif

                @include('layouts.partials.v2.menu-sub-item', [
                    'menu' => $childMenu
                ])
            @endforeach
        </ul>
    @endif
</li>
