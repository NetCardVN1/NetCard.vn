<?php
session_start();

// --- CẤU HÌNH DOITHE.VN ---
$partner_id = 'ID_CUA_NI';   // Thay bằng ID đối tác (Partner ID)
$partner_key = 'KEY_CUA_NI'; // Thay bằng Key đối tác (Partner Key)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['card_type']; // Viettel, Garena...
    $amount = $_POST['amount'];   // 10000, 20000...
    $serial = $_POST['serial'];
    $pin = $_POST['pin'];
    $request_id = rand(100000000, 999999999); // Mã đơn hàng ngẫu nhiên

    // 1. Kiểm tra khóa tài khoản 20 phút (Chống spam)
    if (isset($_SESSION['lock_until']) && time() < $_SESSION['lock_until']) {
        $wait = ceil(($_SESSION['lock_until'] - time()) / 60);
        die(json_encode(["status" => "error", "msg" => "Tài khoản bị khóa. Thử lại sau $wait phút!"]));
    }

    // 2. Tạo chữ ký bảo mật (Sign) theo chuẩn Doithe.vn
    // Công thức: md5(partner_key + pin + serial)
    $sign = md5($partner_key . $pin . $serial);

    // 3. Gửi dữ liệu bằng cURL (Dùng phương thức GET hoặc POST tùy NPH)
    $url = "https://doithe.vn/chargingws/v2?sign=$sign&id=$partner_id&code=$pin&serial=$serial&telco=$type&amount=$amount&request_id=$request_id";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    // 4. Xử lý phản hồi trả về từ hệ thống
    if (isset($result['status'])) {
        if ($result['status'] == 1) { 
            // ✅ THẺ ĐÚNG: Hệ thống đã nhận thẻ và đang xử lý
            $_SESSION['wrong_count'] = 0;
            echo json_encode([
                "status" => "success", 
                "msg" => "Gửi thẻ thành công! Vui lòng đợi 1-3 phút để hệ thống kiểm tra.",
                "pin" => $pin,
                "serial" => $serial
            ]);
        } 
        else if ($result['status'] == 2) {
            echo json_encode(["status" => "error", "msg" => "Lỗi: Sai mệnh giá thẻ!"]);
        }
        else {
            // ❌ THẺ SAI: Cộng dồn số lần nhập sai
            $_SESSION['wrong_count'] = ($_SESSION['wrong_count'] ?? 0) + 1;
            
            if ($_SESSION['wrong_count'] >= 3) {
                $_SESSION['lock_until'] = time() + (20 * 60); // Khóa 20 phút
                echo json_encode(["status" => "error", "msg" => "Bạn đã nhập sai 3 lần! Tài khoản bị khóa 20 phút."]);
            } else {
                echo json_encode(["status" => "error", "msg" => "Mã thẻ hoặc Serial không đúng! (" . $_SESSION['wrong_count'] . "/3)"]);
            }
        }
    } else {
        echo json_encode(["status" => "error", "msg" => "Không thể kết nối đến Doithe.vn!"]);
    }
}
?>

