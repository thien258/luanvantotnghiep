/**
 * order-show.js
 * Xử lý in tem vận chuyển + QR cho trang chi tiết đơn hàng admin
 */
function printShippingLabel() {
    var btn          = document.getElementById('btn-print-label');
    var confirmUrl   = btn.dataset.confirmUrl;
    var trackingCode = btn.dataset.trackingCode;
    var orderId      = btn.dataset.orderId;
    var fullname     = btn.dataset.fullname;
    var phone        = btn.dataset.phone;
    var address      = btn.dataset.address;
    var cod          = btn.dataset.cod;
    var items        = [];
    try { items = JSON.parse(btn.dataset.items || '[]'); } catch(e) {}

    var qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(confirmUrl);

    // Build danh sách sản phẩm
    var itemsHtml = '';
    if (items.length > 0) {
        itemsHtml += '<div class="products">'
            + '<div class="prod-title">Sản phẩm:</div>'
            + '<table>'
            + '<thead><tr><th>Tên sản phẩm</th><th>SL</th></tr></thead>'
            + '<tbody>';
        items.forEach(function(item) {
            itemsHtml += '<tr>'
                + '<td>' + item.name + '</td>'
                + '<td class="qty">' + item.qty + '</td>'
                + '</tr>';
        });
        itemsHtml += '</tbody></table></div>';
    }

    var html = '<!DOCTYPE html>'
        + '<html><head><meta charset="UTF-8">'
        + '<style>'
        + 'body{margin:0;display:flex;justify-content:center;align-items:flex-start;padding:30px;background:#fff;}'
        + '.box{width:360px;border:2px dashed #000;padding:24px;font-family:"Courier New",monospace;text-align:center;color:#000;}'
        + 'h2{font-size:1.2rem;text-transform:uppercase;letter-spacing:1px;margin:0 0 4px;}'
        + '.sub{font-size:0.7rem;letter-spacing:1px;border-bottom:1px solid #000;padding-bottom:8px;margin-bottom:16px;}'
        + '.qr img{width:180px;height:180px;}'
        + '.code{font-size:0.75rem;font-weight:bold;margin-top:6px;letter-spacing:1px;}'
        + '.info{text-align:left;font-size:0.8rem;border-top:1px solid #000;padding-top:10px;margin-top:16px;line-height:1.9;}'
        + '.info p{margin:0;}'
        + '.products{text-align:left;margin-top:12px;border-top:1px dashed #000;padding-top:10px;}'
        + '.prod-title{font-size:0.72rem;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;}'
        + '.products table{width:100%;border-collapse:collapse;font-size:0.78rem;}'
        + '.products th{border-bottom:1px solid #000;padding:2px 4px;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;}'
        + '.products td{padding:3px 4px;border-bottom:1px dashed #ccc;vertical-align:top;}'
        + '.products td.qty{text-align:center;font-weight:bold;width:30px;}'
        + '</style>'
        + '</head><body>'
        + '<div class="box">'
        + '<h2>ATELIER SCENT</h2>'
        + '<p class="sub">TEM VẬN CHUYỂN ĐƠN HÀNG</p>'
        + '<div class="qr"><img src="' + qrSrc + '" width="180" height="180"></div>'
        + '<div class="code">' + trackingCode + '</div>'
        + '<div class="info">'
        + '<p><strong>Mã đơn:</strong> #DH' + orderId + '</p>'
        + '<p><strong>Khách hàng:</strong> ' + fullname + '</p>'
        + '<p><strong>SĐT:</strong> ' + phone + '</p>'
        + '<p><strong>Địa chỉ:</strong> ' + address + '</p>'
        + '<p><strong>Thu hộ:</strong> ' + cod + '</p>'
        + '</div>'
        + itemsHtml
        + '</div>'
        + '</body></html>';

    var win = window.open('', '_blank', 'width=500,height=750');
    win.document.write(html);
    win.document.close();

    var img = win.document.querySelector('img');
    img.onload  = function() { win.focus(); win.print(); };
    img.onerror = function() { win.focus(); win.print(); };
}
