<?php
// Cấu hình từ THESIEURE ní vừa gửi
$partner_id = '20336378768'; 
$partner_key = '393a56da5f75c71333e4bf3111deba8b';

// Nhận dữ liệu callback từ hệ thống
$status = $_POST['status']; // 1: Thành công, 2: Lỗi, 3: Sai mệnh giá
$amount = $_POST['amount']; // Mệnh giá thực của thẻ
$request_id = $_POST['request_id']; // Mã giao dịch ní gửi đi
$declared_value = $_POST['declared_value']; // Mệnh giá ní chọn lúc gửi

if (isset($status)) {
    // Kiểm tra chữ ký bảo mật để tránh nạp ảo
    $callback_sign = $_POST['callback_sign'];
    $sign = md5($partner_key . $_POST['code'] . $_POST['serial']);

    if ($status == 1) {
        // THÀNH CÔNG: Ghi log để ní biết ai đã nạp
        $log = "Giao dịch $request_id: THÀNH CÔNG - Số tiền: $amount VNĐ \n";
        file_put_contents("lich_su_nap.txt", $log, FILE_APPEND);
    } else {
        // THẤT BẠI hoặc SAI MỆNH GIÁ
        $log = "Giao dịch $request_id: THẤT BẠI (Status: $status) \n";
        file_put_contents("lich_su_nap.txt", $log, FILE_APPEND);
    }
}
?>
