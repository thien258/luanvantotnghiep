/**
 * order-show.js
 * Xử lý in tem vận chuyển + QR cho trang chi tiết đơn hàng admin
 */
function printShippingLabel() {
    var btn     = document.getElementById('btn-print-label');
    var confirmUrl   = btn.dataset.confirmUrl;
    var trackingCode = btn.dataset.trackingCode;
    var orderId      = btn.dataset.orderId;
    var fullname     = btn.dataset.fullname;
    var phone        = btn.dataset.phone;
    var address      = btn.dataset.address;
    var cod          = btn.dataset.cod;

    var qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(confirmUrl);

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
        + '</div>'
        + '</body></html>';

    var win = window.open('', '_blank', 'width=500,height=700');
    win.document.write(html);
    win.document.close();

    var img = win.document.querySelector('img');
    img.onload  = function() { win.focus(); win.print(); };
    img.onerror = function() { win.focus(); win.print(); };
}
