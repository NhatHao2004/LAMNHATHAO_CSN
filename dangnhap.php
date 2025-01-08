<?php
session_start();
require_once('connect.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_dang_nhap = $_POST['ten_dang_nhap'];
    $mat_khau = $_POST['mat_khau'];

    try {
        $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE ten_dang_nhap = ?");
        $stmt->execute([$ten_dang_nhap]);
        $user = $stmt->fetch();

        if ($user && password_verify($mat_khau, $user['mat_khau'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['ho_ten'];
            $_SESSION['vai_tro'] = $user['vai_tro'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không chính xác';
        }
    } catch(PDOException $e) {
        $error = 'Có lỗi xảy ra: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px; /* Giữ nguyên max-width 400px như dangnhapAdmin.php */
        }
        .title {
        text-align: center;
        color: #000; /* Change color to black */
        margin-bottom: 30px;
        font-weight: bold;
        font-size: 24px;
    }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            height: auto;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            box-sizing: border-box;
            height: auto;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #9b59b6, #71b7e6);
            transform: translateY(-2px);
        }
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin: 0 0 15px 0;  /* Điều chỉnh margin */
            padding: 8px;  /* Thêm padding */
            background-color: rgba(231, 76, 60, 0.1);  /* Thêm màu nền */
            border: 1px solid #e74c3c;  /* Thêm viền */
            border-radius: 5px;  /* Bo góc */
            font-weight: 500;  /* Làm đậm chữ một chút */
            white-space: nowrap;  /* Giữ text trên cùng một dòng */
            overflow: hidden;  /* Ẩn phần text bị tràn */
            text-overflow: ellipsis;  /* Hiển thị dấu ... nếu text quá dài */
        }
        .text-center {
            text-align: center;
            margin-top: 20px;
        }
        a {
            color: #1e3c72;
            text-decoration: none;
        }
        a:hover {
        text-decoration: none;
    }
        @media (max-width: 576px) {
            .container {
                margin: 10px;
                padding: 20px;
                width: calc(100% - 20px);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">Đăng nhập</div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="ten_dang_nhap">Tên đăng nhập</label>
                <input type="text" class="form-control" id="ten_dang_nhap" name="ten_dang_nhap" required>
            </div>

            <div class="form-group">
                <label for="mat_khau">Mật khẩu</label>
                <input type="password" class="form-control" id="mat_khau" name="mat_khau" required>
            </div>

            <button type="submit" class="btn-login">Đăng nhập</button>

            <p class="text-center">
                Chưa có tài khoản / <a href="dangky.php">Đăng ký ngay</a>
            </p>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>