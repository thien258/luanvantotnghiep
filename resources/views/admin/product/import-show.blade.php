@extends('layout/admin')
@section('body')

<style>
    .import-row-approved {
        background-color: #d1e7dd !important;
        border-left: 4px solid #198754 !important;
    }

    .import-row-skipped {
        background-color: #f8f9fa;
        opacity: 0.65;
    }

    .import-row-skipped td {
        text-decoration: line-through;
        text-decoration-color: rgba(0, 0, 0, 0.25);
    }

    .import-row-skipped td:first-child,
    .import-row-skipped td:nth-child(2) {
        text-decoration: none;
    }
</style>

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
                    <div class="d-flex align-items-center gap-3">
                        {{-- % tăng chung — áp cho tất cả sản phẩm 1 lần --}}
                        <div class="input-group input-group-sm" style="width:180px;">
                            <span class="input-group-text rounded-0 text-muted" style="font-size:0.75rem;">% tăng tất cả</span>
                            <input type="number" id="globalMarkup" value="30" min="0" max="1000" step="1"
                                class="form-control form-control-sm rounded-0 text-center">
                            <span class="input-group-text rounded-0 px-1" style="font-size:0.75rem;">%</span>
                        </div>
                        <button type="button" class="btn btn-dark btn-sm rounded-0" id="applyGlobalMarkup">
                            Áp dụng
                        </button>
                        <label class="small text-muted mb-0">
                            <input type="checkbox" id="selectAll" class="me-1">Chọn tất cả
                        </label>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size:0.85rem;">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="3%">✓</th>
                                <th width="18%" class="text-center">Tên sản phẩm</th>
                                <th width="6%"class="text-center">Ảnh</th>
                                <th width="5%"class="text-center">SL Order</th>
                                <th width="5%"class="text-center">SL thực tế</th>
                                <th width="8%"class="text-center">Giá nhập (₫)</th>
                                <th width="8%"class="text-center">Giá bán (₫)</th>
                                <th width="5%"class="text-center">Volume</th>
                                <th width="8%"class="text-center">Category</th>
                                <th width="8%"class="text-center">Brand</th>
                                <th width="10%"class="text-center">Nồng độ</th>
                                <th width="9%"class="text-center">HSD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productsPreview as $index => $p)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="selected_products[]" value="{{ $index }}" class="product-checkbox">
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
                                    <input type="hidden" name="unit_price[{{ $index }}]" value="{{ $p['unit_price'] ?? 0 }}">
                                    <input type="hidden" name="sl_order[{{ $index }}]" value="{{ $p['sl_order'] ?? 0 }}">
                                </td>
                                <td class="text-center">
                                    @if(!empty($p['image']))
                                    <img src="{{ $p['image'] }}" style="width:36px;height:36px;object-fit:cover;" class="border">
                                    @else
                                    <span class="text-muted" style="font-size:0.7rem;">No img</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- SL Order từ đơn đặt hàng --}}
                                    <input type="text"
                                        value="{{ $p['sl_order'] ?? '' }}" readonly
                                        class="form-control form-control-sm rounded-0 text-center text-muted bg-light px-1">
                                </td>
                                <td>
                                    {{-- SL thực tế nhập kho - chỉ đọc, không cho sửa --}}
                                    <input type="number" name="quantity[{{ $index }}]"
                                        value="{{ $p['quantity'] }}" min="0" readonly
                                        class="form-control form-control-sm rounded-0 text-center fw-bold bg-light text-muted px-1">
                                </td>
                                <td>
                                    {{-- Giá nhập từ NSX - chỉ đọc --}}
                                    <input type="number"
                                        value="{{ $p['unit_price'] ?? $p['price'] }}" min="0" readonly
                                        class="form-control form-control-sm rounded-0 text-center text-muted cost-input px-1"
                                        data-index="{{ $index }}">
                                </td>
                                <td>
                                    {{-- Giá bán = giá nhập × (1 + %/100) - chỉ đọc, tính từ % markup --}}
                                    <input type="number" name="price[{{ $index }}]"
                                        value="{{ round(($p['unit_price'] ?? 0) * 1.3) }}" min="0" readonly
                                        class="form-control form-control-sm rounded-0 text-center bg-light sell-input px-1"
                                        data-index="{{ $index }}">
                                </td>
                                <td class="text-center text-muted" style="font-size:0.78rem;">{{ $p['volume'] }}</td>
                                <td class="text-muted text-center" style="font-size:0.78rem;">{{ $p['category'] }}</td>
                                <td class="text-muted text-center" style="font-size:0.78rem;">{{ $p['brand'] }}</td>
                                <td class="text-muted" style="font-size:0.78rem;">{{ $p['concentration'] }}</td>
                                <td>
                                    <input type="date" name="expiry_date[{{ $index }}]"
                                        value="{{ $p['expiry_date'] ?? '' }}"
                                        class="form-control form-control-sm rounded-0 text-center bg-light text-muted"
                                        style="min-width:130px;" readonly>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="text-muted small">
                        <span id="selectedCount">0</span>/{{ count($productsPreview) }} sản phẩm được chọn
                    </span>
                    <button type="submit" class="btn btn-success rounded-0 fw-bold px-4"
                        onclick="return confirmApprove()">
                        <i class="fa fa-check me-2"></i>Duyệt & Nhập kho
                    </button>
                </div>
            </div>
        </form>

        @elseif($import->status === 'approved')
        @php
        $importedCount = count($approvedItems);
        $totalInFile = count($productsPreview);
        @endphp

        @if($importedCount === 0)
        <div class="alert alert-info rounded-0 small mb-3">
            <i class="fa fa-info-circle me-1"></i>
            File này duyệt trước khi hệ thống lưu chi tiết — hiển thị toàn bộ {{ $totalInFile }} sản phẩm trong file.
        </div>
        @else
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="text-muted small">
                <i class="fa fa-check-circle text-success me-1"></i>
                Đã nhập kho <strong class="text-success">{{ $importedCount }}</strong>/{{ $totalInFile }} sản phẩm trong file
            </div>
            <label class="small text-muted mb-0">
                <input type="checkbox" id="showImportedOnly" class="me-1">
                Chỉ hiển thị SP đã nhập
            </label>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle" style="font-size:0.85rem;">
                <thead class="table-dark">
                    <tr>
                        <th width="12%">Trạng thái</th>
                        <th>Tên sản phẩm</th>
                        <th class="text-center">SL</th>
                        <th class="text-center">Giá bán</th>
                        <th>Volume</th>
                        <th>Category</th>
                        <th>Brand</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productsPreview as $index => $p)
                    @php
                    $key = mb_strtolower(trim($p['title']));
                    $imported = $importedCount > 0 && $approvedByTitle->has($key);
                    $data = $imported ? $approvedByTitle->get($key) : $p;
                    @endphp
                    <tr class="import-result-row {{ $importedCount === 0 ? '' : ($imported ? 'import-row-approved' : 'import-row-skipped') }}"
                        data-imported="{{ $imported ? '1' : '0' }}">
                        <td class="text-center">
                            @if($importedCount === 0)
                            <span class="text-muted">—</span>
                            @elseif($imported)
                            <span class="badge bg-success rounded-0 px-2 py-1">
                                <i class="fa fa-check me-1"></i>Đã nhập
                            </span>
                            @else
                            <span class="badge bg-secondary rounded-0 px-2 py-1">
                                <i class="fa fa-minus me-1"></i>Bỏ qua
                            </span>
                            @endif
                        </td>
                        <td class="fw-bold {{ $imported ? 'text-success' : 'text-muted' }}">{{ $p['title'] }}</td>
                        <td class="text-center {{ $imported ? 'fw-bold' : 'text-muted' }}">{{ $data['quantity'] ?? 0 }}</td>
                        <td class="text-center {{ $imported ? 'text-danger fw-bold' : 'text-muted' }}">
                            {{ number_format($data['price'] ?? $data['unit_price'] ?? 0) }}đ
                        </td>
                        <td class="{{ $imported ? '' : 'text-muted' }}">{{ $p['volume'] }}</td>
                        <td class="{{ $imported ? '' : 'text-muted' }}">{{ $p['category'] }}</td>
                        <td class="{{ $imported ? '' : 'text-muted' }}">{{ $p['brand'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @else
        {{-- File bị từ chối --}}
        <div class="alert alert-danger rounded-0 small mb-3">File này đã bị từ chối, không nhập kho.</div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle" style="font-size:0.85rem;">
                <thead class="table-dark">
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th class="text-center">SL</th>
                        <th class="text-center">Giá nhập</th>
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
                        <td class="text-center">{{ number_format($p['unit_price'] ?? 0) }}đ</td>
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
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const total = document.querySelectorAll('.product-checkbox').length;
        const selected = document.querySelectorAll('.product-checkbox:checked').length;
        const el = document.getElementById('selectedCount');
        if (el) el.textContent = selected;
    }

    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    updateSelectedCount();

    // Tính giá bán = giá nhập × (1 + % / 100)
    function calcSellPrice(index, markup) {
        const cost = parseFloat(document.querySelector(`.cost-input[data-index="${index}"]`)?.value) || 0;
        const sellEl = document.querySelector(`.sell-input[data-index="${index}"]`);
        if (sellEl) sellEl.value = Math.round(cost * (1 + markup / 100));
    }

    // Nút "Áp dụng" — áp % chung cho tất cả dòng 1 lần
    document.getElementById('applyGlobalMarkup')?.addEventListener('click', function() {
        const globalPct = parseFloat(document.getElementById('globalMarkup')?.value) || 0;
        document.querySelectorAll('.cost-input').forEach(el => {
            calcSellPrice(el.dataset.index, globalPct);
        });
    });

    function confirmApprove() {
        const selected = document.querySelectorAll('.product-checkbox:checked').length;
        if (selected === 0) {
            alert('Vui lòng chọn ít nhất 1 sản phẩm để duyệt.');
            return false;
        }
        return confirm(`Xác nhận duyệt và nhập kho ${selected} sản phẩm đã chọn?`);
    }

    document.getElementById('showImportedOnly')?.addEventListener('change', function() {
        document.querySelectorAll('.import-result-row').forEach(row => {
            if (this.checked) {
                row.style.display = row.dataset.imported === '1' ? '' : 'none';
            } else {
                row.style.display = '';
            }
        });
    });
</script>
@endsection