<?php
// Thông tin ní cung cấp
$partner_id = '20336378768'; 
$partner_key = '393a56da5f75c71333e4bf3111deba8b';

$status = $_POST['status'];
$amount = $_POST['amount'];
$request_id = $_POST['request_id'];

if (isset($status)) {
    if ($status == 1) {
        // Nạp thành công
        file_put_contents("log_nap.txt", "ID: $request_id - Nap thanh cong: $amount \n", FILE_APPEND);
    }
}
?>
