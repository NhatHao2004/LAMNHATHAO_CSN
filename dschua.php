<?php
session_start();

// Kết nối CSDL
$conn = new mysqli('localhost', 'root', '', 'chua_khmer');
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy và kiểm tra ID
$current_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
if($current_id < 1 || $current_id > 12) {
    $current_id = 1;
}

// Chuẩn bị câu truy vấn cơ bản
$sql = "SELECT * FROM dschua WHERE trang_thai = 1";
$params = array();
$types = "";

// Chỉ tìm kiếm khi form được submit
if(isset($_GET['search'])) {
    // Tìm kiếm theo huyện
    if(isset($_GET['district']) && !empty($_GET['district'])) {
        $sql .= " AND LOWER(dia_chi) LIKE LOWER(?)";
        $params[] = "%" . $_GET['district'] . "%";
        $types .= "s";
    }

    // Tìm kiếm theo tên chùa - sử dụng LIKE để tìm gần đúng
    if(isset($_GET['temple_name']) && !empty($_GET['temple_name'])) {
        $sql .= " AND LOWER(ten_chua) LIKE LOWER(?)";
        $params[] = "%" . $_GET['temple_name'] . "%";
        $types .= "s";
    }
}

// Phân trang
$items_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Đếm tổng số kết quả
$count_sql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
$stmt = $conn->prepare($count_sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_items = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Thêm LIMIT và OFFSET vào câu truy vấn chính
$sql .= " ORDER BY id ASC LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$types .= "ii";

// Thực thi truy vấn chính
$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$temples = $result->fetch_all(MYSQLI_ASSOC);

// Danh sách các huyện cố định của Trà Vinh
$districts = array(
    "TP. Trà Vinh",
    "Huyện Càng Long",
    "Huyện Cầu Kè",
    "Huyện Tiểu Cần", 
    "Huyện Châu Thành",
    "Huyện Cầu Ngang",
    "Huyện Trà Cú",
    "Huyện Duyên Hải",
    "Thị xã Duyên Hải"
);

// Đóng các statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách chùa - Chùa Khmer Trà Vinh</title>
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
            max-width: 1400px;
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

        .logo-img:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 20px rgba(52,152,219,0.25);
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
            margin: 40px auto;
            padding: 0 30px;
        }

        .search-form {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
        }

        .search-fields {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .search-field {
            flex: 1;
        }

        select, input[type="text"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1.1rem;
            color: #2d3436;
            transition: all 0.3s ease;
        }

        select {
            appearance: none;
            background: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24'%3E%3Cpath fill='%232d3436' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 15px center/16px 16px;
        }

        select:focus, input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }

        button {
            padding: 15px 30px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52,152,219,0.3);
        }

        /* Container and Section Title */
        .container {
            max-width: 1300px;
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

        /* Temples Grid and Cards */
        .temples-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(600px, 1fr));
            gap: 50px;
            padding: 30px 0;
        }

        .temple-card {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.06);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            display: flex;
            flex-direction: row;
            max-height: 350px;
        }

        .temple-image {
            width: 45%;
            height: 350px;
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
        }

        .temple-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60%;
            background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);
        }

        .temple-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .temple-info {
            padding: 35px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            width: 55%;
            height: 100%;
        }

        .temple-title {
            font-size: 2rem;
            color: #2d3436;
            margin-bottom: 25px;
            font-weight: 700;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .temple-info p {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            color: #343a40;
            font-size: 1.3rem;
            line-height: 1.6;
        }

        .temple-info p i {
            color: #000000;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .temple-info p a {
            color: #000000;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .temple-actions {
            margin-top: auto;
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .read-more-btn {
            padding: 12px 25px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 50px 0;
        }

        .pagination a {
            padding: 12px 20px;
            background: white;
            color: #2d3436;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .pagination a.active {
            background: #3498db;
            color: white;
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
            max-width: 1300px;
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

        .contact-item a {
            color: #000000;
            font-size: 1.3rem;
            text-decoration: none;
            font-weight: bold;
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

            .temples-grid {
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
                    <li><a href="sukien.php">Lễ hội</a></li>
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
                    <input type="text" name="temple_name" id="templeSearch" placeholder="Nhập tên chùa..." 
                           value="<?php echo isset($_GET['temple_name']) ? htmlspecialchars($_GET['temple_name']) : ''; ?>">
                </div>
                <div class="search-field">
                    <select name="district" id="districtSearch">
                        <option value="">Tất cả huyện</option>
                        <?php foreach($districts as $district): ?>
                            <option value="<?php echo htmlspecialchars($district); ?>" 
                                    <?php echo (isset($_GET['district']) && $_GET['district'] == $district) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($district); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="search" id="searchButton">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
        </form>
    </div>

    <div class="container">
        <h2 class="section-title">Chùa Khmer</h2>
        
        <?php if (isset($_GET['search']) && empty($temples)): ?>
            <div class="no-temples" style="text-align: center; padding: 3rem; background: #f8f9fa; border-radius: 12px; margin: 2rem auto; max-width: 800px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;"></i>
                <div style="color: #6c757d;">
                    <strong style="display: block; font-size: 1.25rem; margin-bottom: 0.5rem; color: #343a40;">Không tìm thấy kết quả phù hợp</strong>
                    <p>Vui lòng thử lại với từ khóa khác hoặc điều chỉnh bộ lọc tìm kiếm của bạn.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="temples-grid">
                <?php foreach ($temples as $temple): ?>
                    <div class="temple-card">
                        <div class="temple-image">
                            <?php if (!empty($temple['hinh_anh'])): ?>
                                <img src="<?php echo htmlspecialchars($temple['hinh_anh']); ?>" 
                                     alt="<?php echo htmlspecialchars($temple['ten_chua']); ?>">
                            <?php else: ?>
                                <img src="assets/images/default-temple.jpg" alt="Default temple image">
                            <?php endif; ?>
                        </div>
                        <div class="temple-info">
                            <h3 class="temple-title">
                                <?php echo htmlspecialchars($temple['ten_chua']); ?>
                            </h3>
                            <p>
                                <a>Địa chỉ:</a>
                                <span><?php echo htmlspecialchars($temple['dia_chi']); ?></span>
                            </p>
                            <?php if (!empty($temple['dien_thoai'])): ?>
                                <p>
                                    <a>Số điện thoại:</a>
                                    <span><?php echo htmlspecialchars($temple['dien_thoai']); ?></span>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($temple['email'])): ?>
                                <p>
                                    <a>Email:</a>
                                    <span><?php echo htmlspecialchars($temple['email']); ?></span>
                                </p>
                            <?php endif; ?>
                            <div class="temple-actions">
                                <a href="chitietchua.php?id=<?php echo (int)$temple['id']; ?>" class="read-more-btn">
                                    Xem thêm tại đây
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo isset($_GET['district']) ? '&district='.urlencode($_GET['district']) : ''; ?><?php echo isset($_GET['temple_name']) ? '&temple_name='.urlencode($_GET['temple_name']) : ''; ?><?php echo isset($_GET['search']) ? '&search=1' : ''; ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?page=<?php echo $page-1; ?><?php echo isset($_GET['district']) ? '&district='.urlencode($_GET['district']) : ''; ?><?php echo isset($_GET['temple_name']) ? '&temple_name='.urlencode($_GET['temple_name']) : ''; ?><?php echo isset($_GET['search']) ? '&search=1' : ''; ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo isset($_GET['district']) ? '&district='.urlencode($_GET['district']) : ''; ?><?php echo isset($_GET['temple_name']) ? '&temple_name='.urlencode($_GET['temple_name']) : ''; ?><?php echo isset($_GET['search']) ? '&search=1' : ''; ?>" 
                           class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo isset($_GET['district']) ? '&district='.urlencode($_GET['district']) : ''; ?><?php echo isset($_GET['temple_name']) ? '&temple_name='.urlencode($_GET['temple_name']) : ''; ?><?php echo isset($_GET['search']) ? '&search=1' : ''; ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo isset($_GET['district']) ? '&district='.urlencode($_GET['district']) : ''; ?><?php echo isset($_GET['temple_name']) ? '&temple_name='.urlencode($_GET['temple_name']) : ''; ?><?php echo isset($_GET['search']) ? '&search=1' : ''; ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
                            <a style="display: inline-block; margin-right: 5px; white-space: nowrap;">Địa chỉ:</a>
                            <p style="display: inline-block; margin: 0; white-space: nowrap; color: #000000;">Xã Phong Phú, Huyện Cầu Kè, Tỉnh Trà Vinh</p>
                        </div>
                        <div class="contact-item">
                            <a style="display: inline-block; margin-right: 5px;">Số điện thoại:</a>
                            <p style="display: inline-block; margin: 0; color: #000000;">0337048780</p>
                        </div>
                        <div class="contact-item">
                            <a style="display: inline-block; margin-right: 5px;">Email:</a>
                            <p style="display: inline-block; margin: 0; color: #000000;">nhathao21112004@gmail.com</p>
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
        // Xóa sự kiện tự động submit form khi thay đổi
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            const districtSearch = document.getElementById('districtSearch');
            const templeSearch = document.getElementById('templeSearch');

            // Xóa nội dung tìm kiếm khi form được reset
            searchForm.addEventListener('reset', function() {
                window.location.href = window.location.pathname;
            });
        });
    </script>
</body>
</html>