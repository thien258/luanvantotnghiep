<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Aura & Essence - Atelier Admin">
    <base href="{{asset('public/')}}">
    
    <title>Aura & Essence - Dashboard</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top" class="bg-white">

    <div id="wrapper">

        <ul class="navbar-nav bg-white sidebar sidebar-light border-right accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex flex-column align-items-start justify-content-center py-5 px-4" href="{{ route('admin.dashboard') }}">
                <div class="sidebar-brand-text text-dark font-weight-normal h4 mb-0">Aura & Essence</div>
                <div class="text-dark font-weight-bold text-uppercase mt-2 small">Atelier Admin</div>
            </a>

            <hr class="sidebar-divider my-2">

            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-t text-dark mr-3"></i>
                    <span>Dashboard Overview</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.category.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{route('admin.category.index')}}">
                    <i class="fa-solid fa-shapes text-dark mr-3"></i>
                    <span>Category</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.brand.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{route('admin.brand.index')}}">
                    <i class="fa-solid fa-tag text-dark mr-3"></i>
                    <span>Brand</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.concentration.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{route('admin.concentration.index')}}">
                    <i class="fa-solid fa-droplet text-dark mr-3"></i>
                    <span>Concentration</span>
                </a>
            </li>

       
            
            <li class="nav-item {{ request()->routeIs('admin.festival.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{route('admin.festival.index')}}">
                    <i class="fa-solid fa-calendar-alt text-dark mr-3"></i>
                    <span>Festival</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.orders.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{route('admin.orders.index')}}">
                    <i class="fa-solid fa-calendar-alt text-dark mr-3"></i>
                    <span>Order</span>
                </a>
            </li>
            

            <li class="nav-item {{ request()->routeIs('admin.product.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{route('admin.product.index')}}">
                    <i class="fa-solid fa-bottle-droplet text-dark mr-3"></i>
                    <span>Product</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.contacts.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.contacts.index') }}">
                    <i class="fa-regular fa-circle-question text-dark mr-3"></i>
                    <span>Contact</span>
                </a>
            </li>

            <li class="nav-item {{ request()->routeIs('admin.orders.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.orders.index') }}">
                    <i class="fa-solid fa-bag-shopping text-dark mr-3"></i>
                    <span>Order</span>
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
            </li>

            <li class="nav-item {{ request()->routeIs('admin.footer.index') ? 'active font-weight-bold' : '' }}">
                <a class="nav-link text-dark" href="{{ route('admin.footer.index') }}">
                    <i class="fa-solid fa-grip-lines-bottom text-dark mr-3"></i>
                    <span>Footer</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block mt-auto mb-0">

            <li class="nav-item p-3 d-flex align-items-center">
                <img class="img-profile rounded-circle border border-dark mr-3" src="img/undraw_profile.svg" width="35" height="35" alt="User">
                <div class="d-flex flex-column text-left">
                    <span class="text-dark font-weight-bold text-uppercase small">{{ auth()->user()->name ?? 'ADMIN USER' }}</span>
                    <span class="text-muted text-uppercase"><small>Quản trị viên</small></span>
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
                            <a class="nav-link dropdown-toggle text-dark" href="#" id="alertsDropdown" role="button" data-toggle="dropdown">
                                <i class="fa-regular fa-bell h5 mb-0"></i>
                            </a>
                        </li>

                        <li class="nav-item mx-1">
                            <a class="nav-link text-dark" href="#">
                                <i class="fa-solid fa-gear h5 mb-0"></i>
                            </a>
                        </li>

                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <i class="fa-regular fa-circle-user h4 mb-0"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border rounded-0 animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item text-dark" href="#" data-toggle="modal" data-target="#logoutModal">
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>
	<script src="{{ asset('js/editProduct.js') }}"></script>
		<script src="{{ asset('js/addProduct.js') }}"></script>
        <script src="{{ asset('js/adminProduct_search.js') }}"></script>

</body>

</html>