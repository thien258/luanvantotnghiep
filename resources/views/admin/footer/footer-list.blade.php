@extends('layout.admin')

@section('body')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chỉnh sửa Footer</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.footer.update', $footer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Header</label>
                            <input type="text" name="header" class="form-control"
                                   value="{{ $footer->header }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Text Header</label>
                            <input type="text" name="textheader" class="form-control"
                                   value="{{ $footer->textheader }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Header 2</label>
                            <input type="text" name="header2" class="form-control"
                                   value="{{ $footer->header2 }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control"
                                   value="{{ $footer->address }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ $footer->phone }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ $footer->email }}">
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Cập nhật
                            </button>
        
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
