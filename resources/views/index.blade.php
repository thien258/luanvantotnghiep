    @extends('layout/home')
    @section('body')
    <main class="site-main">
<section class="hero-banner mb-5">
        @forelse($title as $banner) {{-- Giả sử biến truyền qua là $titles --}}
        <div class="position-relative w-100 d-flex align-items-center justify-content-center text-white text-center" 
             style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{{ $banner->image }}') no-repeat center center / cover; min-height: 500px;">
            
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <h1 class="display-3 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">
                            {{ $banner->title }}
                        </h1>
                        
                        <p class="lead mb-4 fs-5 opacity-75">
                            {{ $banner->descrip }} {{-- Lưu ý tên cột trong ảnh của bạn là descrip --}}
                        </p>
                        
                        <div>
                            <a href="show-products" class="btn btn-light rounded-0 px-5 py-3 fw-bold text-uppercase tracking-widest shadow-sm">
                                {{ $banner->button }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        @endforelse
    </section>
     
        
      </main>
    @endsection