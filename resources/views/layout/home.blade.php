	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<base href="{{ asset('public/') }}">
		<title>Aroma Shop - Home</title>
		<link rel="icon" href="img/Fevicon.png" type="image/png">
		<link rel="stylesheet" href="vendors/bootstrap/bootstrap.min.css">
		<link rel="stylesheet" href="vendors/fontawesome/css/all.min.css">
		<link rel="stylesheet" href="vendors/themify-icons/themify-icons.css">
		<link rel="stylesheet" href="vendors/nice-select/nice-select.css">
		<link rel="stylesheet" href="vendors/owl-carousel/owl.theme.default.min.css">
		<link rel="stylesheet" href="vendors/owl-carousel/owl.carousel.min.css">

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<script>
			window.APP_URL = "{{ url('/') }}";
		</script>
		<meta name="csrf-token" content="{{ csrf_token() }}">

		<link rel="stylesheet" href="css/style.css">
	</head>

	<body>
		<!--================ Start Header Menu Area =================-->
		<header class="header_area">
			<div class="main_menu">
				<nav class="navbar navbar-expand-lg navbar-light">
					<div class="container">
						<a class="navbar-brand logo_h" href="{{route('welcome')}}"><img src="img/logo.png" alt=""></a>
						<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
							aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<div class="collapse navbar-collapse offset" id="navbarSupportedContent">
							<div class="navbar-collapse-inner">
								<ul class="nav navbar-nav menu_nav ml-auto mr-auto align-items-lg-center">
									<li class="nav-item active"><a class="nav-link" style="color: #000 !important;" href="{{ route('welcome') }}">Home</a></li>
									<li class="nav-item active"><a class="nav-link" style="color: #000 !important;" href="{{ route('show_products') }}">Products</a></li>

									<li class="nav-item submenu dropdown">
										<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
											Category <i class="fas fa-chevron-down fa-xs ml-1"></i>
										</a>
										<ul class="dropdown-menu shadow border-0 rounded-lg py-2">
											@forelse($categories as $object)
											<li>
												<a class="dropdown-item px-4 py-2" href="{{ route('category_product', ['category' => $object->id]) }}">
													<i class="fas fa-tag fa-xs mr-2 text-muted"></i>{{ $object->name }}
												</a>
											</li>
											@empty
											<li><span class="dropdown-item text-muted">Chưa có danh mục</span></li>
											@endforelse
										</ul>
									</li>

									<li class="nav-item submenu dropdown">
										<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
											Brand <i class="fas fa-chevron-down fa-xs ml-1"></i>
										</a>
										<ul class="dropdown-menu shadow border-0 rounded-lg py-2">
											@forelse($brands as $object)
											<li>
												<a class="dropdown-item px-4 py-2" href="{{ route('brand_product', ['brand' => $object->id]) }}">
													<i class="fas fa-gem fa-xs mr-2 text-muted"></i>{{ $object->name }}
												</a>
											</li>
											@empty
											<li><span class="dropdown-item text-muted">Chưa có thương hiệu</span></li>
											@endforelse
										</ul>
									</li>

                                    @if($festivals->isNotEmpty())
                                    <li class="nav-item submenu dropdown">
                                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            Festival <i class="fas fa-chevron-down fa-xs ml-1"></i>
                                        </a>
                                        <ul class="dropdown-menu shadow border-0 rounded-lg py-2">
                                            @foreach($festivals as $object)
                                            <li>
                                                <a class="dropdown-item px-4 py-2" href="{{ route('festival_product', ['festival' => $object->id]) }}">
                                                    <i class="fas fastar- fa-xs mr-2 text-muted"></i>{{ $object->name }}
                                                </a>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                    @endif

									@guest
									<li class="nav-item submenu dropdown">
										<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Pages</a>
										<ul class="dropdown-menu">
											<li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
											<li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
										</ul>
									</li>
									@endguest

									<li class="nav-item"><a class="nav-link" href="{{ route('contact.index') }}">Contact</a></li>
								</ul>

								<div class="header-actions">
									<form action="{{ route('home.search') }}" method="GET" class="header-search-form position-relative">
										<div class="header-search-wrap">
											<input type="text"
												name="keyword"
												id="search-input"
												class="form-control form-control-sm shadow-none border-dark rounded-0 header-search-input"
												placeholder="Tìm kiếm..."
												value="{{ request('keyword') }}"
												required
												autocomplete="off">

											<i class="fa-solid fa-magnifying-glass position-absolute text-muted header-search-icon"></i>
										</div>

										<div id="search-suggestions" class="dropdown-menu w-100 p-0 shadow-sm border mt-1 header-search-suggestions"></div>
									</form>

									@auth
									<a href="{{ route('carts.index') }}" class="header-icon-link" title="Giỏ hàng">
										<i class="fa-solid fa-shopping-cart"></i>
									</a>

									<a href="{{ route('order.index') }}" class="header-icon-link" title="Thanh toán">
										<i class="fa-solid fa-credit-card"></i>
									</a>


									<div class="nav-item submenu dropdown">
										<a href="#" class="nav-link dropdown-toggle header-user-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
											<i class="fa-solid fa-user mr-1"></i>
											<span>{{ Auth::user()->name }}</span>
										</a>
										<ul class="dropdown-menu dropdown-menu-right header-user-menu">
											<li class="nav-item">
												<a class="nav-link" href="{{ route('profile.index') }}">
													<i class="fa-solid fa-id-card mr-2"></i> Hồ sơ
												</a>
											</li>
											<li class="nav-item">
												<a href="{{ route('order.history') }}" class="nav-link">
													<i class="fa-solid fa-clock-rotate-left"></i> lịch sử
												</a>
											</li>

											<li class="nav-item">
												<a class="nav-link" href="{{ route('logout') }}"
													onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
													<i class="fa-solid fa-right-from-bracket mr-2"></i> Đăng xuất
												</a>
												<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
													@csrf
												</form>
											</li>
										</ul>
									</div>
									@endauth
								</div>
							</div>
						</div>
					</div>
				</nav>
			</div>
		</header>
		<!--================ End Header Menu Area =================-->

		@yield('body')


		<!--================ Start footer Area  =================-->
		<footer class="footer">
			<div class="footer-area">
				<div class="container">
					<div class="row section_gap">

						<!-- CỘT TRÁI -->
						<div class="col-lg-6 col-md-6 col-sm-12">
							<div class="single-footer-widget tp_widgets">
								<h4 class="footer_title large_title">{{ $footer->header }}</h4>
								<p>
									{{ $footer->textheader }}
								</p>

							</div>
						</div>

						<!-- CỘT PHẢI -->
						<div class="col-lg-6 col-md-6 col-sm-12">
							<div class="single-footer-widget tp_widgets">
								<h4 class="footer_title">{{ $footer->header2 }}</h4>
								<div class="ml-40">
									<p class="sm-head">
										<span class="fa fa-location-arrow"></span>
										{{ $footer->address }}
									</p>


									<p class="sm-head">
										<span class="fa fa-phone"></span>
										{{ $footer->phone }}
									</p>


									<p class="sm-head">
										<span class="fa fa-envelope"></span>
										{{ $footer->email }}
									</p>

								</div>
							</div>
						</div>

					</div>
				</div>
			</div>

			<div class="footer-bottom">
				<div class="container">
					<div class="row d-flex">
						<p class="col-lg-12 footer-text text-center">
							<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
							Copyright &copy;<script>
								document.write(new Date().getFullYear());
							</script> All rights reserved | This template is made with <i class="fa fa-heart" aria-hidden="true"></i>
							<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
						</p>
					</div>
				</div>
			</div>
		</footer>
		<!--================ End footer Area  =================-->



		<script src="vendors/jquery/jquery-3.2.1.min.js"></script>
		<script src="vendors/bootstrap/bootstrap.bundle.min.js"></script>
		<script src="vendors/skrollr.min.js"></script>
		<script src="vendors/owl-carousel/owl.carousel.min.js"></script>
		<script src="vendors/nice-select/jquery.nice-select.min.js"></script>
		<script src="vendors/jquery.ajaxchimp.min.js"></script>
		<script src="vendors/mail-script.js"></script>
		<script src="js/main.js"></script>


		<script src="{{ asset('js/live-search.js') }}"></script>

		@yield('script')
	</body>

	</html>