<?php

namespace App\Http\Controllers\admin;  // Namespace admin — tách riêng với controller frontend

use App\Http\Controllers\Controller;   // Controller base của Laravel — cung cấp các helper cơ bản
use App\Models\RootActivityLog;        // Model để query bảng root_activity_logs
use Illuminate\Http\Request;           // Đối tượng chứa toàn bộ dữ liệu từ HTTP request (query string, form, ...)

/**
 * ActivityLogController — Xem lịch sử thao tác của tài khoản root.
 *
 * Quyền truy cập: chỉ director và root (kiểm tra bằng middleware 'role:director,root' trong routes/web.php)
 * URL: GET /admin/activity-log
 */
class ActivityLogController extends Controller
{
    /**
     * index() — Hiển thị danh sách log, hỗ trợ filter theo ngày và tìm kiếm.
     *
     * @param  Request $request  Chứa các query string: ?date=2026-07-10&search=Nguyễn
     */
    public function index(Request $request)
    {
        // Bắt đầu query builder — chưa chạy SQL, chỉ tạo object để chain thêm điều kiện
        // orderBy('created_at', 'desc') → log mới nhất hiển thị trên cùng
        $query = RootActivityLog::orderBy('created_at', 'desc');

        // ── Filter 1: Lọc theo ngày ──────────────────────────────────────────
        // $request->filled('date') → trả true nếu query string 'date' tồn tại và không rỗng
        // Tránh dùng isset() vì filled() xử lý cả trường hợp chuỗi rỗng ""
        if ($request->filled('date')) {
            // whereDate so sánh phần DATE của cột timestamp (bỏ qua giờ/phút/giây)
            // → tìm đúng ngày bất kể giờ nào trong ngày
            $query->whereDate('created_at', $request->input('date'));
        }

        // ── Filter 2: Tìm kiếm theo tên hoặc email ───────────────────────────
        if ($request->filled('search')) {
            $search = $request->input('search');  // Lấy giá trị search từ query string

            // Dùng closure trong where() để nhóm điều kiện thành: WHERE (name LIKE ... OR email LIKE ...)
            // Nếu không dùng closure, Laravel sẽ tạo: WHERE name LIKE ... OR email LIKE ...
            // → dẫn đến kết quả sai khi kết hợp với filter ngày ở trên
            $query->where(function ($q) use ($search) {
                $q->where('user_name',  'like', "%{$search}%")   // tìm trong tên (chứa search)
                  ->orWhere('user_email', 'like', "%{$search}%"); // hoặc tìm trong email
            });
        }

        // Thực thi query, lấy 50 dòng / trang
        // withQueryString() → giữ lại các query string (?date=...&search=...) khi chuyển trang
        // Không có withQueryString → khi bấm trang 2, filter bị mất
        $logs = $query->paginate(50)->withQueryString();

        // Trả về view với biến $logs
        // compact('logs') === ['logs' => $logs]
        return view('admin.activity-log.index', compact('logs'));
    }
}
