@extends('layout.admin')

@section('body')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chỉnh sửa Title</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.title.update', $title->idTitle) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">image</label>
                            <input type="text" name="image" class="form-control"
                                   value="{{ $title->image }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">title</label>
                            <input type="text" name="title" class="form-control"
                                   value="{{ $title->title }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrip</label>
                            <input type="text" name="descrip" class="form-control"
                                   value="{{ $title->descrip }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Button</label>
                            <input type="text" name="button" class="form-control"
                                   value="{{ $title->button }}">
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
