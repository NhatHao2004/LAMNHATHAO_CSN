<?php
session_start();
require_once('connect.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_dang_nhap = $_POST['ten_dang_nhap'];
    $mat_khau = $_POST['mat_khau'];
    $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'];
    $ho_ten = $_POST['ho_ten'];
    $email = $_POST['email'];

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM nguoi_dung WHERE ten_dang_nhap = ?");
        $stmt->execute([$ten_dang_nhap]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Tên đăng nhập đã tồn tại!';
        }
        else {
            $hashed_password = password_hash($mat_khau, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ten_dang_nhap, $hashed_password, $ho_ten, $email]);
            
            $success = 'Đăng ký thành công!';
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'dangnhap.php';
                }, 2000);
            </script>";
        }
    } catch(PDOException $e) {
        $error = 'Có lỗi xảy ra, vui lòng thử lại sau!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Chùa Khmer Trà Vinh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    body {
        background: #f5f6fa;  /* Thay đổi màu nền */
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .container {
        max-width: 500px;  /* Tăng độ rộng container */
        width: 100%;
        background: #fff;
        padding: 30px;  /* Tăng padding */
        border-radius: 15px;  /* Tăng border radius */
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);  /* Điều chỉnh shadow nhẹ nhàng hơn */
        margin: auto;
    }

    .title {
        font-size: 28px;  /* Tăng kích thước chữ */
        font-weight: 600;  /* Tăng độ đậm */
        text-align: center;
        margin-bottom: 30px;  /* Tăng margin bottom */
        color: #1a1a1a;  /* Màu chữ đậm hơn */
    }

    .form-control {
        height: 45px;  /* Tăng chiều cao input */
        font-size: 16px;
        border-radius: 8px;  /* Bo tròn góc input */
        border: 1px solid #ddd;
        padding: 0 15px;
    }

    .form-control:focus {
        border-color: #71b7e6;
        box-shadow: 0 0 0 0.2rem rgba(113, 183, 230, 0.25);
    }

    .form-group {
        margin-bottom: 20px;  /* Tăng khoảng cách giữa các form group */
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
        color: #333;
    }

    .btn-register {
        height: 48px;  /* Tăng chiều cao button */
        width: 100%;
        background: linear-gradient(135deg, #71b7e6, #9b59b6);
        border: none;
        color: #fff;
        font-size: 18px;  /* Tăng kích thước chữ */
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .btn-register:hover {
        background: linear-gradient(135deg, #9b59b6, #71b7e6);
        transform: translateY(-2px);
    }

    .error-message, .success-message {
        padding: 12px;  /* Tăng padding */
        margin-bottom: 20px;
        border-radius: 8px;
        text-align: center;
        font-weight: 500;
    }

    @media (max-width: 576px) {
        .container {
            margin: 20px;
            padding: 20px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="title">Đăng ký tài khoản</div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="ho_ten">Họ và tên:</label>
                <input type="text" class="form-control" id="ho_ten" name="ho_ten" required>
            </div>

            <div class="form-group">
                <label for="ten_dang_nhap">Tên đăng nhập:</label>
                <input type="text" class="form-control" id="ten_dang_nhap" name="ten_dang_nhap" required minlength="3" maxlength="50">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="mat_khau">Mật khẩu:</label>
                <input type="password" class="form-control" id="mat_khau" name="mat_khau" required minlength="6">
            </div>

            <div class="form-group">
                <label for="xac_nhan_mat_khau">Xác nhận mật khẩu:</label>
                <input type="password" class="form-control" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" required>
            </div>

            <button type="submit" class="btn-register">Đăng ký</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script>
    function validateForm() {
        var password = document.getElementById("mat_khau").value;
        var confirm_password = document.getElementById("xac_nhan_mat_khau").value;
        
        if (password !== confirm_password) {
            alert("Mật khẩu xác nhận không khớp!");
            return false;
        }
        return true;
    }
    </script>
</body>
</html>
