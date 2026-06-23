# Hướng dẫn tạo file Excel Báo giá từ NSX

## 📋 Mục đích
File Excel này dùng để **NSX (Nhà sản xuất) báo giá** cho các sản phẩm trong Yêu cầu nhập hàng (Procurement Request).

---

## 📊 Cấu trúc file Excel

### Dòng 1 - Header (tiêu đề cột)
File Excel phải có **10 cột** theo đúng thứ tự:

| Cột | Tên cột | Ý nghĩa | Bắt buộc |
|-----|---------|---------|----------|
| A | **title** | Tên sản phẩm | ✅ Bắt buộc |
| B | **image** | URL ảnh sản phẩm | ⚪ Không bắt buộc |
| C | **decription** | Mô tả sản phẩm | ⚪ Không bắt buộc |
| D | **unit_price** | Giá nhập từ NSX (VNĐ) | ✅ Bắt buộc |
| E | **sl_order** | Số lượng đã đặt hàng | ✅ Bắt buộc |
| F | **quantity** | Số lượng thực tế nhập kho | ⚪ Để trống (NV kho điền sau) |
| G | **volume** | Dung tích (VD: 100ml) | ⚪ Không bắt buộc |
| H | **category** | Tên danh mục (VD: Nam, Nữ) | ✅ Bắt buộc |
| I | **brand** | Tên thương hiệu (VD: CHANEL) | ✅ Bắt buộc |
| J | **concentration** | Nồng độ (VD: EDP, EDT) | ✅ Bắt buộc |

### Dòng 2 trở đi - Dữ liệu sản phẩm
Mỗi dòng là 1 sản phẩm trong báo giá.

---

## 📝 Ví dụ file Excel

### Sheet mẫu:

| title | image | decription | unit_price | sl_order | quantity | volume | category | brand | concentration |
|-------|-------|------------|------------|----------|----------|--------|----------|-------|---------------|
| Chanel No5 | https://example.com/chanel.jpg | Nước hoa nữ cao cấp | 2500000 | 20 | | 100ml | Nữ | CHANEL | EDP |
| Versace Pour Homme | https://example.com/versace.jpg | Nước hoa nam sang trọng | 2500000 | 29 | | 100ml | Nam | VERSACE | EDT |
| Versace new generation | | Phiên bản mới | 2000 | 7 | | 50ml | Nam | GUCCI | EDP |

### Giải thích từng cột:

**Cột A - title** (Tên sản phẩm)
- Tên chính xác của sản phẩm
- Ví dụ: `Chanel No5`, `Versace Pour Homme`

**Cột B - image** (URL ảnh)
- Link đầy đủ đến ảnh sản phẩm
- Có thể để trống nếu không có ảnh
- Ví dụ: `https://example.com/chanel.jpg`

**Cột C - decription** (Mô tả)
- Mô tả ngắn về sản phẩm
- Có thể để trống

**Cột D - unit_price** (Giá nhập)
- Giá mà NSX chào bán (VNĐ)
- Chỉ ghi số, không ghi dấu phẩy hay "đ"
- Ví dụ: `2500000` (2 triệu 5)

**Cột E - sl_order** (Số lượng Order)
- Số lượng mà shop đã đặt hàng
- Con số này lấy từ Yêu cầu nhập hàng (Procurement Request)
- Ví dụ: `20`, `29`, `7`

**Cột F - quantity** (Số lượng thực tế)
- **ĐỂ TRỐNG** - NV kho sẽ điền sau khi nhận hàng
- Số lượng thực tế có thể khác với `sl_order` (do thiếu hàng, hư hỏng...)

**Cột G - volume** (Dung tích)
- Dung tích của sản phẩm
- Ví dụ: `100ml`, `50ml`, `30ml`
- Có thể để trống, mặc định là `100ml`

**Cột H - category** (Danh mục)
- Tên danh mục trong hệ thống
- Phải trùng khớp với tên trong database
- Ví dụ: `Nam`, `Nữ`, `Unisex`

**Cột I - brand** (Thương hiệu)
- Tên thương hiệu trong hệ thống
- Phải trùng khớp với tên trong database
- Ví dụ: `CHANEL`, `VERSACE`, `GUCCI`

**Cột J - concentration** (Nồng độ)
- Nồng độ nước hoa
- Phải trùng khớp với tên trong database
- Ví dụ: `EDP` (Eau de Parfum), `EDT` (Eau de Toilette), `EDC` (Eau de Cologne)

---

## 🔄 Quy trình sử dụng

### Bước 1: Tạo Yêu cầu nhập hàng (Procurement Request)
- Admin tạo yêu cầu nhập hàng với danh sách SP cần mua
- Hệ thống sinh mã yêu cầu (VD: `PRQ-20260622-001`)

### Bước 2: Tải file mẫu Excel
- Vào trang **Yêu cầu nhập hàng** → Chi tiết yêu cầu
- Phần **"UPLOAD FILE BÁO GIÁ TỪ NSX"**
- Bấm nút **"Tải file mẫu Excel"** (góc phải)
- Hệ thống tự động xuất file CSV chứa:
  - Danh sách SP trong yêu cầu
  - Thông tin sẵn có (tên, ảnh, mô tả, số lượng cần, category, brand...)
  - Cột **unit_price** (giá nhập) = trống → NSX điền
  - Cột **quantity** (SL thực tế) = trống → để sau

### Bước 3: NSX điền giá vào file
- Mở file CSV vừa tải
- Điền giá nhập vào **cột D (unit_price)**
- Không được sửa tên SP, category, brand, concentration
- Lưu file

### Bước 4: Upload file báo giá
- Chọn NSX trong dropdown
- Chọn file CSV đã điền giá
- Điền ghi chú nếu cần
- Bấm **"Upload"**
- Hệ thống đọc file và tạo **Supplier Offer** (Báo giá NSX)

### Bước 5: Admin duyệt báo giá
- Admin xem chi tiết báo giá
- Tick chọn các SP muốn đặt hàng
- Điền số lượng đặt cho từng SP
- Bấm **"Đặt hàng các SP đã chọn"**
- Hệ thống tạo **Purchase Order** (Đơn đặt hàng)

### Bước 6: Xuất file nhập kho
- Vào trang **Purchase Order** → Chi tiết đơn
- Bấm **"Xuất CSV nhập kho"**
- File CSV sẽ có đầy đủ 10 cột, trong đó:
  - Cột E (`sl_order`) = số lượng đã đặt
  - Cột F (`quantity`) = để trống

### Bước 7: NV kho điền số lượng thực tế
- NV kho nhận hàng và đếm số lượng thực tế
- Mở file CSV, điền số vào **cột F (quantity)**
- Lưu file

### Bước 7: Upload và duyệt nhập kho
- Vào trang **Nhập Kho** (Warehouse)
- Upload file CSV đã điền số lượng
- Admin xem preview và duyệt
- Hệ thống **cộng tồn kho** theo số lượng thực tế (cột F)

---

## ⚠️ Lưu ý quan trọng

### 1. Tên cột phải chính xác
Header phải đúng tên: `title`, `image`, `decription`, `unit_price`, `sl_order`, `quantity`, `volume`, `category`, `brand`, `concentration`

**Lưu ý typo**: Cột mô tả là `decription` (thiếu chữ "s"), KHÔNG phải `description`

### 2. Thứ tự cột phải đúng
10 cột phải theo đúng thứ tự từ A đến J như bảng trên.

### 3. Category, Brand, Concentration phải tồn tại trong hệ thống
Nếu ghi sai tên, hệ thống sẽ không import được sản phẩm.

Kiểm tra danh sách:
- **Category**: Vào menu Admin → Danh mục
- **Brand**: Vào menu Admin → Thương hiệu  
- **Concentration**: Vào menu Admin → Nồng độ

### 4. Giá trị số không có ký tự đặc biệt
- `unit_price`: chỉ ghi số, VD: `2500000` (KHÔNG ghi `2,500,000` hay `2.500.000đ`)
- `sl_order`, `quantity`: số nguyên dương

### 5. Cột F (quantity) luôn để trống khi upload báo giá
NV kho sẽ điền sau khi nhận hàng thực tế.

### 6. File hỗ trợ: CSV, XLSX, XLS
Có thể lưu dưới 3 định dạng:
- `.csv` (UTF-8)
- `.xlsx` (Excel 2007+)
- `.xls` (Excel 97-2003)

---

## 📥 File mẫu

### Download file Excel mẫu
Tạo file Excel với nội dung như sau, sau đó lưu với tên `mau-bao-gia-nsx.xlsx`:

```
title	image	decription	unit_price	sl_order	quantity	volume	category	brand	concentration
Chanel No5	https://example.com/chanel.jpg	Nước hoa nữ cao cấp	2500000	20		100ml	Nữ	CHANEL	EDP
Versace Pour Homme	https://example.com/versace.jpg	Nước hoa nam sang trọng	2500000	29		100ml	Nam	VERSACE	EDT
```

**Lưu ý**: Dùng Tab để phân cách các cột khi copy-paste vào Excel.

---

## 🆘 Xử lý lỗi thường gặp

### Lỗi 1: "Không tìm thấy category/brand/concentration"
**Nguyên nhân**: Tên ghi trong Excel không khớp với tên trong database

**Giải pháp**: 
- Kiểm tra tên chính xác trong menu Admin
- Copy-paste tên từ hệ thống vào Excel để tránh sai chính tả

### Lỗi 2: "File không đúng format"
**Nguyên nhân**: Thiếu cột hoặc tên cột sai

**Giải pháp**:
- Kiểm tra lại header có đủ 10 cột và đúng tên không
- Đảm bảo không có dòng trống giữa header và data

### Lỗi 3: "Giá trị không hợp lệ"
**Nguyên nhân**: Cột số (unit_price, sl_order, quantity) có ký tự chữ

**Giải pháp**:
- Chỉ ghi số, không ghi dấu phẩy, dấu chấm, chữ "đ"
- Format cột Excel thành "Number" (không phải "Text")

### Lỗi 4: Tiếng Việt hiển thị lỗi font
**Nguyên nhân**: File CSV không đúng encoding UTF-8

**Giải pháp**:
- Khi lưu CSV, chọn "CSV UTF-8 (Comma delimited)" trong Excel
- Hoặc dùng file `.xlsx` để tránh vấn đề encoding

---

## 📞 Liên hệ hỗ trợ
Nếu gặp vấn đề khi upload file, liên hệ Admin qua:
- Email: admin@example.com
- Hotline: 0900 xxx xxx

---

**Cập nhật lần cuối**: 22/06/2026
