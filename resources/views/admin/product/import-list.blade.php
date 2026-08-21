@extends('layout/admin')
@section('body')

<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">

        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <h5 class="m-0 fw-bold text-dark">
                <i class="fa fa-inbox me-2"></i>Nhập Kho — Danh sách file chờ duyệt
            </h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-0 py-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-0 py-2">{{ session('error') }}</div>
        @endif
        @if($errors->has('excel_file'))
        <div class="alert alert-danger rounded-0 py-2 small">
            <strong><i class="fa fa-exclamation-triangle me-1"></i>File không hợp lệ:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->get('excel_file') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Form upload file: chỉ warehouse --}}
        @if(auth()->user()->role === 'warehouse')
        <div class="card rounded-0 border mb-4">
            <div class="card-header bg-white fw-bold small text-uppercase py-2">
                <i class="fa fa-upload me-1"></i> Upload file nhập kho mới
            </div>
            <div class="card-body">
                <form action="{{ route('admin.warehouse.imports.upload') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <input type="text" name="supplier" id="supplierDisplay"
                               class="form-control rounded-0 bg-light"
                               placeholder="Nhà cung cấp (tự động từ file)"
                               readonly>
                    </div>
                    <div class="col-md-4">
                        <input type="file" name="excel_file" id="csvFileInput" class="form-control rounded-0" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="note" class="form-control rounded-0" placeholder="Ghi chú (tùy chọn)">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark rounded-0 w-100">
                            <i class="fa fa-upload me-1"></i> Gửi file
                        </button>
                    </div>
                    <div class="col-12">
                        <div id="csvValidationErrors" class="alert alert-danger rounded-0 small py-2" style="display:none;"></div>
                    </div>
                   
                </form>
            </div>
        </div>
        @endif

        {{-- Danh sách file --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white mb-0" style="font-size:0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th>File</th>
                        <th>Nhà cung cấp</th>
                        <th>Người upload</th>
                        <th>Ghi chú</th>
                        <th class="text-center">Trạng thái</th>
                        <th>Ngày upload</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                    <tr>
                        <td class="fw-bold">{{ $import->original_name }}</td>
                        <td>{{ $import->supplier ?? '—' }}</td>
                        <td>{{ $import->uploader?->name ?? '—' }}</td>
                        <td class="text-muted" style="font-size:0.8rem;">{{ $import->note ?? '—' }}</td>
                        <td class="text-center">
                            @if($import->status === 'pending')
                                <span class="badge bg-warning text-dark">Chờ duyệt</span>
                            @elseif($import->status === 'approved')
                                <span class="badge bg-success">Đã duyệt</span>
                            @else
                                <span class="badge bg-danger">Từ chối</span>
                            @endif
                        </td>
                        <td>{{ $import->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.warehouse.imports.show', $import->id) }}"
                               class="btn btn-sm btn-info text-white rounded-0">
                                <i class="fa fa-eye me-1"></i>Xem & Duyệt
                            </a>
                            @if($import->status === 'pending')
                            @if(auth()->user()->isAdmin())
                            <form action="{{ route('admin.warehouse.imports.reject', $import->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-0"
                                        onclick="return confirm('Từ chối file này?')">
                                    Từ chối
                                </button>
                            </form>
                            @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Chưa có file nhập kho nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $imports->links() }}
        </div>

    </div>
</div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('csvFileInput');
    const supplierDisplay = document.getElementById('supplierDisplay');
    const validationBox = document.getElementById('csvValidationErrors');
    const submitBtn = document.querySelector('button[type="submit"]');

    if (!fileInput) return;

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        supplierDisplay.value = '';
        validationBox.innerHTML = '';
        validationBox.style.display = 'none';
        if (submitBtn) submitBtn.disabled = false;

        if (!file) return;

        // Chỉ validate CSV phía client
        if (!file.name.endsWith('.csv')) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            const lines = e.target.result.split('\n');
            const errors = [];

            // Đọc NSX từ dòng metadata
            let dataStartIndex = 0;
            if (lines[0] && lines[0].includes('# supplier')) {
                const commaIdx = lines[0].indexOf(',');
                if (commaIdx !== -1) {
                    supplierDisplay.value = lines[0].substring(commaIdx + 1)
                        .trim().replace(/^"|"$/g, '').replace(/\r/g, '').replace(/,.*$/, '');
                }
                dataStartIndex = 1; // bỏ qua dòng metadata
            }

            // Bỏ qua dòng header
            dataStartIndex += 1;

            // Validate từng dòng dữ liệu
            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;

            for (let i = dataStartIndex; i < lines.length; i++) {
                const line = lines[i].trim();
                if (!line) continue;

                // Parse CSV đơn giản (split theo dấu phẩy, bỏ qua phần trong ngoặc kép)
                const cols = [];
                let inQuote = false, current = '';
                for (let c of line) {
                    if (c === '"') { inQuote = !inQuote; }
                    else if (c === ',' && !inQuote) { cols.push(current); current = ''; }
                    else { current += c; }
                }
                cols.push(current);

                const lineNum = i + 1;
                const title     = cols[0]?.trim();
                const unitPrice = cols[3]?.trim();
                const slOrder   = cols[4]?.trim();
                const quantity  = cols[5]?.trim();
                const volume    = cols[6]?.trim();
                const category  = cols[7]?.trim();
                const brand     = cols[8]?.trim();
                const conc      = cols[9]?.trim();
                const expiry    = cols[10]?.trim().replace(/\r/g, '');

                if (!title)     errors.push(`Tên sản phẩm (title) không được để trống.`);
                if (!unitPrice || isNaN(unitPrice) || parseFloat(unitPrice) < 0)
                    errors.push(`Giá nhập (unit_price) không được để trống và phải là số không âm.`);
                if (!slOrder || isNaN(slOrder) || parseInt(slOrder) < 0)
                    errors.push(`SL order (sl_order) không được để trống và phải là số nguyên không âm.`);
                if (!quantity || isNaN(quantity) || parseInt(quantity) < 0)
                    errors.push(`Số lượng (quantity) không được để trống và phải là số nguyên không âm.`);
                if (!volume)    errors.push(`Dung tích (volume) không được để trống.`);
                if (!category)  errors.push(`Danh mục (category) không được để trống.`);
                if (!brand)     errors.push(`Thương hiệu (brand) không được để trống.`);
                if (!conc)      errors.push(`Nồng độ (concentration) không được để trống.`);
                if (!expiry)
                    errors.push(`HSD không được để trống (định dạng: YYYY-MM-DD, ví dụ: 2026-09-15).`);
                else if (!dateRegex.test(expiry))
                    errors.push(`HSD "${expiry}" sai định dạng, phải là YYYY-MM-DD (ví dụ: 2026-09-15).`);

                if (errors.length >= 10) { errors.push('... (còn nhiều lỗi, vui lòng kiểm tra lại file)'); break; }
            }

            if (errors.length > 0) {
                const uniqueErrors = [...new Set(errors)];
                let html = '<strong><i class="fa fa-exclamation-triangle me-1"></i>File có lỗi, vui lòng kiểm tra:</strong><ul class="mb-0 mt-1">';
                uniqueErrors.forEach(e => html += `<li>${e}</li>`);
                html += '</ul>';
                validationBox.innerHTML = html;
                validationBox.style.display = 'block';
                if (submitBtn) submitBtn.disabled = true;
            }
        };
        reader.readAsText(file, 'UTF-8');
    });
});
</script>
@endsection
