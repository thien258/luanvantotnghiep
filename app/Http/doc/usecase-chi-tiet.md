# 3.3 Đặc tả Use Case Chi Tiết

---

## 3.3.1 Use case Quản lý Tài khoản

| | |
|---|---|
| **Tên Use case** | Quản lý Tài khoản |
| **Actor** | Khách hàng |
| **Mô tả** | Khách hàng đăng nhập vào hệ thống bằng email và mật khẩu tài khoản được tạo. Khách hàng đăng nhập vào hệ thống và chọn quản lý tài khoản. Khách hàng có thể xem và cập nhật tài khoản của mình. Khách hàng có thể đặt lại mật khẩu khi đã quên mật khẩu. |
| **Pre-conditions** | Actor chưa đăng nhập vào hệ thống. |
| **Post-conditions** | - Khách hàng đăng nhập vào hệ thống thành công và có thể thực hiện các chức năng của khách hàng.<br>- Extend Use Case Đăng ký<br>- Extend Use Case Đăng nhập<br>- Extend Use Case Cập nhật profile<br>- Extend Use Case Đăng xuất<br>- Extend Use Case Quên mật khẩu |
| **Luồng sự kiện chính** | Actor chọn chức năng Quản lý Tài Khoản. Hệ thống hiển thị màn hình Quản lý Tài Khoản. |
| **Luồng sự kiện phụ** | Actor nhấn đăng xuất, hệ thống trở về trang chủ. |
| **\<\<Extend\>\> Đăng ký** | 1. Khách hàng chọn đăng ký tài khoản.<br>2. Hệ thống hiển thị trang đăng ký.<br>3. Khách hàng nhập thông tin đăng ký.<br>4. Khách hàng nhấn nút đăng ký.<br>5. Hệ thống kiểm tra thông tin nhập vào hợp lệ.<br>6. Hệ thống kiểm tra email đã được đăng ký chưa.<br>7. Lưu tài khoản vào CSDL.<br>Rẽ nhánh 1 — 5.1: Thông tin không hợp lệ, quay lại bước 3.<br>Rẽ nhánh 2 — 6.1: Email đã đăng ký tài khoản, quay lại bước 3. |
| **\<\<Extend\>\> Đăng nhập** | 1. Khách hàng truy cập trang đăng nhập.<br>2. Hệ thống hiển thị trang đăng nhập.<br>3. Khách hàng nhập email và mật khẩu.<br>4. Khách hàng nhấn nút đăng nhập.<br>5. Hệ thống kiểm tra thông tin đăng nhập.<br>6. Hệ thống kiểm tra trạng thái tài khoản (is_active).<br>7. Thông báo đăng nhập thành công và chuyển hướng theo role.<br>Rẽ nhánh 1 — 5.1: Thông tin không hợp lệ, quay lại bước 3.<br>Rẽ nhánh 2 — 6.1: Tài khoản bị vô hiệu hóa, hiển thị thông báo lỗi. |
| **\<\<Extend\>\> Cập nhật profile** | 1. Khách hàng đăng nhập vào hệ thống.<br>2. Khách hàng vào quản lý tài khoản.<br>3. Khách hàng chọn chỉnh sửa thông tin.<br>4. Hệ thống hiển thị form cập nhật, khách hàng nhập thông tin cần thay đổi.<br>5. Khách hàng nhấn lưu thay đổi.<br>6. Hệ thống kiểm tra dữ liệu nhập hợp lệ.<br>7. Hiển thị thông tin tài khoản đã được cập nhật.<br>Rẽ nhánh 1 — 6.1: Thông tin không hợp lệ, quay lại bước 4. |
| **\<\<Extend\>\> Đăng xuất** | 1. Khách hàng nhấn nút Đăng xuất.<br>2. Hệ thống hủy phiên đăng nhập của khách hàng.<br>3. Hệ thống chuyển hướng về trang chủ. |
| **\<\<Extend\>\> Quên mật khẩu** | 1. Khách hàng chọn quên mật khẩu.<br>2. Hệ thống hiển thị trang quên mật khẩu.<br>3. Khách hàng nhập vào email.<br>4. Khách hàng nhấn nút tiếp tục.<br>5. Hệ thống kiểm tra thông tin.<br>6. Khách hàng nhập mật khẩu mới.<br>7. Lưu lại thông tin vào CSDL.<br>Rẽ nhánh 1 — 5.1: Thông tin không hợp lệ, quay lại bước 3. |


### PlantUML — Use Case Quản lý Tài khoản

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Khách hàng" as KH

rectangle "Quản lý Tài khoản" {
    usecase "Quản lý Tài khoản" as UC_QLTK
    usecase "Đăng ký" as UC_DK
    usecase "Đăng nhập" as UC_DN
    usecase "Cập nhật profile" as UC_CNP
    usecase "Đăng xuất" as UC_DX
    usecase "Quên mật khẩu" as UC_QMK
}

KH --> UC_QLTK

UC_QLTK <.. UC_DK    : <<extend>>
UC_QLTK <.. UC_DN    : <<extend>>
UC_QLTK <.. UC_CNP   : <<extend>>
UC_QLTK <.. UC_DX    : <<extend>>
UC_QLTK <.. UC_QMK   : <<extend>>

UC_CNP ..> UC_DN : <<include>>
UC_DX  ..> UC_DN : <<include>>
@enduml
```

---

## 3.3.2 Use case Quản lý Người dùng

| | |
|---|---|
| **Tên Use case** | Quản lý Người dùng |
| **Actor** | Admin, Giám đốc |
| **Mô tả** | Admin xem danh sách tất cả tài khoản trong hệ thống, có thể tìm kiếm theo email, thay đổi quyền hạn (role) và bật/tắt trạng thái hoạt động của tài khoản. |
| **Pre-conditions** | Actor đã đăng nhập với role admin hoặc director. |
| **Post-conditions** | - Thay đổi role hoặc trạng thái tài khoản được lưu vào CSDL và có hiệu lực ngay.<br>- Extend Use Case Đổi quyền hạn<br>- Extend Use Case Vô hiệu hóa / Kích hoạt tài khoản |
| **Luồng sự kiện chính** | Actor truy cập trang /admin/user. Hệ thống hiển thị danh sách tài khoản có phân trang, hỗ trợ tìm kiếm theo email. |
| **Luồng sự kiện phụ** | Actor không tìm thấy tài khoản, hệ thống hiển thị danh sách rỗng. |
| **\<\<Extend\>\> Đổi quyền hạn** | 1. Admin chọn tài khoản cần đổi quyền.<br>2. Admin chọn role mới từ danh sách.<br>3. Admin nhấn lưu.<br>4. Hệ thống kiểm tra quyền của admin có được phép gán role đó không.<br>5. Hệ thống lưu role mới vào CSDL.<br>6. Hệ thống hiển thị thông báo thành công.<br>Rẽ nhánh 1 — 4.1: Admin tự đổi role của mình, hệ thống từ chối.<br>Rẽ nhánh 2 — 4.2: Role không thuộc danh sách được phép, hệ thống từ chối. |
| **\<\<Extend\>\> Vô hiệu hóa / Kích hoạt tài khoản** | 1. Admin chọn tài khoản cần thay đổi trạng thái.<br>2. Admin nhấn nút toggle trạng thái.<br>3. Hệ thống kiểm tra quyền của admin.<br>4. Hệ thống đảo trạng thái is_active của tài khoản.<br>5. Hệ thống hiển thị thông báo kết quả.<br>Rẽ nhánh 1 — 3.1: Admin cố tắt tài khoản của chính mình, hệ thống từ chối.<br>Rẽ nhánh 2 — 3.2: Tài khoản không thuộc nhóm được phép thay đổi, hệ thống từ chối. |


### PlantUML — Use Case Quản lý Người dùng

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Admin" as A
actor "Giám đốc" as GD

rectangle "Quản lý Người dùng" {
    usecase "Đăng nhập" as UC_DN
    usecase "Quản lý Người dùng" as UC_QLND
    usecase "Đổi quyền hạn" as UC_DQ
    usecase "Vô hiệu hóa / Kích hoạt tài khoản" as UC_VHH
}

A --> UC_QLND
GD --> UC_QLND

UC_QLND ..> UC_DN  : <<include>>
UC_QLND <.. UC_DQ  : <<extend>>
UC_QLND <.. UC_VHH : <<extend>>
@enduml
```

---

## 3.3.3 Use case Quản lý Danh mục

| | |
|---|---|
| **Tên Use case** | Quản lý Danh mục |
| **Actor** | Admin |
| **Mô tả** | Admin quản lý các danh mục sản phẩm trong hệ thống. Admin có thể thêm mới, chỉnh sửa tên và bật/tắt trạng thái hiển thị của danh mục. |
| **Pre-conditions** | Actor đã đăng nhập với role admin. |
| **Post-conditions** | - Thay đổi danh mục được lưu vào CSDL và phản ánh ngay trên giao diện.<br>- Extend Use Case Thêm danh mục<br>-  |
| **Luồng sự kiện chính** | Actor truy cập trang quản lý danh mục. Hệ thống hiển thị danh sách danh mục hiện có kèm trạng thái hiển thị. |
| **Luồng sự kiện phụ** | Không có danh mục nào, hệ thống hiển thị danh sách rỗng. |
| **\<\<Extend\>\> Thêm danh mục** | 1. Admin nhấn nút Thêm mới.<br>2. Hệ thống hiển thị form nhập thông tin.<br>3. Admin nhập tên danh mục và chọn trạng thái (hiện/ẩn).<br>4. Admin nhấn Lưu.<br>5. Hệ thống kiểm tra dữ liệu hợp lệ.<br>6. Hệ thống lưu danh mục mới vào CSDL và chuyển về danh sách.<br>Rẽ nhánh 1 — 5.1: Tên bị trống, hiển thị lỗi quay lại bước 3. |
| **\<\<Extend\>\> Sửa danh mục / Bật-tắt trạng thái** | 1. Admin chọn danh mục cần sửa.<br>2. Hệ thống hiển thị form với tên và trạng thái hiện tại.<br>3. Admin chỉnh sửa tên và/hoặc thay đổi trạng thái (status: 0=ẩn, 1=hiện).<br>4. Admin nhấn Lưu.<br>5. Hệ thống kiểm tra dữ liệu hợp lệ.<br>6. Hệ thống cập nhật vào CSDL và chuyển về danh sách.<br>Rẽ nhánh 1 — 5.1: Tên bị trống, hiển thị lỗi quay lại bước 3. |

### PlantUML — Use Case Quản lý Danh mục

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Admin" as A

rectangle "Quản lý Danh mục" {
    usecase "Đăng nhập" as UC_DN
    usecase "Quản lý Danh mục" as UC_QLDM
    usecase "Thêm danh mục" as UC_THEM
    usecase "Sửa danh mục / Bật-tắt trạng thái" as UC_SUA
}

A --> UC_QLDM

UC_QLDM ..> UC_DN   : <<include>>
UC_QLDM <.. UC_THEM : <<extend>>
UC_QLDM <.. UC_SUA  : <<extend>>
@enduml
```


---

## 3.3.4 Use case Giỏ hàng

| | |
|---|---|
| **Tên Use case** | Giỏ hàng |
| **Actor** | Khách hàng |
| **Mô tả** | Khách hàng đã đăng nhập thêm sản phẩm vào giỏ hàng, xem giỏ hàng, cập nhật số lượng và xóa sản phẩm khỏi giỏ. |
| **Pre-conditions** | Actor đã đăng nhập vào hệ thống. |
| **Post-conditions** | - Giỏ hàng được cập nhật đúng theo thao tác.<br>- Extend Use Case Thêm vào giỏ hàng<br>- Extend Use Case Xem giỏ hàng<br>- Extend Use Case Cập nhật số lượng<br>- Extend Use Case Xóa sản phẩm khỏi giỏ |
| **Luồng sự kiện chính** | Actor xem danh sách sản phẩm, chọn sản phẩm và thêm vào giỏ hàng. Hệ thống hiển thị giỏ hàng với tổng tiền. |
| **Luồng sự kiện phụ** | Actor chưa đăng nhập khi thêm giỏ hàng, hệ thống chuyển hướng về trang đăng nhập. |
| **\<\<Extend\>\> Thêm vào giỏ hàng** | 1. Khách hàng chọn sản phẩm và nhấn Thêm vào giỏ.<br>2. Hệ thống kiểm tra số lượng tồn kho.<br>3. Hệ thống kiểm tra sản phẩm đã có trong giỏ chưa.<br>4. Nếu chưa có: tạo mới item trong giỏ. Nếu đã có: tăng số lượng.<br>5. Hệ thống thông báo thêm thành công.<br>Rẽ nhánh 1 — 2.1: Tồn kho không đủ, hiển thị lỗi. |
| **\<\<Extend\>\> Xem giỏ hàng** | 1. Khách hàng truy cập trang giỏ hàng.<br>2. Hệ thống hiển thị danh sách sản phẩm đã thêm, số lượng, giá (đã áp dụng khuyến mãi nếu có).<br>3. Hệ thống hiển thị tổng tiền. |
| **\<\<Extend\>\> Cập nhật số lượng** | 1. Khách hàng nhấn nút tăng/giảm số lượng.<br>2. Hệ thống kiểm tra tồn kho.<br>3. Hệ thống cập nhật số lượng qua AJAX.<br>Rẽ nhánh 1 — 2.1: Số lượng vượt tồn kho, không cho tăng thêm. |
| **\<\<Extend\>\> Xóa sản phẩm khỏi giỏ** | 1. Khách hàng nhấn nút xóa sản phẩm.<br>2. Hệ thống xóa item khỏi giỏ hàng của khách.<br>3. Hệ thống cập nhật lại giỏ hàng và tổng tiền. |

### PlantUML — Use Case Giỏ hàng

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Khách hàng" as KH

rectangle "Giỏ hàng" {
    usecase "Đăng nhập" as UC_DN
    usecase "Giỏ hàng" as UC_GH
    usecase "Thêm vào giỏ hàng" as UC_THEM
    usecase "Xem giỏ hàng" as UC_XEM
    usecase "Cập nhật số lượng" as UC_CAPNHAT
    usecase "Xóa sản phẩm khỏi giỏ" as UC_XOA
}

KH --> UC_GH

UC_GH ..> UC_DN      : <<include>>
UC_GH <.. UC_THEM    : <<extend>>
UC_GH <.. UC_XEM     : <<extend>>
UC_GH <.. UC_CAPNHAT : <<extend>>
UC_GH <.. UC_XOA     : <<extend>>
@enduml
```

---

## 3.3.5 Use case Đặt hàng

| | |
|---|---|
| **Tên Use case** | Đặt hàng |
| **Actor** | Khách hàng |
| **Mô tả** | Khách hàng chọn sản phẩm trong giỏ để thanh toán, nhập thông tin giao hàng, chọn phương thức thanh toán (COD hoặc chuyển khoản qua PayOS) và xác nhận đặt hàng. |
| **Pre-conditions** | Actor đã đăng nhập và có sản phẩm trong giỏ hàng. |
| **Post-conditions** | - Đơn hàng được tạo trong CSDL, giỏ hàng được xóa.<br>- Extend Use Case Đặt hàng / Thanh toán<br>- Extend Use Case Xem lịch sử đơn hàng<br>- Extend Use Case Hủy đơn hàng<br>- Extend Use Case Yêu cầu hoàn hàng<br>- Extend Use Case Xác nhận nhận hàng |
| **Luồng sự kiện chính** | 1. Khách hàng chọn sản phẩm trong giỏ và nhấn Thanh toán.<br>2. Hệ thống hiển thị trang nhập thông tin giao hàng.<br>3. Khách hàng nhập họ tên, địa chỉ, SĐT và chọn phương thức thanh toán.<br>4. Khách hàng xác nhận đặt hàng. |
| **Luồng sự kiện phụ** | Không chọn sản phẩm nào trong giỏ, hệ thống thông báo lỗi. |
| **\<\<Extend\>\> Đặt hàng / Thanh toán** | 1. Khách hàng chọn sản phẩm trong giỏ và nhấn Thanh toán.<br>2. Hệ thống hiển thị trang nhập thông tin giao hàng.<br>3. Khách hàng nhập họ tên, địa chỉ, SĐT và chọn phương thức thanh toán.<br>4. Khách hàng nhấn Đặt hàng.<br>5. Hệ thống tạo đơn hàng và xóa sản phẩm khỏi giỏ.<br>Rẽ nhánh 1 — Thanh toán khi nhận hàng (COD): Hệ thống tạo đơn status=1 (đã đặt hàng), gửi email xác nhận kèm link thanh toán online tùy chọn, thông báo đặt hàng thành công. Admin tiếp nhận và xử lý xuất kho.<br>Rẽ nhánh 2 — Chuyển khoản online (PayOS): Hệ thống tạo đơn status=0 (chờ thanh toán), tạo link PayOS và chuyển hướng sang trang QR. Khách quét mã thanh toán → PayOS gọi webhook → hệ thống cập nhật status=1. Nếu khách hủy thanh toán → đơn bị xóa, sản phẩm hoàn về giỏ. |
| **\<\<Extend\>\> Hủy đơn hàng** | 1. Khách hàng vào lịch sử đơn hàng.<br>2. Khách hàng chọn đơn cần hủy và nhập lý do.<br>3. Hệ thống kiểm tra đơn có ở trạng thái cho phép hủy không (status=1).<br>4. Hệ thống cập nhật status=-1.<br>5. Hệ thống hoàn sản phẩm về giỏ hàng.<br>Rẽ nhánh 1 — 3.1: Đơn đã xuất kho (status≠1), không cho hủy. |
| **\<\<Extend\>\> Yêu cầu hoàn hàng** | 1. Khách hàng vào lịch sử đơn hàng, chọn đơn đã giao (status=4).<br>2. Khách hàng nhấn Yêu cầu hoàn hàng và nhập lý do.<br>3. Hệ thống kiểm tra đơn trong vòng 3 ngày kể từ khi giao.<br>4. Hệ thống cập nhật status=5 (chờ admin xét duyệt hoàn).<br>Rẽ nhánh 1 — 3.1: Quá 3 ngày hoặc đơn không hợp lệ, hệ thống từ chối. |
| **\<\<Extend\>\> Xem lịch sử đơn hàng** | 1. Khách hàng truy cập trang lịch sử đơn hàng.<br>2. Hệ thống hiển thị danh sách đơn hàng (ẩn các đơn status=0 chưa thanh toán).<br>3. Khách hàng chọn đơn để xem chi tiết.<br>4. Hệ thống hiển thị thông tin đơn, danh sách sản phẩm, trạng thái theo timeline. |
| **\<\<Extend\>\> Xác nhận nhận hàng** | 1. Khách hàng quét mã QR trên thùng hàng.<br>2. Hệ thống hiển thị trang xác nhận thông tin đơn hàng.<br>3. Khách hàng nhấn xác nhận đã nhận.<br>4. Hệ thống cập nhật status=4 (Hoàn tất). |

### PlantUML — Use Case Đặt hàng

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Khách hàng" as KH

rectangle "Đặt hàng" {
    usecase "Đăng nhập" as UC_DN
    usecase "Đặt hàng" as UC_DH
    usecase "Đặt hàng / Thanh toán" as UC_TT
    usecase "Xem lịch sử đơn hàng" as UC_LS
    usecase "Hủy đơn hàng" as UC_HUY
    usecase "Yêu cầu hoàn hàng" as UC_HOAN
    usecase "Xác nhận nhận hàng" as UC_XN
}

KH --> UC_DH

UC_DH ..> UC_DN   : <<include>>
UC_DH <.. UC_TT   : <<extend>>
UC_DH <.. UC_LS   : <<extend>>
UC_DH <.. UC_HUY  : <<extend>>
UC_DH <.. UC_HOAN : <<extend>>
UC_DH <.. UC_XN   : <<extend>>
@enduml
```


---

## 3.3.6 Use case Quản lý Đơn hàng (Admin)

| | |
|---|---|
| **Tên Use case** | Quản lý Đơn hàng |
| **Actor** | Admin, Nhân viên kho |
| **Mô tả** | Admin và nhân viên kho xem danh sách đơn hàng khách, xử lý các bước từ xác nhận → xuất kho → giao hàng → hoàn tất. Admin còn xử lý yêu cầu hoàn hàng và hàng hỏng. |
| **Pre-conditions** | Actor đã đăng nhập với role admin hoặc warehouse. |
| **Post-conditions** | - Trạng thái đơn hàng được cập nhật vào CSDL.<br>- Extend Use Case Xem danh sách đơn hàng<br>- Extend Use Case Cập nhật trạng thái đơn hàng<br>- Extend Use Case Xử lý hoàn hàng<br>- Extend Use Case Xem hàng hỏng |
| **Luồng sự kiện chính** | Actor truy cập /admin/orders. Hệ thống hiển thị danh sách đơn hàng, hỗ trợ lọc theo trạng thái. |
| **Luồng sự kiện phụ** | Không có đơn nào ở trạng thái cần xử lý, hệ thống hiển thị danh sách rỗng. |
| **\<\<Extend\>\> Xem danh sách đơn hàng** | 1. Actor truy cập trang quản lý đơn hàng.<br>2. Hệ thống hiển thị danh sách đơn hàng có phân trang và lọc theo trạng thái.<br>3. Actor chọn đơn để xem chi tiết. |
| **\<\<Extend\>\> Cập nhật trạng thái đơn hàng** | 1. Admin chọn đơn hàng cần xử lý.<br>2. Admin chọn trạng thái mới (xác nhận / xuất kho / đang giao / hoàn tất / hủy).<br>3. Hệ thống kiểm tra chuyển trạng thái hợp lệ.<br>4. Nếu xuất kho: hệ thống trừ tồn kho và ghi nhật ký kho.<br>5. Hệ thống lưu trạng thái mới.<br>Rẽ nhánh 1 — 3.1: Chuyển trạng thái không hợp lệ, hệ thống từ chối. |
| **\<\<Extend\>\> Xử lý hoàn hàng** | 1. Admin nhận yêu cầu hoàn hàng từ khách (status=5).<br>2. Admin xem xét lý do hoàn.<br>3a. Duyệt hoàn: Admin nhấn duyệt → hệ thống cập nhật status=6, cộng lại tồn kho.<br>3b. Từ chối hoàn: Admin nhập lý do từ chối → hệ thống cập nhật status=4.<br>Rẽ nhánh — 3b: Lý do từ chối rỗng, hệ thống yêu cầu nhập. |
| **\<\<Extend\>\> Xem hàng hỏng** | 1. Admin truy cập trang hàng hỏng.<br>2. Hệ thống hiển thị danh sách đơn hàng đã được duyệt hoàn (status=6).<br>3. Admin ghi nhận và xử lý nội bộ. |

### PlantUML — Use Case Quản lý Đơn hàng

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Admin" as A
actor "Nhân viên kho" as NVK

rectangle "Quản lý Đơn hàng" {
    usecase "Đăng nhập" as UC_DN
    usecase "Quản lý Đơn hàng" as UC_QLDH
    usecase "Xem danh sách đơn hàng" as UC_XEM
    usecase "Cập nhật trạng thái đơn hàng" as UC_CAPNHAT
    usecase "Xử lý hoàn hàng" as UC_HOAN
    usecase "Xem hàng hỏng" as UC_HONG
}

A --> UC_QLDH
NVK --> UC_QLDH

UC_QLDH ..> UC_DN      : <<include>>
UC_QLDH <.. UC_XEM     : <<extend>>
UC_QLDH <.. UC_CAPNHAT : <<extend>>
UC_QLDH <.. UC_HOAN    : <<extend>>
UC_QLDH <.. UC_HONG    : <<extend>>
@enduml
```

---

## 3.3.7 Use case Quản lý Sản phẩm

| | |
|---|---|
| **Tên Use case** | Quản lý Sản phẩm |
| **Actor** | Admin |
| **Mô tả** | Admin thêm mới, chỉnh sửa thông tin và bật/tắt trạng thái hiển thị sản phẩm. Admin có thể import sản phẩm hàng loạt qua file Excel. |
| **Pre-conditions** | Actor đã đăng nhập với role admin. |
| **Post-conditions** | - Thay đổi sản phẩm được lưu vào CSDL.<br>- Extend Use Case Thêm sản phẩm bằng import file Excel<br>- Extend Use Case Sửa sản phẩm / Ẩn-Hiện sản phẩm |
| **Luồng sự kiện chính** | Actor truy cập trang /admin/product. Hệ thống hiển thị danh sách sản phẩm có tìm kiếm và phân trang. |
| **Luồng sự kiện phụ** | Không có sản phẩm nào, hệ thống hiển thị danh sách rỗng. |
| **\<\<Extend\>\> Thêm sản phẩm bằng import file Excel** | 1. Admin nhấn Import sản phẩm.<br>2. Hệ thống hiển thị form upload file.<br>3. Admin chọn file Excel theo đúng mẫu cột (title, price, quantity, category, brand, ...).<br>4. Hệ thống đọc file và hiển thị preview danh sách sản phẩm.<br>5. Admin xác nhận import.<br>6. Hệ thống lưu các sản phẩm hợp lệ vào CSDL.<br>Rẽ nhánh 1 — 3.1: File sai định dạng hoặc thiếu cột bắt buộc, hiển thị lỗi. |
| **\<\<Extend\>\> Sửa sản phẩm / Ẩn-Hiện sản phẩm** | 1. Admin chọn sản phẩm cần sửa.<br>2. Hệ thống hiển thị form với thông tin hiện tại.<br>3. Admin chỉnh sửa thông tin và/hoặc thay đổi trạng thái (status: 0=ẩn, 1=hiện).<br>4. Admin nhấn Lưu.<br>5. Hệ thống kiểm tra dữ liệu hợp lệ.<br>6. Hệ thống cập nhật vào CSDL.<br>Rẽ nhánh 1 — 5.1: Dữ liệu không hợp lệ, hiển thị lỗi quay lại bước 3. |

### PlantUML — Use Case Quản lý Sản phẩm

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Admin" as A

rectangle "Quản lý Sản phẩm" {
    usecase "Đăng nhập" as UC_DN
    usecase "Quản lý Sản phẩm" as UC_QLSP
    usecase "Thêm sản phẩm bằng import file Excel" as UC_IMPORT
    usecase "Sửa sản phẩm / Ẩn-Hiện sản phẩm" as UC_SUA
}

A --> UC_QLSP

UC_QLSP ..> UC_DN     : <<include>>
UC_QLSP <.. UC_IMPORT : <<extend>>
UC_QLSP <.. UC_SUA    : <<extend>>
@enduml
```


---

## 3.3.8 Use case Quản lý Kho hàng

| | |
|---|---|
| **Tên Use case** | Quản lý Kho hàng |
| **Actor** | Admin, Nhân viên kho |
| **Mô tả** | Nhân viên kho upload file nhập kho, admin duyệt hoặc từ chối. Hệ thống tự động cập nhật tồn kho sau khi admin duyệt. |
| **Pre-conditions** | Actor đã đăng nhập với role admin hoặc warehouse. |
| **Post-conditions** | - Tồn kho được cập nhật sau khi duyệt nhập kho.<br>- Extend Use Case Upload file nhập kho<br>- Extend Use Case Duyệt nhập kho<br>- Extend Use Case Từ chối nhập kho<br>- Extend Use Case Xem tồn kho |
| **Luồng sự kiện chính** | Actor truy cập trang kho hàng. Hệ thống hiển thị danh sách tồn kho và lịch sử nhập/xuất. |
| **Luồng sự kiện phụ** | File nhập kho sai định dạng, hệ thống hiển thị lỗi. |
| **\<\<Extend\>\> Upload file nhập kho** | 1. Nhân viên kho nhấn Upload file nhập kho.<br>2. Hệ thống hiển thị form upload.<br>3. Nhân viên chọn file Excel và điền thông tin nhà cung cấp.<br>4. Hệ thống đọc file và lưu với status=pending.<br>5. Hệ thống hiển thị thông báo chờ admin duyệt.<br>Rẽ nhánh 1 — 3.1: File sai định dạng, hiển thị lỗi. |
| **\<\<Extend\>\> Duyệt nhập kho** | 1. Admin xem danh sách file nhập kho chờ duyệt.<br>2. Admin mở file xem chi tiết từng sản phẩm.<br>3. Admin tick chọn sản phẩm cần nhập và điền số lượng.<br>4. Admin nhấn Duyệt.<br>5. Hệ thống cập nhật tồn kho, tạo phiếu nhập và ghi nhật ký kho. |
| **\<\<Extend\>\> Từ chối nhập kho** | 1. Admin mở file nhập kho chờ duyệt.<br>2. Admin nhấn Từ chối và nhập lý do.<br>3. Hệ thống cập nhật status=rejected.<br>Rẽ nhánh 1 — 2.1: Lý do rỗng, hệ thống yêu cầu nhập lý do. |
| **\<\<Extend\>\> Xem tồn kho** | 1. Actor truy cập trang xem tồn kho.<br>2. Hệ thống hiển thị danh sách sản phẩm kèm số lượng tồn kho hiện tại.<br>3. Actor có thể xem lịch sử biến động tồn kho theo từng sản phẩm. |

### PlantUML — Use Case Quản lý Kho hàng

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Admin" as A
actor "Nhân viên kho" as NVK

rectangle "Quản lý Kho hàng" {
    usecase "Đăng nhập" as UC_DN
    usecase "Quản lý Kho hàng" as UC_QLKH
    usecase "Upload file nhập kho" as UC_UPLOAD
    usecase "Duyệt nhập kho" as UC_DUYET
    usecase "Từ chối nhập kho" as UC_TUCHOI
    
}

A --> UC_QLKH
NVK --> UC_QLKH

UC_QLKH ..> UC_DN      : <<include>>
UC_QLKH <.. UC_UPLOAD  : <<extend>>
UC_QLKH <.. UC_DUYET   : <<extend>>
UC_QLKH <.. UC_TUCHOI  : <<extend>>

@enduml
```

---

## 3.3.9 Use case Báo giá Nhà sản xuất

| | |
|---|---|
| **Tên Use case** | Báo giá Nhà sản xuất |
| **Actor** | Nhà sản xuất, Admin |
| **Mô tả** | Nhà sản xuất upload file báo giá (Excel/CSV) lên hệ thống. Admin xem danh sách báo giá, chọn sản phẩm cần đặt và tạo đơn đặt hàng. |
| **Pre-conditions** | Actor đã đăng nhập với role tương ứng (manufacturer hoặc admin). |
| **Post-conditions** | - Báo giá được lưu vào CSDL. Admin có thể tạo đơn đặt hàng hoặc từ chối báo giá.<br>- Extend Use Case Upload file báo giá<br>- Extend Use Case Xem chi tiết báo giá<br>- Extend Use Case Từ chối báo giá<br>- Extend Use Case Tạo đơn đặt hàng từ báo giá |
| **Luồng sự kiện chính** | NSX truy cập trang /admin/supplier-offers và upload file. Admin vào trang để xem danh sách báo giá và xử lý. |
| **Luồng sự kiện phụ** | File báo giá sai định dạng, hệ thống hiển thị lỗi. |
| **\<\<Extend\>\> Upload file báo giá** | 1. Nhà sản xuất đăng nhập và truy cập trang báo giá.<br>2. NSX chọn file Excel/CSV chứa danh sách sản phẩm và đơn giá.<br>3. NSX nhấn Upload.<br>4. Hệ thống đọc file, tạo SupplierOffer + các dòng sản phẩm liên kết với tài khoản NSX.<br>5. Hệ thống hiển thị thông báo upload thành công.<br>Rẽ nhánh 1 — 4.1: File rỗng hoặc sai định dạng, hiển thị lỗi. |
| **\<\<Extend\>\> Xem chi tiết báo giá** | 1. Admin chọn báo giá cần xem.<br>2. Hệ thống hiển thị danh sách sản phẩm, đơn giá, nhà sản xuất.<br>3. Admin tick chọn sản phẩm và điền số lượng để đặt hàng. |
| **\<\<Extend\>\> Từ chối báo giá** | 1. Admin xem chi tiết báo giá không phù hợp.<br>2. Admin nhấn Từ chối báo giá.<br>3. Hệ thống cập nhật status=rejected.<br>4. Hệ thống chuyển về danh sách báo giá. |
| **\<\<Extend\>\> Tạo đơn đặt hàng từ báo giá** | 1. Admin xem chi tiết báo giá, tick sản phẩm và điền số lượng cần đặt.<br>2. Admin nhấn Đặt hàng.<br>3. Hệ thống tạo PurchaseOrder và các dòng chi tiết từ báo giá được chọn.<br>4. Hệ thống cập nhật báo giá đó status=accepted.<br>5. Nếu báo giá này thuộc một yêu cầu nhập hàng, hệ thống tự động đóng yêu cầu đó lại (status=closed).<br>6. Hệ thống redirect sang trang danh sách đơn đặt hàng. |

### PlantUML — Use Case Báo giá Nhà sản xuất

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Admin" as A

rectangle "Báo giá Nhà sản xuất" {
    usecase "Đăng nhập" as UC_DN
    usecase "Báo giá Nhà sản xuất" as UC_BG
    usecase "Upload file báo giá" as UC_UPLOAD
    usecase "Xem chi tiết báo giá" as UC_XEM
    usecase "Từ chối báo giá" as UC_TC
    usecase "Tạo đơn đặt hàng từ báo giá" as UC_TAODH
}

actor "Nhà sản xuất" as NSX

A --> UC_BG
NSX --> UC_BG

UC_BG ..> UC_DN     : <<include>>
UC_BG <.. UC_UPLOAD : <<extend>>
UC_BG <.. UC_XEM    : <<extend>>
UC_BG <.. UC_TC     : <<extend>>
UC_BG <.. UC_TAODH  : <<extend>>
@enduml
```

---

## 3.3.10 Use case Yêu cầu nhập hàng

| | |
|---|---|
| **Tên Use case** | Yêu cầu nhập hàng |
| **Actor** | Admin, Nhà sản xuất |
| **Mô tả** | Admin tạo yêu cầu nhập hàng công khai từ danh sách sản phẩm sắp hết. Nhà sản xuất xem yêu cầu và upload file báo giá đáp lại. Admin xem và so sánh các báo giá nhận được. |
| **Pre-conditions** | Actor đã đăng nhập với role admin hoặc manufacturer. |
| **Post-conditions** | - Yêu cầu được tạo, NSX có thể gửi báo giá. Admin có thể đóng yêu cầu và tạo đơn đặt hàng.<br>- Extend Use Case Tạo yêu cầu nhập hàng<br>- Extend Use Case Xem yêu cầu và gửi báo giá<br>- Extend Use Case Đóng yêu cầu |
| **Luồng sự kiện chính** | Admin truy cập /admin/procurement. Hệ thống hiển thị danh sách yêu cầu thu mua. |
| **Luồng sự kiện phụ** | Không có sản phẩm sắp hết hàng, modal hiển thị danh sách rỗng. |
| **\<\<Extend\>\> Tạo yêu cầu nhập hàng** | 1. Admin vào trang sản phẩm và nhấn Đăng yêu cầu nhập hàng.<br>2. Hệ thống hiển thị modal danh sách SP tồn kho thấp.<br>3. Admin tick SP cần nhập và điền số lượng.<br>4. Admin điền hạn chót và nhấn Đăng yêu cầu.<br>5. Hệ thống tạo ProcurementRequest (status=open) và các dòng sản phẩm.<br>6. Hệ thống redirect sang trang chi tiết yêu cầu.<br>Rẽ nhánh 1 — 3.1: Không chọn sản phẩm nào, hệ thống hiển thị lỗi. |
| **\<\<Extend\>\> Xem yêu cầu và gửi báo giá** | 1. Nhà sản xuất truy cập trang yêu cầu nhập hàng.<br>2. Hệ thống hiển thị danh sách yêu cầu đang mở (status=open).<br>3. NSX chọn yêu cầu và tải file mẫu báo giá.<br>4. NSX điền giá vào file mẫu và upload lên.<br>5. Hệ thống tạo SupplierOffer liên kết với yêu cầu.<br>Rẽ nhánh 1 — 4.1: File sai định dạng hoặc thiếu dữ liệu, hiển thị lỗi. |
| **\<\<Extend\>\> Đóng yêu cầu** | 1. Admin nhấn Đóng yêu cầu.<br>2. Hệ thống xác nhận đóng.<br>3. Hệ thống cập nhật status=closed.<br>4. NSX không thể upload báo giá cho yêu cầu này nữa. |

### PlantUML — Use Case Yêu cầu nhập hàng

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Admin" as A
actor "Nhà sản xuất" as NSX

rectangle "Yêu cầu nhập hàng" {
    usecase "Đăng nhập" as UC_DN
    usecase "Yêu cầu nhập hàng" as UC_YCNH
    usecase "Tạo yêu cầu nhập hàng" as UC_TAO
    usecase "Xem yêu cầu và gửi báo giá" as UC_GUI
    usecase "Đóng yêu cầu" as UC_DONG
}

A --> UC_YCNH
NSX --> UC_YCNH

UC_YCNH ..> UC_DN   : <<include>>
UC_YCNH <.. UC_TAO  : <<extend>>
UC_YCNH <.. UC_GUI  : <<extend>>
UC_YCNH <.. UC_DONG : <<extend>>
@enduml
```

---

## 3.3.11 Use case Quản lý Đơn đặt hàng NSX

| | |
|---|---|
| **Tên Use case** | Quản lý Đơn đặt hàng NSX |
| **Actor** | Admin, Nhân viên kho, Nhà sản xuất |
| **Mô tả** | Admin tạo đơn đặt hàng từ báo giá được chọn. Nhà sản xuất xác nhận đơn và giao hàng. Nhân viên kho nhận hàng và cập nhật tồn kho. |
| **Pre-conditions** | Actor đã đăng nhập với role tương ứng. Đã có báo giá được chấp nhận. |
| **Post-conditions** | - Đơn đặt hàng được theo dõi qua các bước. Tồn kho được cập nhật khi nhận hàng.<br>- Extend Use Case Xem chi tiết đơn đặt hàng<br>- Extend Use Case Cập nhật trạng thái đơn<br>- Extend Use Case Xác nhận đã nhận hàng |
| **Luồng sự kiện chính** | Actor truy cập /admin/purchase-orders. Hệ thống hiển thị danh sách đơn đặt hàng. |
| **Luồng sự kiện phụ** | Chưa có đơn đặt hàng nào, hệ thống hiển thị danh sách rỗng. |
| **\<\<Extend\>\> Xem chi tiết đơn đặt hàng** | 1. Actor chọn đơn đặt hàng cần xem.<br>2. Hệ thống hiển thị thông tin đơn, danh sách sản phẩm, trạng thái hiện tại.<br>3. Nhà sản xuất xem thông tin và chuẩn bị hàng. |
| **\<\<Extend\>\> Cập nhật trạng thái đơn** | 1. Admin hoặc NSX chọn trạng thái mới phù hợp với vai trò.<br>2. Hệ thống kiểm tra quyền và tính hợp lệ của chuyển trạng thái.<br>3. Hệ thống lưu trạng thái mới.<br>Rẽ nhánh 1 — 2.1: Không có quyền chuyển trạng thái này, hệ thống từ chối. |
| **\<\<Extend\>\> Xác nhận đã nhận hàng** | 1. Nhân viên kho mở đơn đặt hàng đang giao.<br>2. Nhân viên kho nhấn Đã nhận hàng.<br>3. Hệ thống cập nhật status=received.<br>4. Hệ thống cộng số lượng vào tồn kho và ghi nhật ký kho. |

### PlantUML — Use Case Quản lý Đơn đặt hàng NSX

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Admin" as A
actor "Nhân viên kho" as NVK
actor "Nhà sản xuất" as NSX

rectangle "Quản lý Đơn đặt hàng NSX" {
    usecase "Đăng nhập" as UC_DN
    usecase "Quản lý Đơn đặt hàng NSX" as UC_QLDH
    usecase "Xem chi tiết đơn đặt hàng" as UC_XEM
    usecase "Cập nhật trạng thái đơn" as UC_CAPNHAT
    usecase "Xác nhận đã nhận hàng" as UC_XN
}

A --> UC_QLDH
NVK --> UC_QLDH
NSX --> UC_QLDH

UC_QLDH ..> UC_DN      : <<include>>
UC_QLDH <.. UC_XEM     : <<extend>>
UC_QLDH <.. UC_CAPNHAT : <<extend>>
UC_QLDH <.. UC_XN      : <<extend>>
@enduml
```
