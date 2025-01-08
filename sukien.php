<?php
session_start();

// Kết nối CSDL
$conn = new mysqli('localhost', 'root', '', 'chua_khmer');
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý tìm kiếm
$search_term = '';
if(isset($_GET['search'])) {
    $search_term = trim($_GET['search']);
    $sql = "SELECT * FROM su_kien WHERE trang_thai = 1 AND ten_su_kien LIKE ? ORDER BY ngay_tao ASC";
    $stmt = $conn->prepare($sql);
    $search_param = "%$search_term%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Lấy tất cả sự kiện nếu không có tìm kiếm
    $sql = "SELECT * FROM su_kien WHERE trang_thai = 1 ORDER BY ngay_tao ASC";
    $result = $conn->query($sql);
}

// Lưu kết quả vào mảng
$su_kien_list = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $su_kien_list[] = $row;
    }
}

$lehoiList = [
    "chitietchua.php?id=1" => "Lễ hội Chol Chnam Thmay",
];

// Xử lý thêm sự kiện mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin'])) {
    $ten_su_kien = trim(htmlspecialchars($_POST['ten_su_kien'] ?? ''));

    // Xử lý upload ảnh
    $hinh_anh = '';
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/sukien/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $upload_path)) {
            $hinh_anh = $upload_path;
        }
    }

    // Cập nhật câu lệnh SQL để thêm trường hinh_anh
    $stmt = $conn->prepare("INSERT INTO su_kien (ten_su_kien, y_nghia, thoi_gian_to_chuc, 
                           cac_nghi_thuc, am_thuc_truyen_thong, luu_y_khach_tham_quan, 
                           hinh_anh, trang_thai, ngay_tao) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");
    
    $stmt->bind_param("sssssss", $ten_su_kien, $y_nghia, $thoi_gian_to_chuc,
                      $cac_nghi_thuc, $am_thuc_truyen_thong, $luu_y_khach_tham_quan, $hinh_anh);
        
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Thêm sự kiện thành công!";
        header("Location: QTVindex.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Lỗi khi thêm sự kiện: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lễ hội - Chùa Khmer Trà Vinh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            background-color: #f8f9fa;
            color: #343a40;
        }

        /* Header Styles */
        .main-header {
            background: linear-gradient(135deg, #ffffff, #f0f2f5);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 15px 0;
            border-bottom: 2px solid rgba(52,152,219,0.1);
        }

        .header-content {
            max-width: 1800px;
            margin: 0 auto;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-group {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 60%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .logo-group h1 {
            font-size: 2.2rem;
            color: #2d3436;
            font-weight: 800;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .main-nav ul {
            display: flex;
            gap: 50px;
            list-style: none;
        }

        .main-nav a {
            text-decoration: none;
            color: #2d3436;
            font-weight: 600;
            font-size: 1.2rem;
            padding: 12px 0;
            position: relative;
            transition: color 0.3s ease;
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(to right, #3498db, #2980b9);
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        /* Search Container */
        .search-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .search-form {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }

        .search-fields {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-field {
            flex: 1;
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            color: #2d3436;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }

        button {
            padding: 10px 20px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Container and Section Title */
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .section-title {
            font-size: 2.5rem;
            color: #2d3436;
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        /* Cultural Events */
        .cultural-events {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Thay đổi thành 3 cột cố định */
            gap: 30px;
            padding: 20px;
        }

        .event-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .event-image {
            width: 100%;
            height: 400px;
            overflow: hidden;
        }

        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .event-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .event-title {
            font-size: 1.5rem;
            color: #2d3436;
            margin-bottom: 15px;
        }

        .event-content {
            padding: 20px;
            background: #f8f9fa;
        }

        .event-content .mb-3 {
            margin-bottom: 20px;
        }

        .event-content strong {
            display: block;
            color: #2d3436;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .event-content p {
            color: #666;
            line-height: 1.6;
        }

        .read-more-btn {
            width: 50%;
            padding: 15px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            margin-left: 25%;
        }

        .read-more-btn:hover {
            background: linear-gradient(135deg, #2980b9, #2573a7);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52,152,219,0.3);
        }

        /* Footer */
        .main-footer {
            background-color: #f8f9fa; 
            color: #343a40;
            padding: 80px 0 40px;
            margin-top: 100px;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
        }

        .footer-content {
            max-width: 1800px;
            margin: 0 auto;
            padding: 0 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 60px;
        }

        .footer-section h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
            letter-spacing: 0.5px;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #3498db;
            border-radius: 2px;
        }

        .footer-content-wrapper {
            line-height: 2;
            font-size: 1.3rem;
            opacity: 0.9;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 10px 0;
            transition: transform 0.3s ease;
        }

        .contact-item p {
            color: #000000;
            font-size: 1.3rem;
            text-decoration: none;
        }

        .footer-bottom {
            margin-top: 70px;
            padding-top: 35px;
            border-top: 1px solid rgba(255,255,255,0.15);
            text-align: center;
            font-size: 1.1rem;
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container, .header-content, .search-container {
                padding: 0 20px;
            }
            
            .cultural-events {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .logo-group {
                justify-content: center;
            }

            .main-nav ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
            }

            .search-fields {
                flex-direction: column;
            }

            .cultural-events {
                grid-template-columns: 1fr;
            }

            .temple-card {
                flex-direction: column;
                max-height: none;
            }

            .temple-image {
                width: 100%;
                height: 250px;
            }

            .temple-info {
                width: 100%;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-section h3::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .contact-item {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .temple-title {
                font-size: 1.5rem;
            }

            .temple-info p {
                font-size: 1rem;
            }

            .temple-actions {
                flex-direction: column;
            }

            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<header class="main-header">
    <div class="header-content">
        <div class="logo-group">
            
                <img src="image/Logo/logo.jpg" alt="Logo Chùa Khmer" class="logo-img">
                <h1>Chùa Khmer Trà Vinh</h1>
        </div>

        <div class="nav-group">
            <nav class="main-nav">
                <ul>
                    <li><a href="Index.php">Trang chủ</a></li>
                    <li><a href="dschua.php">Chùa Khmer</a></li>
                    <li><a href="taikhoan.php">Tài khoản</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<div class="main-content">
    <!-- Form tìm kiếm -->
    <div class="search-container">
        <form id="searchForm" class="search-form" method="GET" action="">
            <div class="search-fields">
                <div class="search-field">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Nhập tên lễ hội cần tìm..." 
                           value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <button type="submit">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
        </form>
    </div>

<div class="container">
    <h2 class="section-title">Lễ Hội</h2>

<?php if (empty($su_kien_list)): ?>
    <div class="no-temples" style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 15px; margin: 20px auto; max-width: 800px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 20px; display: block;"></i>
        <strong style="font-size: 1.2rem; color: #6c757d; display: block; color: #000000;">Thông tin lễ hội đã tạm ẩn hoặc không tồn tại trong hệ thống</strong>
    </div>
<?php else: ?>
    <div class="cultural-events">
        <?php foreach ($su_kien_list as $su_kien): ?>
            <div class="event-card">
                <div class="event-image">
                    <?php if (!empty($su_kien['hinh_anh'])): ?>
                        <img src="<?php echo htmlspecialchars($su_kien['hinh_anh']); ?>" 
                             alt="<?php echo htmlspecialchars($su_kien['ten_su_kien']); ?>">
                    <?php else: ?>
                        <img src="images/default-event.jpg" alt="Hình ảnh mặc định">
                    <?php endif; ?>
                </div>
                
                <div class="event-header">
                    <h3 class="event-title"><?php echo htmlspecialchars($su_kien['ten_su_kien']); ?></h3>
                    <button class="read-more-btn" onclick="toggleContent(this)">Xem thêm</button>
                </div>

                <div class="event-content" style="display: none;">
                    <div class="mb-3">
                        <strong>Ý nghĩa</strong>
                        <p><?php echo nl2br(htmlspecialchars($su_kien['y_nghia'])); ?></p>
                    </div>
                    <div class="mb-3">
                        <strong>Thời gian tổ chức</strong>
                        <p><?php echo nl2br(htmlspecialchars($su_kien['thoi_gian_to_chuc'])); ?></p>
                    </div>  
                    <div class="mb-3">
                        <strong>Các nghi thức</strong>
                        <p><?php echo nl2br(htmlspecialchars($su_kien['cac_nghi_thuc'])); ?></p>
                    </div>
                    <div class="mb-3">
                        <strong>Ẩm thực truyền thống</strong>
                        <p><?php echo nl2br(htmlspecialchars($su_kien['am_thuc_truyen_thong'])); ?></p>
                    </div>
                    <div class="mb-3">
                        <strong>Lưu ý</strong>
                        <p><?php echo nl2br(htmlspecialchars($su_kien['luu_y_khach_tham_quan'])); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>

<footer class="main-footer">
        <div class="footer-content">
            <!-- Giới thiệu -->
            <div class="footer-section">
                <h3>Về Chùa Khmer</h3>
                <div class="footer-content-wrapper">
                    <p>Khám phá vẻ đẹp và giá trị văn hóa độc đáo của các ngôi chùa Khmer tại Trà Vinh.</p>
                    <p>Chúng tôi cam kết bảo tồn và phát huy di sản văn hóa quý báu này.</p>
                </div>
            </div>

            <!-- Thông tin liên hệ -->
            <div class="footer-section">
                <h3>Liên Hệ</h3>
                <div class="footer-content-wrapper">
                    <div class="contact-info">
                        <div class="contact-item">
                            <a style="display: inline-block; margin-right: 5px; white-space: nowrap; font-weight: bold;">Địa chỉ:</a>
                            <p style="display: inline-block; margin: 0; white-space: nowrap;">Xã Phong Phú, Huyện Cầu Kè, Tỉnh Trà Vinh</p>
                        </div>
                        <div class="contact-item">
                            <a style="display: inline-block; margin-right: 5px; white-space: nowrap; font-weight: bold;">Số điện thoại:</a>
                            <p style="display: inline-block; margin: 0; white-space: nowrap;">0337048780</p>
                        </div>
                        <div class="contact-item">
                            <a style="display: inline-block; margin-right: 5px; white-space: nowrap; font-weight: bold;">Email:</a>
                            <p style="display: inline-block; margin: 0; white-space: nowrap;">nhathao21112004@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> Chùa Khmer Trà Vinh.</p>
            </div>
        </div>
    </footer>

<script>
function toggleContent(button) {
    const card = button.closest('.event-card');
    const content = button.parentElement.nextElementSibling;
    
    if (content.style.display === "none") {
        // Thu gọn tất cả các card khác
        document.querySelectorAll('.event-card').forEach(otherCard => {
            if (otherCard !== card) {
                otherCard.classList.remove('expanded');
                const otherContent = otherCard.querySelector('.event-content');
                const otherButton = otherCard.querySelector('.read-more-btn');
                if (otherContent) {
                    otherContent.style.display = "none";
                }
                if (otherButton) {
                    otherButton.textContent = "Xem thêm";
                }
            }
        });
        
        // Mở rộng card hiện tại
        content.style.display = "block";
        button.textContent = "Thu gọn";
        card.classList.add('expanded');
    } else {
        // Thu gọn card hiện tại
        content.style.display = "none";
        button.textContent = "Xem thêm";
        card.classList.remove('expanded');
    }
}
</script>
</body>
</html>