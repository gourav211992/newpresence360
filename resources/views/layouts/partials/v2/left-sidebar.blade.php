<?php

$services = new App\Lib\Services\AccessRightsService;
$data = $services->fetchAuthData();
$oauthUser  = $data['oauthUser'];
$oauthPermissions = $data['oauthPermissions'];
?>
<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow erpnewsidemenu" data-scroll-to-active="true">

    <div class="shadow-bottom"></div>
    <div class="main-menu-content newmodulleftmenu">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
           
            @foreach ($data['oauthMenu'] as $menu)

                    
                @if($oauthUser->user_type !== 'IAM-SUPER' && ! in_array('menu.'.$menu->alias, $oauthPermissions))
                    @continue
                @endif
                  
                    <li class="nav-item  @if ($menu->childMenus->count()) has-sub @endif">
                        <a class="d-flex align-items-center dashboard-icon"
                            @if (!$menu->childMenus->count()) href="{{ $menu->generateLink($authSessionUser) }}" @else href="#" @endif>
                            <i data-feather="{{ $menu->icon ?: 'file-text' }}"></i>
                            <span class="menu-title text-truncate">{{ $menu->name }}</span></a>

                        @if ($menu->childMenus->count())
                            <ul class="menu-content">
                                @foreach ($menu->childMenus as $childMenu)
                                 
                                    @if($oauthUser->user_type !== 'IAM-SUPER' && ! in_array('menu.'.$childMenu->alias, $oauthPermissions))
                                        @continue
                                    @endif

                                    @include('layouts.partials.v2.menu-item', [
                                        'menu' => $childMenu,
                                        'oauthUser' => $oauthUser,
                                        'oauthPermissions' => $oauthPermissions
                                    ])
                                @endforeach
                            </ul>
                        @endif
                    </li>
            @endforeach
            {{-- <li class=" nav-item">
            <a class="d-flex align-items-center" href="{{ route('admin.company.index') }}" style="color: #1772eb;">
                <i data-feather="file-text"></i>
                <span class="menu-title text-truncate" data-i18n="Dashboards">
                    Companies
                </span>
            </a>

            </li>

            <li class=" nav-item">
                <a class="d-flex align-items-center" href="{{ route('admin.group.index') }}"  style="color: #1772eb;">
                    <i data-feather="file-text"></i>
                    <span class="menu-title text-truncate" data-i18n="Dashboards">
                        Groups
                    </span>
                </a>
            </li>

            <li class=" nav-item">
                <a class="d-flex align-items-center" href="{{ route('admin.user.index') }}" style="color: #c6178a;">
                    <i data-feather="file-text"></i>
                    <span class="menu-title text-truncate" data-i18n="Dashboards">
                        Users
                    </span>
                </a>
            </li>

            <li class=" nav-item">
                <a class="d-flex align-items-center" href="{{ route('admin.organizations') }}"  style="color: #1772eb;">
                    <i data-feather="file-text"></i>
                    <span class="menu-title text-truncate" data-i18n="Dashboards">
                        Organizations
                    </span>
                </a>
            </li>



            <li class=" nav-item">
                <a class="d-flex align-items-center" href="{{ route('admin.services') }}"  style="color: #1772eb;">
                    <i data-feather="file-text"></i>
                    <span class="menu-title text-truncate" data-i18n="Dashboards">
                        Service
                    </span>
                </a>
            </li>

            <li class=" nav-item">
                <a class="d-flex align-items-center" href="{{ route('admin.menus') }}" style="color: #6b12b7;">
                    <i data-feather="file-text"></i>
                    <span class="menu-title text-truncate" data-i18n="Dashboards">
                     Menus
                    </span>
                </a>
            </li> --}}

        </ul>
    </div>

</div>
<!-- END: Main Menu-->
