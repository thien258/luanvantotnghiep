@extends('layout/admin')
@section('body')

<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h5 class="font-weight-bold text-dark mb-0">
        <i class="fa-solid fa-file-invoice mr-2 text-muted"></i>Tạo báo giá NSX mới
    </h5>
    <a href="{{ route('admin.supplier-offers.index') }}" class="btn btn-outline-secondary btn-sm rounded-0">
        <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger rounded-0">
        <ul class="mb-0 small">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.supplier-offers.store') }}" method="POST" id="offerForm">
@csrf

<div class="card shadow-none border rounded-0 mb-3">
    <div class="card-header bg-white py-2 border-bottom">
        <span class="small font-weight-bold text-uppercase text-muted">Thông tin báo giá</span>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="small font-weight-bold">Nhà sản xuất <span class="text-danger">*</span></label>
                <select name="manufacturer_id" class="form-control form-control-sm rounded-0" id="manufacturerSelect" required>
                    <option value="">— Chọn NSX —</option>
                    @foreach($manufacturers as $m)
                        <option value="{{ $m->id }}" {{ old('manufacturer_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label class="small font-weight-bold">Ghi chú</label>
                <input type="text" name="note" class="form-control form-control-sm rounded-0"
                       placeholder="Ghi chú thêm về báo giá..." value="{{ old('note') }}">
            </div>
        </div>
    </div>
</div>

<div class="card shadow-none border rounded-0 mb-3">
    <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
        <span class="small font-weight-bold text-uppercase text-muted">Danh sách sản phẩm chào giá</span>
        <button type="button" class="btn btn-dark btn-sm rounded-0" id="addRowBtn">
            <i class="fa-solid fa-plus mr-1"></i> Thêm dòng
        </button>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 small" id="itemsTable">
            <thead class="table-light">
                <tr>
                    <th class="pl-3 py-2" style="width:35%">Tên sản phẩm <span class="text-danger">*</span></th>
                    <th class="py-2" style="width:25%">Sản phẩm trong hệ thống</th>
                    <th class="py-2" style="width:20%">Giá chào (₫) <span class="text-danger">*</span></th>
                    <th class="py-2" style="width:15%">Ghi chú</th>
                    <th class="py-2" style="width:5%"></th>
                </tr>
            </thead>
            <tbody id="itemsBody">
                {{-- Dòng mẫu đầu tiên --}}
                <tr class="item-row">
                    <td class="pl-3 py-2">
                        <input type="text" name="items[0][product_name]"
                               class="form-control form-control-sm rounded-0"
                               placeholder="Tên SP NSX chào" required>
                    </td>
                    <td class="py-2">
                        <select name="items[0][product_id]" class="form-control form-control-sm rounded-0">
                            <option value="">— Chưa có trong hệ thống —</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="py-2">
                        <input type="number" name="items[0][unit_price]"
                               class="form-control form-control-sm rounded-0"
                               placeholder="VD: 2000000" min="0" required>
                    </td>
                    <td class="py-2">
                        <input type="text" name="items[0][note]"
                               class="form-control form-control-sm rounded-0" placeholder="Ghi chú">
                    </td>
                    <td class="py-2 text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-0 remove-row"
                                title="Xóa dòng">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="text-right">
    <button type="submit" class="btn btn-dark rounded-0 px-4">
        <i class="fa-solid fa-paper-plane mr-1"></i> Gửi báo giá
    </button>
</div>

</form>

@endsection

@section('script')
<script>
    let rowIndex = 1;
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'title' => $p->title]));

    document.getElementById('addRowBtn').addEventListener('click', function () {
        const tbody = document.getElementById('itemsBody');
        const i = rowIndex++;

        const optionsHtml = '<option value="">— Chưa có trong hệ thống —</option>'
            + products.map(p => `<option value="${p.id}">${p.title}</option>`).join('');

        const row = document.createElement('tr');
        row.classList.add('item-row');
        row.innerHTML = `
            <td class="pl-3 py-2">
                <input type="text" name="items[${i}][product_name]"
                       class="form-control form-control-sm rounded-0"
                       placeholder="Tên SP NSX chào" required>
            </td>
            <td class="py-2">
                <select name="items[${i}][product_id]" class="form-control form-control-sm rounded-0">
                    ${optionsHtml}
                </select>
            </td>
            <td class="py-2">
                <input type="number" name="items[${i}][unit_price]"
                       class="form-control form-control-sm rounded-0"
                       placeholder="VD: 2000000" min="0" required>
            </td>
            <td class="py-2">
                <input type="text" name="items[${i}][note]"
                       class="form-control form-control-sm rounded-0" placeholder="Ghi chú">
            </td>
            <td class="py-2 text-center">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-0 remove-row">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>`;
        tbody.appendChild(row);

        row.querySelector('.remove-row').addEventListener('click', function () {
            row.remove();
        });
    });

    // Gán sự kiện xóa cho dòng đầu tiên
    document.querySelectorAll('.remove-row').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('tr').remove();
        });
    });
</script>
@endsection
