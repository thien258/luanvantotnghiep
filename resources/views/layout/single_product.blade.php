@extends('layout/home')
@section('body')

<section class="blog-banner-area py-5 bg-light border-bottom">
	<div class="container">
		<div class="text-center py-4">
			<h1 class="display-5 fw-bold text-dark">Exclusive Fragrance</h1>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb justify-content-center mb-0">
					<li class="breadcrumb-item"><a href="{{ route('welcome') }}" class="text-decoration-none text-muted">Home</a></li>
					<li class="breadcrumb-item active text-dark" aria-current="page">Shop Single</li>
				</ol>
			</nav>
		</div>
	</div>
</section>

@forelse($products as $product)
<div class="product_image_area py-5">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-6 mb-4 mb-lg-0">
				<div class="col-lg-6 mb-4 mb-lg-0">
					<div class="d-flex justify-content-center align-items-center bg-light">
						<img class="img-fluid w-100" src="{{$product->image}}" alt="{{$product->title}}" style="object-fit: cover; max-height: 500px;">
					</div>
				</div>
			</div>

			<div class="col-lg-5 offset-lg-1">
				<div class="s_product_text">
					<h3 class="display-6 fw-bold mb-2">{{$product->title}}</h3>
					<h2 class="text-muted fw-light mb-4">{{$product->price}} VNĐ</h2>

					<p class="text-secondary lh-lg mb-4">{{$product->decription}}</p>

					<div class="mb-4 py-3 border-top border-bottom">
						<div class="text-uppercase small tracking-widest text-muted mb-2">
							Concentration: <span class="text-dark fw-bold ms-2">{{$product->concentration->concentration}}</span>
						</div>
						<div class="text-uppercase small tracking-widest text-muted">
							Volume: <span class="text-dark fw-bold ms-2">{{$product->volume->name}}</span>
						</div>
					</div>

					<div class="d-flex align-items-center gap-3">
						<form action="{{ route('loves.store') }}" method="POST" class="m-0">
							@csrf
							<input type="hidden" name="idProduct" value="{{ $product->id }}">
							<button type="submit" class="btn btn-outline-danger btn-lg rounded-pill px-4">
								<i class="fa-solid fa-heart"></i>
							</button>
						</form>
						<!-- <button class="btn btn-dark btn-lg rounded-pill px-5 flex-grow-1 fw-bold">ADD TO CART</button> -->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="bg-light py-5">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 mb-5 mb-lg-0">
				<h4 class="fw-bold mb-4 border-bottom pb-2">Customer Feedback</h4>

				@foreach($product->comment as $c)
				<div class="bg-white p-3 mb-3 shadow-sm border-start border-dark border-4">
					<div class="d-flex justify-content-between align-items-start">
						<div>
							<p class="fw-bold mb-1 text-uppercase small">{{ $c->name }}</p>
							<p class="text-muted mb-0 small">"{{ $c->chat }}"</p>
						</div>

					</div>
				</div>
				@endforeach
			</div>

			<div class="col-lg-5 offset-lg-1">
				<div class="p-4 bg-white shadow-sm border">
					<h4 class="fw-bold mb-4">Leave a Review</h4>
					<form action="{{ route('comments.store') }}" method="POST">
						@csrf
						<input type="hidden" name="idProduct" value="{{ $product->id }}">

						<div class="mb-3">
							<label class="form-label small fw-bold text-uppercase">Your Name</label>
							<input type="text" class="form-control rounded-0" name="name" placeholder="Enter your name" required>
						</div>

						<div class="mb-3">
							<label class="form-label small fw-bold text-uppercase">Your Experience</label>
							<textarea name="chat" class="form-control rounded-0" rows="4" placeholder="Tell us what you think..." required></textarea>
						</div>

						<button type="submit" class="btn btn-dark w-100 rounded-0 py-3 text-uppercase fw-bold shadow-none">Submit Now</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@empty
<div class="container py-5 text-center text-muted">
	<h3>Sản phẩm đang được cập nhật...</h3>
</div>
@endforelse



@endsection