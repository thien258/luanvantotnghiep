Nghiệp vụ hệ thống bán hàng và vận hành kho mà bạn đã gửi được tóm tắt lại trọn vẹn theo đúng luồng thực tế dưới đây:

---

### GIAI ĐOẠN 1: KHÁCH HÀNG MUA HÀNG (TRANG CHECKOUT)

* **Điều kiện tiên quyết:** Khách hàng **bắt buộc phải đăng nhập** tài khoản thành viên thì mới tiến hành thanh toán được.
* **Thao tác trong trang thanh toán:** * Khách hàng có quyền chọn một trong các địa chỉ đã lưu từ trước (Sổ địa chỉ giống Shopee), hoặc có thể bấm **Thêm địa chỉ mới** trực tiếp tại đây.
* Khách hàng lựa chọn hình thức thanh toán mong muốn (Ví dụ: Thẻ tín dụng, Chuyển khoản, Ví điện tử...).


* **Sau khi bấm Thanh toán thành công:** Hệ thống sẽ tự động chuyển hướng khách hàng quay trở lại **Trang chính (Homepage)** và có khu vực **Lịch sử đơn hàng** để khách hàng có thể vào xem lại trạng thái đơn của mình bất cứ lúc nào.

---

### GIAI ĐOẠN 2: PHÍA ADMIN & NHÂN VIÊN KHO (XỬ LÝ XUẤT KHO)

* **Tiếp nhận đơn:** Sau khi khách đặt hàng thành công, một giao diện "Đã thanh toán / Đơn hàng mới" sẽ hiển thị ở trang quản trị (Admin). Nhân viên sẽ nhìn thấy đơn hàng này.
* **Quy trình xuất kho:** 1.  Nhân viên tiến hành lấy sản phẩm thực tế theo đúng đơn hàng.
2.  Hệ thống thực hiện lệnh **Xuất kho** và tự động **Trừ đi số lượng tồn kho** của sản phẩm đó.
3.  Hệ thống sinh ra một mã định danh duy nhất cho đơn hàng và **In mã QR** chứa thông tin mã này để dán lên thùng hàng.
4.  Bàn giao gói hàng cho đơn vị vận chuyển.

---

### GIAI ĐOẠN 3: GIAO HÀNG & XỬ LÝ SỰ CỐ HOÀN HÀNG (RESTOCK)

Khi shipper giao hàng tới tay khách, sẽ xảy ra 2 trường hợp:

* **Trường hợp 1: Giao hàng THÀNH CÔNG**
* Khách hàng nhận hàng, dùng điện thoại **Quét mã QR** dán trên thùng hàng.
* Mã QR dẫn khách về trang web của bạn, khách bấm vào nút **"Xác nhận đã giao thành công"**. Đơn hàng hoàn tất.


* **Trường hợp 2: Giao hàng THẤT BẠI (Khách không nhận / Hoàn hàng)**
* Đơn hàng bị trả ngược về kho. Nhân viên kho nhận lại và vào giao diện kiểm tra tình trạng hàng hoàn.
* **Nếu hàng còn nguyên vẹn (Khách boom đơn lý do khách quan):** Nhân viên chọn lý do tương ứng, hệ thống sẽ **Cộng ngược lại số lượng vào Tồn kho** để tiếp tục bán.
* **Nếu hàng bị lỗi/hỏng (Móp méo do vận chuyển, lỗi nhà sản xuất...):** Nhân viên chọn lý do lỗi, hệ thống sẽ **Không cộng vào tồn kho bán**, mà chuyển sản phẩm đó sang một khu vực riêng gọi là **Danh sách sản phẩm lỗi**.



---
Toàn bộ luồng dữ liệu (Database) 2 bảng `orders` và `order_details` mà chúng ta vừa làm trầy trật lúc nãy chính là để phục vụ cho các bước lưu thông tin đơn hàng này đấy!

Bây giờ bạn muốn chúng ta bắt đầu code chức năng nào đầu tiên trong luồng này? (Dựng form giao diện Checkout, hay viết Controller lưu đơn?)