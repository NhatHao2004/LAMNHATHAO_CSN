<?php
session_start();
require_once('connect.php');

$message = '';
$redirect = false;

// Xử lý gửi liên hệ mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gui_lien_he'])) {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $dien_thoai = trim($_POST['dien_thoai']);
    $noi_dung = trim($_POST['noi_dung']);

    try {
        $sql = "INSERT INTO lien_he (ho_ten, email, dien_thoai, chu_de, noi_dung) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if($stmt->execute([$ho_ten, $email, $dien_thoai, $chu_de, $noi_dung])) {
            $message = 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ sớm gửi lời phản hồi đến bạn';
            $redirect = true;
        } else {
            $message = 'Có lỗi xảy ra, vui lòng thử lại sau.';
        }
    } catch(PDOException $e) {
        $message = 'Có lỗi xảy ra: ' . $e->getMessage();
    }
}

// Lấy danh sách liên hệ (chỉ cho admin)
$lien_he_list = [];
if (isset($_SESSION['admin'])) {
    $sql = "SELECT * FROM lien_he ORDER BY ngay_gui DESC";
    $result = $conn->query($sql);
    $lien_he_list = $result->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - Chùa Khmer Trà Vinh</title>
    <style>
    body {
        background: #fff;
        margin: 0;
        padding: 20px;
    }

    .contact-container {
        max-width: 800px;
        width: 100%;
        background: #fff;
        padding: 0 25px 25px 25px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        margin: 0 auto;
    }

    .section-title {
        font-size: 24px;
        font-weight: 600;
        text-align: center;
        margin-bottom: 25px;
        color: #333;
    }

    .contact-info {
        margin: 20px 0 25px;
        padding: 1px 15px;
        border-radius: 8px;
    }

    .contact-info h3 {
        font-size: 40px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #000;
        text-align: center;
    }

    .contact-info p {
        margin: 10px 0;
        color: #000;
        font-size: 18px;
        line-height: 1.0;
    }

    .contact-info p strong {
        font-weight: bold;
        color: black;
        margin-right: 8px;
        font-size: 18px;
    }

    .btn-submit {
        height: 40px;
        width: 100%;
        background: linear-gradient(135deg, #71b7e6, #9b59b6);
        border: none;
        color: #fff;
        font-size: 16px;
        font-weight: bold;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: linear-gradient(-135deg, #71b7e6, #9b59b6);
    }

    .message {
        padding: 15px 20px;  /* Tăng padding */
        margin: 15px 0;      /* Tăng margin */
        border-radius: 4px;
        font-size: 16px;     /* Tăng font size */
        font-weight: 500;    /* Thêm độ đậm vừa phải */
        text-align: center;  /* Căn giữa text */
    }

    .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Style hiện tại của bạn */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group input,
    .form-group textarea {
        width: 98%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: Arial, sans-serif;
        font-weight: normal;
        font-size: 14px;
    }

    .form-group textarea {
        height: 100px;
        resize: vertical;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    /* Bỏ hiệu ứng focus */
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;  /* Bỏ viền mặc định của trình duyệt */
        border-color: #ddd;  /* Giữ nguyên màu viền như ban đầu */
    }
        
    @media (max-width: 520px) {
        .contact-container, body {
            padding: 20px;
        }
    }
    </style>
</head>
<body>
    <!-- Sửa lại phần HTML -->
<div class="contact-container">
    <div class="contact-info">
        <h3>Liên hệ</h3>
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>

        <div class="contact-form">
            <form method="POST" action="">
                <input type="hidden" name="gui_lien_he" value="1">
                <div class="form-group">
                    <label for="ho_ten">Họ và tên:</label>
                    <input type="text" id="ho_ten" name="ho_ten" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="dien_thoai">Số điện thoại:</label>
                    <input type="tel" id="dien_thoai" name="dien_thoai" pattern="[0-9]{10,11}" title="Vui lòng nhập số điện thoại từ 10-11 số">
                </div>

                <div class="form-group">
                    <label for="noi_dung">Nội dung:</label>
                    <textarea id="noi_dung" name="noi_dung" required></textarea>
                </div>

                <button type="submit" class="btn-submit">Gửi liên hệ</button>
            </form>
        </div>

        <!-- Phần quản lý cho admin -->
        <?php if (isset($_SESSION['admin'])): ?>
        <div class="admin-section">
            <h2>Quản lý liên hệ</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Nội dung</th>
                        <th>Ngày gửi</th>
                        <th>Cập nhật lần cuối</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lien_he_list as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['ho_ten']) ?></td>
                        <td><?= htmlspecialchars($item['email']) ?></td>
                        <td><?= htmlspecialchars($item['dien_thoai']) ?></td>
                        <td><?= htmlspecialchars($item['noi_dung']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($item['ngay_gui'])) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($item['ngay_cap_nhat'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($redirect): ?>
    <script>
        setTimeout(() => window.location.href = 'index.php', 2000);
    </script>
    <?php endif; ?>
</body>
</html>
