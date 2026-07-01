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
                        <select name="supplier" id="supplierSelect" class="form-control rounded-0" required>
                            <option value="">— Chọn nhà cung cấp —</option>
                            @foreach($manufacturers as $m)
                                <option value="{{ $m->name }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
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
// Khi warehouse chọn file CSV xuất từ đơn đặt hàng,
// tự động đọc dòng đầu "# supplier, TênNSX" và chọn NSX trong dropdown
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('csvFileInput');
    const supplierSelect = document.getElementById('supplierSelect');

    if (!fileInput || !supplierSelect) return;

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file || !file.name.endsWith('.csv')) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            const firstLine = e.target.result.split('\n')[0];
            // Dòng metadata có dạng: # supplier,"Tên NSX"
            if (firstLine.startsWith('# supplier')) {
                // Lấy giá trị sau dấu phẩy, bỏ dấu nháy và khoảng trắng
                const parts = firstLine.split(',');
                if (parts.length >= 2) {
                    const supplierName = parts[1].trim().replace(/^"|"$/g, '');
                    // Tìm option khớp tên và chọn
                    for (let opt of supplierSelect.options) {
                        if (opt.value.trim() === supplierName) {
                            opt.selected = true;
                            break;
                        }
                    }
                }
            }
        };
        reader.readAsText(file, 'UTF-8');
    });
});
</script>
@endsection
