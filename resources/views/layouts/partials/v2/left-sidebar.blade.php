
<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow erpnewsidemenu" data-scroll-to-active="true">

    <div class="shadow-bottom"></div>
    <div class="main-menu-content newmodulleftmenu">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
           
            @foreach ($iamMenu as $menu)
                @if($iamUser->user_type !== 'IAM-SUPER' && ! in_array('menu.' . $menu['alias'], $iamPermissions))
                    @continue
                @endif
                <li class="nav-item  @if (!empty($menu['childMenus'])) has-sub @endif">
                    <a class="d-flex align-items-center dashboard-icon"
                        @if (empty($menu['childMenus'])) href="{{ $menu['url'] }}" @else href="#" @endif>
                        <i data-feather="{{ $menu['icon'] ?? 'file-text' }}"></i>
                        <span class="menu-title text-truncate">{{ $menu['name'] }}</span></a>

                    @if (!empty($menu['childMenus']))
                        <ul class="menu-content">
                            @foreach ($menu['childMenus'] as $childMenu)
                                @if($iamUser->user_type !== 'IAM-SUPER' && ! in_array('menu.' . $childMenu['alias'], $iamPermissions))
                                    @continue
                                @endif
                                @include('p360::layouts.partials.menu-item', [
                                    'menu' => $childMenu,
                                    'iamUser' => $iamUser,
                                    'iamPermissions' => $iamPermissions,
                                    'iamAppUrls' => $iamAppUrls
                                ])
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
            
        </ul>
    </div>

</div>
<!-- END: Main Menu-->
