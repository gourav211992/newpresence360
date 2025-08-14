<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow erpnewsidemenu" data-scroll-to-active="true">

    <div class="shadow-bottom"></div>
    <div class="main-menu-content newmodulleftmenu">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="nav-item">
                <a class="d-flex align-items-center dashboard-icon" href="https://login.thepresence360.com"><i
                        data-feather="home"></i><span class="menu-title text-truncate"
                        data-i18n="Dashboards">HRMS</span></a>

            </li>
            @if ($organization_id == 6)
                <li @if (Route::currentRouteName() == 'crm.home') class="active nav-item" @else class="nav-item" @endif
                    url={{ Route::currentRouteName() }}><a class="d-flex align-items-center"
                        href="{{ route('crm.home') }}"><i data-feather="alert-circle"></i><span
                            class="menu-title text-truncate">CRM</span></a>
                </li>
            @else

            @php
               $baseUrls = [
                    'erp' => 'http://erp.thepresence360.com/',
                    'hrms' => 'https://login.thepresence360.com/',
                    'portal' => 'https://portal.thepresence360.com/',
                    'web' => 'https://web.thepresence360.com/',
                ]; 
               
            @endphp
                
            @foreach ($menues as $menu)
                <?php
                    $webBaseUrl = '/';
                    $serviceGroupAlias = optional(@$menu->menu->serviceGroup)->alias;
                    if (isset($baseUrls[$serviceGroupAlias])) {
                        $webBaseUrl = $baseUrls[$serviceGroupAlias];
                    }
                   
                ?>
                @if ($logedinUser->hasPermission('menu.' . $menu->alias))
                    <li class="nav-item  @if ($menu->childMenus->count()) has-sub @endif">
                        <a class="d-flex align-items-center dashboard-icon"
                            @if (!$menu->childMenus->count()) href="{{$webBaseUrl}}{{ $menu->menu_link }}" @else href="#" @endif>
                            <i data-feather="file-text"></i>
                            <span class="menu-title text-truncate">{{ $menu->name }}</span></a>
                        @if ($menu->childMenus->count())
                            <ul class="menu-content">
                                <?php
                                    $serviceGroupAlias = optional(@$childMenu->menu->serviceGroup)->alias;
                                    if (isset($baseUrls[$serviceGroupAlias])) {
                                        $webBaseUrl = $baseUrls[$serviceGroupAlias];
                                    }
                                ?>
                                @foreach ($menu->childMenus as $childMenu)
                                    @include('layouts.partials.menu-item', [
                                        'menu' => $childMenu,
                                        'webBaseUrl'=> $webBaseUrl
                                    ])
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endif
            @endforeach
            @endif
        </ul>
    </div>

</div>
<!-- END: Main Menu-->
