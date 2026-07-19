@extends('layout.admin')

@section('body')
<div class="container">
    <div class="row">

        <form action="{{route('admin.brand.store')}}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Brand <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name" value="{{ old('name') }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="text" class="form-control" id="image" name="image" value="{{ old('image') }}">
            </div>
            <div class="mb-3">
                <label for="descrip" class="form-label">Description <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('descrip') is-invalid @enderror"
                       id="descrip" name="descrip" value="{{ old('descrip') }}">
                @error('descrip')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>ON</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Off</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>


            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{ route('admin.brand.index') }} " class='btn btn-secondary'>BACK</a>
        </form>

    </div>
</div>

@endsection