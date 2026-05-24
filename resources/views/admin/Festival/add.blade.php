@extends('layout.admin')

@section('body')
<div class="container">
    <div class="row">

        <form action="{{route('admin.festival.store')  }}" method="POST">
            @csrf()
            <div class="mb-3">
                <label for="name" class="form-label font-weight-bold">Tên sự kiện</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Ví dụ: Tết Nguyên Đán, Giáng Sinh..." required>
            </div>

            <div class="mb-3">
                <label for="discount" class="form-label font-weight-bold">Giảm giá (%)</label>
                <input type="number" class="form-control" id="discount" name="discount" placeholder="Nhập phần trăm giảm giá, ví dụ: 10, 20..." required>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label font-weight-bold">Thời gian bắt đầu</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label font-weight-bold">Thời gian kết thúc</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required>
            </div>
            <div class="mb-3"></div>
            <label for="status" class="form-label font-weight-bold">Status (Trạng thái)</label>
            <select name="status" id="status" class="form-control">
                <option value="1">ON</option>
                <option value="0">Off</option>
            </select>
    </div>


    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="{{ route('admin.festival.index') }} " class='btn btn-secondary'>BACK</a>
    </form>

</div>
</div>

@endsection