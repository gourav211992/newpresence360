<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow erpnewsidemenu" data-scroll-to-active="true">

    <div class="shadow-bottom"></div>
    <div class="main-menu-content newmodulleftmenu">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <li>
                <a class="d-flex align-items-center dashboard-icon {{ Request::routeIs('supplier.dashboard') ? 'active' : '' }}" href="{{ route('supplier.dashboard') }}">
                    <i data-feather="home"></i>
                    <span class="menu-item text-truncate" data-i18n="eCommerce">Dashboard</span>
                </a>
            </li>
            <li>
                <a class="d-flex align-items-center dashboard-icon {{ Request::routeIs('supplier.po.index') ? 'active' : '' }}" href="{{ route('supplier.po.index') }}">
                    <i data-feather="home"></i>
                    <span class="menu-item text-truncate" data-i18n="eCommerce">Purchase Order</span>
                </a>
            </li>
            <li>
                <a class="d-flex align-itemsitemsitems-center {{ Request::routeIs('supplier.invoice.index') ? 'active' : '' }}" href="{{ route('supplier.invoice.index') }}">
                    <i data-feather="file-text"></i>
                    <span class="menu-item text-truncate" data-i18n="eCommerce">Supplier Invoice</span>
                </a>
            </li>
            <li>
                <a class="d-flex align-itemsitemsitems-center {{ Request::routeIs('supplier.transporter-bid.index') ? 'active' : '' }}" href="{{ route('supplier.transporter.index') }}">
                    <i data-feather="file-text"></i>
                    <span class="menu-item text-truncate" data-i18n="eCommerce">Transporter Request</span>
                </a>
            </li>
        </ul>
    </div>

</div>
<!-- END: Main Menu-->
