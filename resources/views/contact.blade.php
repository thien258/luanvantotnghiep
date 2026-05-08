@extends('layout/home')
@section('body')
<!-- ================ start banner area ================= -->

	<!-- ================ end banner area ================= -->

	<!-- ================ contact section start ================= -->
  <section class="section-margin--small">
    <div class="container">
 


      <div class="row">
        <div class="col-md-4 col-lg-3 mb-4 mb-md-0">
       
        </div>
        <div class="col-md-8 col-lg-9">
       <form action="{{ route('contact.store') }}" method="POST">
          @csrf()
  <div class="mb-3">
    <label for="exampleInputEmail1" class="form-label">Name</label>
    <input type="name" name="name" id="name" class="form-control"  aria-describedby="emailHelp" required>
    <label for="exampleInputEmail1" class="form-label">Email address</label>
    <input type="email" name="email" class="form-control" id="email" required>
   
  </div>
  <div class="mb-3">
    <label for="exampleInputPassword1" class="form-label">message</label>
    <textarea type="text" name="message" class="form-control" id="message" required></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Submit</button>
</form>
        </div>
      </div>
    </div>
  </section>
	<!-- ================ contact section end ================= -->
  
  
@endsection