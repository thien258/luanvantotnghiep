@extends('layout/admin')
@section('body')

<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">

        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <a href="{{ route('admin.warehouse.imports') }}" class="btn btn-sm btn-secondary rounded-0">
                <i class="fa fa-arrow-left me-1"></i> Quay lại
            </a>
            <div class="text-center">
                <h5 class="m-0 fw-bold text-dark">Duyệt file nhập kho</h5>
                <small class="text-muted">{{ $import->original_name }} — {{ $import->supplier }}</small>
            </div>
            <span class="badge px-3 py-2
                {{ $import->status === 'pending' ? 'bg-warning text-dark' : ($import->status === 'approved' ? 'bg-success' : 'bg-danger') }}">
                {{ $import->status === 'pending' ? 'Chờ duyệt' : ($import->status === 'approved' ? 'Đã duyệt' : 'Từ chối') }}
            </span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
        @endif

        @if($import->status === 'pending')
        <form action="{{ route('admin.warehouse.imports.approve', $import->id) }}" method="POST">
            @csrf
            <input type="hidden" name="supplier" value="{{ $import->supplier }}">
            <input type="hidden" name="main_note" value="{{ $import->note }}">

            <div class="card rounded-0 border bg-white">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-uppercase text-muted small">
                        <i class="fa fa-list me-1"></i>Danh sách sản phẩm trong file — chỉnh sửa rồi duyệt
                    </span>
                    <div>
                        <label class="small text-muted me-2">
                            <input type="checkbox" id="selectAll" class="me-1">Chọn tất cả
                        </label>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size:0.85rem;">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="4%">✓</th>
                                <th>Tên sản phẩm</th>
                                <th width="8%">Ảnh</th>
                                <th width="10%">SL nhập</th>
                                <th width="12%">Giá bán</th>
                                <th width="8%">Volume</th>
                                <th width="10%">Category</th>
                                <th width="10%">Brand</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productsPreview as $index => $p)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="selected_products[]" value="{{ $index }}" class="product-checkbox" checked>
                                </td>
                                <td>
                                    <input type="text" name="product_name[{{ $index }}]"
                                           value="{{ $p['title'] }}"
                                           class="form-control form-control-sm rounded-0 fw-bold">
                                    <input type="hidden" name="image[{{ $index }}]" value="{{ $p['image'] }}">
                                    <input type="hidden" name="decription[{{ $index }}]" value="{{ $p['decription'] }}">
                                    <input type="hidden" name="volume[{{ $index }}]" value="{{ $p['volume'] }}">
                                    <input type="hidden" name="category[{{ $index }}]" value="{{ $p['category'] }}">
                                    <input type="hidden" name="brand[{{ $index }}]" value="{{ $p['brand'] }}">
                                    <input type="hidden" name="concentration[{{ $index }}]" value="{{ $p['concentration'] }}">
                                    <input type="hidden" name="line_note[{{ $index }}]" value="{{ $p['note'] }}">
                                </td>
                                <td class="text-center">
                                    @if(!empty($p['image']))
                                        <img src="{{ $p['image'] }}" style="width:36px;height:36px;object-fit:cover;" class="border">
                                    @else
                                        <span class="text-muted" style="font-size:0.7rem;">No img</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="number" name="quantity[{{ $index }}]"
                                           value="{{ $p['quantity'] }}" min="0"
                                           class="form-control form-control-sm rounded-0 text-center fw-bold">
                                </td>
                                <td>
                                    <input type="number" name="price[{{ $index }}]"
                                           value="{{ $p['price'] }}" min="0"
                                           class="form-control form-control-sm rounded-0 text-center">
                                </td>
                                <td class="text-center text-muted" style="font-size:0.78rem;">{{ $p['volume'] }}</td>
                                <td class="text-muted" style="font-size:0.78rem;">{{ $p['category'] }}</td>
                                <td class="text-muted" style="font-size:0.78rem;">{{ $p['brand'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="text-muted small">{{ count($productsPreview) }} sản phẩm trong file</span>
                    <button type="submit" class="btn btn-success rounded-0 fw-bold px-4"
                            onclick="return confirm('Xác nhận duyệt và nhập kho?')">
                        <i class="fa fa-check me-2"></i>Duyệt & Nhập kho
                    </button>
                </div>
            </div>
        </form>

        @else
        {{-- Đã xử lý rồi, chỉ hiển thị --}}
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle" style="font-size:0.85rem;">
                <thead class="table-dark">
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th class="text-center">SL</th>
                        <th class="text-center">Giá bán</th>
                        <th>Volume</th>
                        <th>Category</th>
                        <th>Brand</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productsPreview as $p)
                    <tr>
                        <td class="fw-bold">{{ $p['title'] }}</td>
                        <td class="text-center">{{ $p['quantity'] }}</td>
                        <td class="text-center">{{ number_format($p['price']) }}đ</td>
                        <td>{{ $p['volume'] }}</td>
                        <td>{{ $p['category'] }}</td>
                        <td>{{ $p['brand'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>

@endsection

@section('script')
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
