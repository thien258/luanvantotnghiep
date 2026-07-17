<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Aura & Essence - Atelier Admin">

    <title>Aura & Essence - Dashboard</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>

<body id="page-top" class="bg-white">

    <div id="wrapper">

        <ul class="navbar-nav bg-white sidebar sidebar-light border-right accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex flex-column align-items-start justify-content-center py-5 px-4" href="{{ route('admin.dashboard') }}">
                <div class="sidebar-brand-text text-dark font-weight-normal h4 mb-0">Aura & Essence</div>
                <div class="text-dark font-weight-bold text-uppercase mt-2 small">Atelier Admin</div>
            </a>

            <hr class="sidebar-divider my-2">

            @php $role = auth()->user()->role; @endphp

            {{-- Dashboard: admin, director và root --}}
            @if(in_array($role, ['admin', 'director', 'root']))
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-chart-line text-dark mr-3"></i>
                    <span>
                        @if($role === 'director') Báo cáo Doanh thu
                        @elseif($role === 'root') Dashboard (Root)
                        @else Dashboard Overview
                        @endif
                    </span>                </a>
            </li>
            @endif

            {{-- ── DROPDOWN DANH MỤC & SẢN PHẨM: admin và root ────────── --}}
            @if(in_array($role, ['admin', 'root']))
            @php
                $catalogActive = request()->routeIs('admin.category.*')
                              || request()->routeIs('admin.brand.*')
                              || request()->routeIs('admin.concentration.*')
                              || request()->routeIs('admin.festival.*')
                              || request()->routeIs('admin.product.*');
            @endphp
            <li class="nav-item">
                <a class="nav-link text-dark d-flex justify-content-between align-items-center"
                   href="#catalogSubmenu" data-toggle="collapse" role="button"
                   aria-expanded="{{ $catalogActive ? 'true' : 'false' }}"
                   aria-controls="catalogSubmenu">
                    <span>
                        <i class="fa-solid fa-layer-group text-dark mr-3"></i>
                        <span>Danh mục &amp; Sản phẩm</span>
                    </span>
                </a>
                <div class="collapse {{ $catalogActive ? 'show' : '' }}" id="catalogSubmenu">
                    <ul class="nav flex-column pl-4 pb-1">
                        <li class="nav-item {{ request()->routeIs('admin.category.*') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.category.index') }}">
                                <i class="fa-solid fa-shapes text-muted mr-2"></i>Category
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.brand.*') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.brand.index') }}">
                                <i class="fa-solid fa-tag text-muted mr-2"></i>Brand
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.concentration.*') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.concentration.index') }}">
                                <i class="fa-solid fa-droplet text-muted mr-2"></i>Concentration
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.festival.*') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.festival.index') }}">
                                <i class="fa-solid fa-calendar-alt text-muted mr-2"></i>Festival
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('admin.product.index') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.product.index') }}">
                                <i class="fa-solid fa-bottle-droplet text-muted mr-2"></i>Product
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- ── DROPDOWN ORDER & KHO: admin, warehouse và root ─────────── --}}
            @if(in_array($role, ['admin', 'warehouse', 'root']))
            @php
                $orderKhoActive = request()->routeIs('admin.orders.*')
                               || request()->routeIs('admin.product.warehouse.index')
                               || request()->routeIs('admin.warehouse.imports*');
            @endphp
            <li class="nav-item">
                <a class="nav-link text-dark d-flex justify-content-between align-items-center"
                   href="#orderKhoSubmenu" data-toggle="collapse" role="button"
                   aria-expanded="{{ $orderKhoActive ? 'true' : 'false' }}"
                   aria-controls="orderKhoSubmenu">
                    <span>
                        <i class="fa-solid fa-boxes-stacked text-dark mr-3"></i>
                        <span>Order &amp; Kho</span>
                    </span>
                </a>
                <div class="collapse {{ $orderKhoActive ? 'show' : '' }}" id="orderKhoSubmenu">
                    <ul class="nav flex-column pl-4 pb-1">

                        {{-- Order: chỉ warehouse --}}
                        @if($role === 'warehouse')
                        <li class="nav-item {{ request()->routeIs('admin.orders.index') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.orders.index') }}">
                                <i class="fa-solid fa-bag-shopping text-muted mr-2"></i>Order
                            </a>
                        </li>
                        @endif

                        {{-- Hàng Hỏng: admin và warehouse --}}
                        <li class="nav-item {{ request()->routeIs('admin.orders.damaged') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.orders.damaged') }}">
                                <i class="fa-solid fa-triangle-exclamation text-danger mr-2"></i>Hàng Hỏng
                            </a>
                        </li>

                        {{-- Kho & Cảnh báo Sale: chỉ admin --}}
                        @if($role === 'admin')
                        <li class="nav-item {{ request()->routeIs('admin.product.warehouse.index') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.product.warehouse.index') }}">
                                <i class="fa-solid fa-boxes-packing text-muted mr-2"></i>Kho &amp; Cảnh báo Sale
                            </a>
                        </li>
                        @endif

                        {{-- Nhập Kho: admin và warehouse --}}
                        <li class="nav-item {{ request()->routeIs('admin.warehouse.imports') || request()->routeIs('admin.warehouse.imports.show') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.warehouse.imports') }}">
                                <i class="fa-solid fa-file-arrow-up text-muted mr-2"></i>Nhập Kho
                            </a>
                        </li>

                    </ul>
                </div>
            </li>
            @endif

            {{-- ── DROPDOWN NSX: admin, manufacturer, warehouse, root ──────── --}}
            @if(in_array($role, ['admin', 'manufacturer', 'warehouse', 'root']))
            @php
                $nsxActive = request()->routeIs('admin.supplier-offers.*')
                          || request()->routeIs('admin.purchase-orders.*')
                          || request()->routeIs('admin.procurement.*');
            @endphp
            <li class="nav-item">
                <a class="nav-link text-dark d-flex justify-content-between align-items-center"
                   href="#nsxSubmenu" data-toggle="collapse" role="button"
                   aria-expanded="{{ $nsxActive ? 'true' : 'false' }}"
                   aria-controls="nsxSubmenu">
                    <span>
                        <i class="fa-solid fa-building text-dark mr-3"></i>
                        <span>Nhà Sản Xuất</span>
                    </span>
                </a>
                <div class="collapse {{ $nsxActive ? 'show' : '' }}" id="nsxSubmenu">
                    <ul class="nav flex-column pl-4 pb-1">

                        {{-- Báo giá NSX: admin, manufacturer và root --}}
                        @if(in_array($role, ['admin', 'manufacturer', 'root']))
                        <li class="nav-item {{ request()->routeIs('admin.supplier-offers.*') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.supplier-offers.index') }}">
                                <i class="fa-solid fa-file-invoice text-muted mr-2"></i>Báo giá NSX
                            </a>
                        </li>
                        @endif

                        {{-- Đơn đặt hàng: tất cả 3 role --}}
                        <li class="nav-item {{ request()->routeIs('admin.purchase-orders.*') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.purchase-orders.index') }}">
                                <i class="fa-solid fa-cart-flatbed text-muted mr-2"></i>Đơn đặt hàng
                            </a>
                        </li>

                        {{-- Yêu cầu nhập hàng: admin, manufacturer và root --}}
                        @if(in_array($role, ['admin', 'manufacturer', 'root']))
                        <li class="nav-item {{ request()->routeIs('admin.procurement.*') ? 'active font-weight-bold' : '' }}">
                            <a class="nav-link text-dark py-1 small" href="{{ route('admin.procurement.index') }}">
                                <i class="fa-solid fa-bullhorn text-muted mr-2"></i>Yêu cầu nhập hàng
                            </a>
                        </li>
                        @endif

                    </ul>
                </div>
            </li>
            @endif

            {{-- Contact, Title, User, Footer: admin và root --}}
            @if(in_array($role, ['admin', 'root']))
            <li class="nav-item {{ request()->routeIs('admin.contacts.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.contacts.index') }}">
                    <i class="fa-regular fa-circle-question text-dark mr-3"></i>
                    <span>Contact</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.title.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.title.index') }}">
                    <i class="fa-solid fa-heading text-dark mr-3"></i>
                    <span>Title</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.user.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.user.index') }}">
                    <i class="fa-solid fa-user text-dark mr-3"></i>
                    <span>User</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.footer.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.footer.index') }}">
                    <i class="fa-solid fa-grip-lines-bottom text-dark mr-3"></i>
                    <span>Footer</span>
                </a>
            </li>
            @endif

            {{-- User: director cũng thấy để tắt/bật tài khoản --}}
            @if($role === 'director')
            <li class="nav-item {{ request()->routeIs('admin.user.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.user.index') }}">
                    <i class="fa-solid fa-user text-dark mr-3"></i>
                    <span>User</span>
                </a>
            </li>
            @endif

            {{-- Activity Log: chỉ director xem, root không thấy --}}
            @if($role === 'director')
            <li class="nav-item {{ request()->routeIs('admin.activity-log.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.activity-log.index') }}">
                    <i class="fa-solid fa-clock-rotate-left text-dark mr-3"></i>
                    <span>Lịch sử hoạt động Root</span>
                </a>
            </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block mt-auto mb-0">

            <li class="nav-item p-3 d-flex align-items-center">
                <div class="d-flex flex-column text-left">
                    <span class="text-dark font-weight-bold text-uppercase small">{{ auth()->user()->name ?? 'ADMIN USER' }}</span>
                    <span class="text-muted text-uppercase"><small>
                        @php
                            $roleLabel = [
                                'admin'        => 'Quản trị viên',
                                'root'         => 'Root',
                                'director'     => 'Giám đốc',
                                'warehouse'    => 'Nhân viên kho',
                                'manufacturer' => 'Nhà sản xuất',
                                'user'         => 'Người dùng',
                            ][$role] ?? $role;
                        @endphp
                        {{ $roleLabel }}
                    </small></span>
                </div>
            </li>

        </ul>
        <div id="content-wrapper" class="d-flex flex-column bg-white">

            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top border-bottom shadow-none">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3 text-dark">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div class="d-none d-md-block h4 mb-0 font-weight-normal text-dark ml-4 text-uppercase">
                        Aura & Essence
                    </div>

                    <ul class="navbar-nav ml-auto align-items-center">

                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <i class="fa-regular fa-circle-user h4 mb-0"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border rounded-0 animated--grow-in" aria-labelledby="userDropdown">
                                <div class="dropdown-header small text-muted px-3 py-2 border-bottom">
                                    <i class="fa-regular fa-circle-user me-1"></i>
                                    {{ Auth::user()->name }}
                                    <span class="badge bg-dark rounded-0 ms-1" style="font-size:0.6rem;">{{ Auth::user()->role }}</span>
                                </div>
                                <a class="dropdown-item text-dark py-2" href="{{ route('profile.index') }}">
                                    <i class="fa-solid fa-user-pen fa-sm fa-fw mr-2 text-muted"></i>
                                    Trang cá nhân
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-dark py-2" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-dark"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>
                </nav>

                <div class="container-fluid px-4 bg-white">
                    @yield('body')
                </div>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded-0 bg-dark" href="#page-top">
        <i class="fas fa-angle-up text-white"></i>
    </a>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content rounded-0 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title text-dark font-weight-normal" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close text-dark" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body text-dark">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer border-top-0">
                    <button class="btn btn-light border rounded-0 text-dark" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-dark rounded-0" href="{{ route('logout') }}">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/admin/sb-admin-2.min.js') }}"></script>
    @yield('script')

</body>

</html>
