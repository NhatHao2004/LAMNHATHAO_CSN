<?php
session_start();
$error_message = '';
$success_message = '';
$is_logged_in = isset($_SESSION['user_id']);
$featured_temples = [];

// Database connection
try {
    $db_config = [
        'host' => 'localhost',
        'dbname' => 'chua_khmer',
        'username' => 'root',
        'password' => ''
    ];
    
    $conn = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password']
    );
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Không thể kết nối đến database. Vui lòng thử lại sau.");
}


/// Sửa lại câu truy vấn SQL để lấy bình luận và phản hồi
$stmt = $conn->prepare("
SELECT DISTINCT
    bl.id,
    bl.noi_dung,
    bl.ngay_tao,
    bl.ngay_cap_nhat,
    bl.phan_hoi,
    bl.id_nguoi_dung,
    bl.trang_thai,
    nd.ho_ten,
    nd.avatar as user_avatar,
    bl.id_binh_luan_goc
FROM binh_luan bl
LEFT JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
WHERE bl.id_binh_luan_goc IS NULL
    AND bl.trang_thai = 1
ORDER BY bl.ngay_tao DESC
");
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy các phản hồi cho mỗi bình luận
foreach ($comments as &$comment) {
$stmt = $conn->prepare("
    SELECT DISTINCT
        bl.id,
        bl.noi_dung,
        bl.ngay_tao,
        bl.ngay_cap_nhat,
        bl.phan_hoi,
        bl.id_nguoi_dung,
        bl.trang_thai,
        nd.ho_ten,
        nd.avatar as user_avatar,
        bl.id_binh_luan_goc
    FROM binh_luan bl
    LEFT JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
    WHERE bl.id_binh_luan_goc = ?
        AND bl.trang_thai = 1
    ORDER BY bl.ngay_tao ASC
");
$stmt->execute([$comment['id']]);
$comment['replies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Xử lý chỉnh sửa bình luận
if (isset($_POST['action']) && $_POST['action'] === 'edit_comment') {
    try {
        if (!$is_logged_in) {
            throw new Exception("Vui lòng đăng nhập để chỉnh sửa bình luận");
        }

        $comment_id = (int)$_POST['comment_id'];
        $noi_dung = trim($_POST['noi_dung']);
        $user_id = $_SESSION['user_id'];

        if (empty($noi_dung)) {
            throw new Exception("Nội dung bình luận không được để trống");
        }

        // Kiểm tra quyền chỉnh sửa và lấy thông tin bình luận
        $stmt = $conn->prepare("
            SELECT bl.*, nd.ho_ten 
            FROM binh_luan bl
            JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
            WHERE bl.id = ? AND bl.id_nguoi_dung = ?
        ");
        $stmt->execute([$comment_id, $user_id]);
        $comment = $stmt->fetch();
        
        if (!$comment) {
            throw new Exception("Bạn không có quyền chỉnh sửa bình luận này");
        }

        // Cập nhật bình luận
        $stmt = $conn->prepare("
            UPDATE binh_luan 
            SET noi_dung = ?, 
                ngay_cap_nhat = NOW() 
            WHERE id = ? AND id_nguoi_dung = ?
        ");
        
        if ($stmt->execute([$noi_dung, $comment_id, $user_id])) {
            // Lấy thời gian cập nhật mới
            $stmt = $conn->prepare("
                SELECT DATE_FORMAT(ngay_cap_nhat, '%d/%m/%Y %H:%i') as ngay_cap_nhat
                FROM binh_luan WHERE id = ?
            ");
            $stmt->execute([$comment_id]);
            $update_time = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật bình luận thành công',
                'comment' => [
                    'id' => $comment_id,
                    'noi_dung' => nl2br(htmlspecialchars($noi_dung)),
                    'ngay_cap_nhat' => $update_time['ngay_cap_nhat'],
                    'ho_ten' => $comment['ho_ten']
                ]
            ]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Xử lý chỉnh sửa phản hồi
if (isset($_POST['action']) && $_POST['action'] === 'edit_reply') {
    try {
        if (!$is_logged_in) {
            throw new Exception("Vui lòng đăng nhập để chỉnh sửa phản hồi");
        }

        $reply_id = (int)$_POST['reply_id'];
        $noi_dung = trim($_POST['noi_dung']);
        $user_id = $_SESSION['user_id'];

        if (empty($noi_dung)) {
            throw new Exception("Nội dung phản hồi không được để trống");
        }

        // Kiểm tra quyền chỉnh sửa và lấy thông tin phản hồi
        $stmt = $conn->prepare("
            SELECT bl.*, nd.ho_ten 
            FROM binh_luan bl
            JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
            WHERE bl.id = ? AND bl.id_nguoi_dung = ?
        ");
        $stmt->execute([$reply_id, $user_id]);
        $reply = $stmt->fetch();
        
        if (!$reply) {
            throw new Exception("Bạn không có quyền chỉnh sửa phản hồi này");
        }

        // Cập nhật phản hồi
        $stmt = $conn->prepare("
            UPDATE binh_luan 
            SET noi_dung = ?, 
                ngay_cap_nhat = NOW() 
            WHERE id = ? AND id_nguoi_dung = ?
        ");
        
        if ($stmt->execute([$noi_dung, $reply_id, $user_id])) {
            // Lấy thời gian cập nhật mới
            $stmt = $conn->prepare("
                SELECT DATE_FORMAT(ngay_cap_nhat, '%d/%m/%Y %H:%i') as ngay_cap_nhat
                FROM binh_luan WHERE id = ?
            ");
            $stmt->execute([$reply_id]);
            $update_time = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật phản hồi thành công',
                'reply' => [
                    'id' => $reply_id,
                    'noi_dung' => nl2br(htmlspecialchars($noi_dung)),
                    'ngay_cap_nhat' => $update_time['ngay_cap_nhat'],
                    'ho_ten' => $reply['ho_ten']
                ]
            ]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Xử lý xóa bình luận
if (isset($_POST['action']) && $_POST['action'] === 'delete_comment') {
    try {
        if (!$is_logged_in) {
            throw new Exception("Vui lòng đăng nhập để xóa bình luận");
        }

        $comment_id = (int)$_POST['comment_id'];
        $user_id = $_SESSION['user_id'];

        // Kiểm tra quyền xóa
        $stmt = $conn->prepare("
            SELECT id_nguoi_dung 
            FROM binh_luan 
            WHERE id = ? AND id_nguoi_dung = ?
        ");
        $stmt->execute([$comment_id, $user_id]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Bạn không có quyền xóa bình luận này");
        }

        // Xóa bình luận và các phản hồi liên quan
        $stmt = $conn->prepare("
            DELETE FROM binh_luan 
            WHERE id = ? OR id_binh_luan_goc = ?
        ");
        
        if ($stmt->execute([$comment_id, $comment_id])) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa bình luận thành công'
            ]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Xử lý gửi bình luận mới
if (isset($_POST['action']) && $_POST['action'] === 'comment') {
    try {
        if (!$is_logged_in) {
            throw new Exception("Vui lòng đăng nhập để bình luận");
        }

        $nguoi_dung_id = $_SESSION['user_id'];
        $noi_dung = trim($_POST['noi_dung']);
        
        if (empty($noi_dung)) {
            throw new Exception("Nội dung bình luận không được để trống");
        }

        $stmt = $conn->prepare("
            INSERT INTO binh_luan (
                id_nguoi_dung, 
                noi_dung, 
                ngay_tao,
                trang_thai
            ) VALUES (?, ?, NOW(), 1)
        ");
        
        if ($stmt->execute([$nguoi_dung_id, $noi_dung])) {
            $comment_id = $conn->lastInsertId();
            
            // Lấy thông tin đầy đủ của bình luận vừa tạo
            $stmt = $conn->prepare("
                SELECT 
                    bl.id,
                    bl.noi_dung,
                    bl.id_nguoi_dung,
                    DATE_FORMAT(bl.ngay_tao, '%d/%m/%Y %H:%i') as ngay_tao,
                    nd.ho_ten,
                    nd.avatar as user_avatar
                FROM binh_luan bl
                JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
                WHERE bl.id = ?
            ");
            $stmt->execute([$comment_id]);
            $comment_data = $stmt->fetch();

            echo json_encode([
                'success' => true,
                'comment' => [
                    'id' => $comment_id,
                    'ho_ten' => htmlspecialchars($comment_data['ho_ten']),
                    'noi_dung' => nl2br(htmlspecialchars($noi_dung)),
                    'ngay_tao' => $comment_data['ngay_tao'],
                    'trang_thai' => 1,
                    'nguoi_dung_id' => $nguoi_dung_id,
                    'user_avatar' => $comment_data['user_avatar'],
                    'can_edit' => true, // Thêm flag này để biết người dùng có thể sửa/xóa
                    'current_user_id' => $nguoi_dung_id // Thêm ID người dùng hiện tại
                ]
            ]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Xử lý phản hồi comment
if (isset($_POST['action']) && $_POST['action'] === 'reply_comment') {
    try {
        if (!$is_logged_in) {
            throw new Exception("Vui lòng đăng nhập để phản hồi");
        }

        $id_binh_luan_goc = isset($_POST['id_binh_luan_goc']) ? (int)$_POST['id_binh_luan_goc'] : 0;
        $noi_dung = isset($_POST['noi_dung']) ? trim($_POST['noi_dung']) : '';
        $nguoi_dung_id = $_SESSION['user_id'];
        
        if (empty($id_binh_luan_goc)) {
            throw new Exception("Không tìm thấy bình luận gốc");
        }

        if (empty($noi_dung)) {
            throw new Exception("Nội dung phản hồi không được để trống");
        }

        // Lấy thông tin bình luận gốc và người được trả lời
        $stmt = $conn->prepare("
            SELECT bl.noi_dung, nd.ho_ten 
            FROM binh_luan bl
            JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
            WHERE bl.id = ?
        ");
        $stmt->execute([$id_binh_luan_goc]);
        $comment_goc = $stmt->fetch();

        if (!$comment_goc) {
            throw new Exception("Không tìm thấy bình luận gốc");
        }

        // Thêm phản hồi mới
        $stmt = $conn->prepare("
            INSERT INTO binh_luan (
                id_nguoi_dung,
                noi_dung,
                ngay_tao,
                trang_thai,
                id_binh_luan_goc,
                noi_dung_tra_loi,
                ten_nguoi_duoc_tra_loi
            ) VALUES (?, ?, NOW(), 1, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $nguoi_dung_id,
            $noi_dung,
            $id_binh_luan_goc,
            $comment_goc['noi_dung'],
            $comment_goc['ho_ten']
        ]);

        if ($result) {
            $reply_id = $conn->lastInsertId();

            // Lấy thông tin đầy đủ của phản hồi vừa tạo với format thời gian
            $stmt = $conn->prepare("
                SELECT 
                    bl.*,
                    DATE_FORMAT(bl.ngay_tao, '%d/%m/%Y %H:%i') as ngay_tao,
                    nd.ho_ten,
                    nd.avatar as user_avatar
                FROM binh_luan bl
                JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
                WHERE bl.id = ?
            ");
            $stmt->execute([$reply_id]);
            $reply = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($reply) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'reply' => [
                        'id' => $reply['id'],
                        'noi_dung' => $reply['noi_dung'],
                        'ngay_tao' => $reply['ngay_tao'],
                        'ho_ten' => $reply['ho_ten'],
                        'user_avatar' => $reply['user_avatar'],
                        'noi_dung_tra_loi' => $comment_goc['noi_dung'],
                        'ten_nguoi_duoc_tra_loi' => $comment_goc['ho_ten'],
                        'id_binh_luan_goc' => $id_binh_luan_goc,
                        'id_nguoi_dung' => $nguoi_dung_id
                    ]
                ]);
                exit;
            }
        }
        throw new Exception("Không thể tạo phản hồi");
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Xử lý xóa phản hồi
if (isset($_POST['action']) && $_POST['action'] === 'delete_reply') {
    try {
        if (!$is_logged_in) {
            throw new Exception("Vui lòng đăng nhập để xóa phản hồi");
        }

        $reply_id = (int)$_POST['reply_id'];
        $user_id = $_SESSION['user_id'];

        // Kiểm tra quyền xóa
        $stmt = $conn->prepare("
            SELECT id_nguoi_dung 
            FROM binh_luan 
            WHERE id = ? AND id_nguoi_dung = ?
        ");
        $stmt->execute([$reply_id, $user_id]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Bạn không có quyền xóa phản hồi này");
        }

        // Xóa phản hồi
        $stmt = $conn->prepare("DELETE FROM binh_luan WHERE id = ?");
        if ($stmt->execute([$reply_id])) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa phản hồi thành công'
            ]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chùa Khmer Trà Vinh - Trang chủ</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Thêm vào phần head -->
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
/* === BIẾN VÀ RESET === */
:root {
    /* Màu sắc chính */
    --primary-color: #1a237e;
    --secondary-color: #0d47a1;
    --accent-color: #2962ff;
    
    /* Màu gradient */
    --gradient-primary: linear-gradient(135deg, #1a237e, #0d47a1);
    --gradient-accent: linear-gradient(135deg, #2962ff, #1565c0);
    --gradient-dark: linear-gradient(135deg, #1a237e, #000051);
    
    /* Màu trung tính */
    --text-primary: #2c3e50;
    --text-secondary: #546e7a;
    --text-light: #78909c;
    --background-light: #f5f7fa;
    --white: #ffffff;
    
    /* Bóng đổ */
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.12);
    --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
    --shadow-xl: 0 12px 32px rgba(0,0,0,0.2);
    /* Hiểu ứng chuyển động */
    --transition-normal: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    
    /* Bo góc */
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 20px;
    --radius-xl: 28px;
    
    /* Typography */
    --font-base: 1rem;
    --font-md: 1.125rem;
    --font-lg: 1.25rem;
    --font-xl: 1.5rem;
    --font-2xl: 1.75rem;
    --font-3xl: 2rem;
    
    /* Font weights */
    --fw-regular: 400;
    --fw-medium: 500;
    --fw-semibold: 600;
    --fw-bold: 700;
    
    /* Khoảng cách */
    --spacing-xs: 0.5rem;
    --spacing-sm: 1rem;
    --spacing-md: 1.5rem;
    --spacing-lg: 2rem;
    --spacing-xl: 3rem;
}

/* Reset CSS */
*, *::before, *::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* === STYLE CƠ BẢN === */
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    line-height: 1.6;
    color: var(--text-primary);
    background-color: var(--background-light);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

/* === MAIN HEADER === */
.main-header {
    background: #FFFFFF;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    padding: 2rem 0;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
    height: 140px;
}

.header-content {
    display: flex;
    align-items: center;
    max-width: 1400px; /* Tăng chiều rộng tối đa */
    margin: 0 auto;
    padding: 0 30px;
}

/* Logo Styles */
.logo-link {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    text-decoration: none;
    margin-right: auto;
}

.logo-img {
    height: 80px; /* Tăng kích thước logo */
    width: auto;
    border-radius: 12px;
}

/* Navigation Styles */
.main-nav ul {
    display: flex;
    gap: 0.5rem; /* Tăng khoảng cách giữa các menu items */
    list-style: none;
    padding: 0;
    margin: 0;
}

.main-nav a {
    color: #000000;
    text-decoration: none;
    font-size: 1.2rem; /* Tăng cỡ chữ */
    padding: 0.8rem 1rem;
    border-radius: 8px;
    font-weight: 700;
}

/* Special Buttons */
.main-nav a[href="dangnhap.php"],
.main-nav a[href="dangky.php"] {
    padding: 0.8rem 1.2rem;
    border: 2px solid black;
    background-color: white;
}

/* Main Content Wrapper - Thêm mới */
.main-content {
    padding-top: calc(120px + 2rem);
    min-height: 100vh;
}

/* Featured Temple Section */
.featured-temple-section {
    position: relative;
    width: 100%;
    padding: 2.5rem 0;
}

/* Header Content Layout */
.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 0 2.5rem;
}

/* Logo Group - Thêm mới */
.logo-group {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

/* Navigation Group - Thêm mới */
.nav-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.main-nav ul {
    display: flex;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
}

.main-nav a {
    color: #000000;
    text-decoration: none;
    font-size: 1.2rem;
    padding: 0.8rem 1rem;
    border-radius: 8px;
    font-weight: 700;
}

/* Special Buttons */
.main-nav a[href="dangnhap.php"],
.main-nav a[href="dangky.php"] {
    padding: 0.8rem 1.2rem;
    font-size: 1.2rem;
    border: 2px solid black;
    text-decoration: none;
    background-color: white;
    color: #000000;
    font-weight: 700;
    transition: all 0.3s ease;
}

/* Hover effect cho buttons */
.main-nav a[href="dangnhap.php"]:hover,
.main-nav a[href="dangky.php"]:hover {
    background-color: #f8f8f8;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 992px) {
    .header-content {
        padding: 0 2rem;
    }
}

@media (max-width: 768px) {
    .header-content {
        padding: 0 1.5rem;
    }
}

@media (max-width: 576px) {
    .main-nav ul {
        gap: 0.4rem;
    }
    
    .main-nav a {
        padding: 0.8rem 0.8rem;
    }
}

/* === CHÙA NỔI BẬT === */
.featured-temples-section {
    padding: 4rem 0;
    background: linear-gradient(180deg, #ffffff, #f8f9fa);
    margin-top: 80px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.section-title {
    text-align: center;
    color: #1a237e;
    font-size: 2.2rem;
    margin-bottom: 3rem;
    font-weight: 800;
    position: relative;
    padding-bottom: 15px;
}

/* Grid container */
.temples-grid {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Temple card styles */
.temple-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.1);
    width: 100%;
    display: flex;
    flex-direction: row;
    height: 350px;
}

/* Temple image container */
.temple-image {
    position: relative;
    width: 40%;
    overflow: hidden;
}

.temple-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Temple info section */
.temple-info {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    width: 60%;
    position: relative;
}

/* Temple title */
.temple-info h3 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    line-height: 1.3;
    text-align: center;
    width: 100%;
    padding-bottom: 0.5rem;
}

/* Temple details */
.temple-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 3rem;
}

.temple-details p,
.temple-address {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    font-size: 1.5rem;
    color: #4a4a4a;
}

/* Read more button */
.read-more-btn {
    position: absolute;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.8rem 1.2rem;
    background: #4a90e2;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 1rem;
    text-align: left;
    text-decoration: none;
    width: fit-content;
}

/* Responsive */
@media (max-width: 768px) {
    .temple-card {
        flex-direction: column;
        height: auto;
    }
    
    .temple-image {
        width: 100%;
        height: 200px;
    }
    
    .temple-info {
        width: 100%;
        padding: 1.2rem;
        padding-bottom: 4rem;
    }
    
    .temple-info h3 {
        font-size: 1.6rem;
    }
}

@media (max-width: 576px) {
    .section-title {
        font-size: 1.8rem;
    }
    
    .temple-info {
        padding: 1rem;
        padding-bottom: 4rem;
    }
    
    .temple-info h3 {
        font-size: 1.4rem;
    }
    
    .temple-details p,
    .temple-address {
        font-size: 1.3rem;
    }
}

    /* Style cho thông báo đăng nhập */
.login-prompt {
    text-align: center;
    padding: 2rem;
    background: #ffffff; /* Nền trắng */
    border: 2px solid #e0e0e0; /* Viền xám nhạt */
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    margin: 2rem auto;
    max-width: 500px; /* Giới hạn chiều rộng tối đa */
    transition: all 0.3s ease;
}

/* Style cho text trong thông báo */
.login-prompt p {
    color: #000000;
    font-size: 1.1rem;
    font-weight: 500;
    margin: 0;
    line-height: 1;
}

/* Style cho link đăng nhập */
.login-prompt a {
    color: #000000; /* Màu xanh đậm */
    font-weight: 600;
    text-decoration: none;
    padding: 0.3rem 0.8rem;
    border-radius: 4px;
    transition: all 0.3s ease;
    display: inline-block;
    margin-left: 0px;
}

/* Hiệu ứng hover cho link */
.login-prompt a:hover {
    background: rgba(41, 98, 255, 0.1); /* Màu nền xanh nhạt khi hover */
    color: #000000; /* Màu chữ đậm hơn khi hover */
    transform: translateY(-1px);
}

/* Hiệu ứng hover cho toàn bộ khung */
.login-prompt:hover {
    border-color: #000000;
    box-shadow: 0 6px 20px rgba(41, 98, 255, 0.1);
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .login-prompt {
        margin: 1.5rem 1rem;
        padding: 1.5rem;
    }
    
    .login-prompt p {
        font-size: 1rem;
    }
}

    /* Comments section styles */
    .comments-section {
        background: var(--white);
        padding: var(--spacing-xl) 0;
        box-shadow: var(--shadow-sm);
    }

    /* Main comments container */
    .comments-container {
        max-width: 1000px; /* Giới hạn chiều rộng tối đa */
        margin: 2rem auto; /* Căn giữa và tạo khoảng cách trên dưới */
        padding: 2rem;
        background: var(--white);
        border: 2px solid black; /* Viền đen cho khung chính */
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

        /* Nội dung bình luận */
        .comment-content {
            background: #ffffff; /* Màu nền Antique White */
            border: 1px solid #000000;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            color: #000000;
            line-height: 1.6;
            font-size: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }

    /* Comments section title */
    .comments-container .section-title {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.1);
        font-size: 1.5rem;
        font-weight: 600;
    }

    /* Comments list */
    .comments-list {
        padding: 1rem 0;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .comments-container {
            max-width: 95%;
            margin: 1rem auto;
            padding: 1rem;
        }
    }

    .comment-form,
    .reply-form {
        background: var(--white);
        padding: var(--spacing-lg);
        border-radius: var(--radius-lg);
        margin-bottom: var(--spacing-lg);
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .comment-form:focus-within,
    .reply-form:focus-within {
        box-shadow: var(--shadow-lg);
    }

    .comment-form textarea,
    .reply-form textarea {
        width: 50%;
        padding: var(--spacing-md);
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-md);
        min-height: 50px;
        resize: vertical;
        font-family: normal;
        font-size: var(--font-base);
        transition: all 0.3s ease;
        background: var(--background-light);
    }

    .comment-form textarea:focus,
    .reply-form textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.1);
        background: var(--white);
    }

    /* Base styles for items */
    .comment-item,
    .reply-item {
        background: var(--white);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
        box-shadow: var(--shadow-md);
        border: 2px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        position: relative;
    }

    .comment-item:hover,
    .reply-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    /* Header styles */
    .comment-header,
    .reply-header,
    .admin-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-md);
    }
    /* Avatar styles */
    .user-avatar,
    .reply-avatar,
    .admin-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: var(--fw-bold);
        font-size: 16px;
        box-shadow: var(--shadow-md);
        border: 3px solid var(--white);
        transition: transform 0.3s ease;
    }

    .user-avatar:hover,
    .reply-avatar:hover,
    .admin-avatar:hover {
        transform: scale(1.1);
    }

    img.user-avatar {
        object-fit: cover;
    }

    .reply-avatar {
        background: var(--gradient-primary);
        color: var(--white);
    }

    .admin-avatar {
        background: var(--gradient-accent);
        color: var(--white);
    }

    /* Content styles */
    .comment-content,
    .reply-content,
    .admin-content {
        color: var(--text-primary);
        line-height: 1.7;
        font-size: var(--font-md);
        margin: var(--spacing-md) 0;
        padding: var(--spacing-md);
        background: var(--background-light);
        border-radius: var(--radius-md);
    }

    /* Replies container */
    .replies-container {
        margin-left: 60px;
        margin-top: var(--spacing-md);
        padding-left: var(--spacing-lg);
        border-left: 3px solid var(--background-light);
    }

    /* Replied content */
    .replied-content {
        background: rgba(0,0,0,0.02);
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-md);
        border-left: 4px solid var(--primary-color);
        font-size: 0.95em;
    }

    .replied-content small {
        color: var(--text-secondary);
        font-weight: var(--fw-medium);
        display: block;
        margin-bottom: 6px;
    }

    .replied-content q {
        color: var(--text-secondary);
        font-style: italic;
        display: block;
        margin-top: 6px;
        line-height: 1.6;
    }

    /* Admin response */
    .admin-response {
        margin-top: 1rem;
        margin-left: 2rem;
        margin-right: 2rem;
        background: var(--white); 
        border-radius: 8px;
        padding: 1.5rem;
        border-top: 2px solid #000000;    
        border-bottom: 2px solid #000000;
        border-left: 2px solid #000000;
        border-right: 2px solid #000000;
        max-width: 95%;
        margin-left: auto;
        margin-right: auto;
    }

        /* Admin header */
        .admin-header {
            display: flex;
            align-items: center;
            gap: 0.5rem; /* Giảm khoảng cách */
            margin-bottom: 1.5rem; /* Giảm margin bottom */
        }

        /* CSS */
        .admin-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .admin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-avatar:hover {
            transform: scale(1.1);
        }
        
    /* Style cho phản hồi của admin */
    .admin-content {
        background-color: #ffffff;
        border: 1px solid #000000;
        border-radius: 8px;
        margin: 0px 10px;
        overflow: hidden;
    }

    /* Meta styles */
    .comment-meta,
    .reply-meta,
    .admin-info {
        flex-grow: 1;
    }

    .comment-author,
    .reply-author,
    .admin-info strong {
        font-weight: var(--fw-semibold);
        font-size: var(--font-md);
        color: var(--text-primary);
        margin-bottom: 4px;
        display: block;
    }

    .comment-date,
    .reply-date,
    .admin-date {
        font-size: var(--font-base);
        color: var(--text-light);
    }

    /* Edit and Delete buttons */
    .edit-btn,
    .delete-btn {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius-sm);
        font-size: 0.9em;
        transition: all 0.3s ease;
    }

    .edit-btn {
        color: var(--accent-color);
        border: 1px solid var(--accent-color);
        background: transparent;
    }

    .edit-btn:hover {
        background: var(--accent-color);
        color: var(--white);
    }

    .delete-btn {
        color: #dc3545;
        border: 1px solid #dc3545;
        background: transparent;
    }

    .delete-btn:hover {
        background: #dc3545;
        color: var(--white);
    }

    /* Form bình luận chính */
    .comment-form {
        background: #ffffff;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center; /* Căn giữa các phần tử con */
    }

    .comment-form:focus-within {
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    /* Textarea bình luận */
    .comment-form textarea {
        width: 100%;
        min-height: 120px;
        padding: 1rem;
        border: 2px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        font-size: 1rem;
        line-height: 1.6;
        resize: vertical;
        transition: all 0.3s ease;
        background: var(--background-light);
        margin-bottom: 1rem;
    }

    .comment-form textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(26,35,126,0.1);
        background: var(--white);
    }

    /* Nút gửi bình luận */
    .comment-form .btn-primary {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(26,35,126,0.2);
        margin: 0 auto; /* Căn giữa nút */
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(26,35,126,0.3);
    }

    .comment-form .btn-primary:active {
        transform: translateY(0);
    }

    /* Style cho thông báo đăng nhập */
    .login-prompt {
        text-align: center;
        padding: 1.5rem;
        background: var(--white);
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .login-prompt a {
        color: var(--primary-color);
        text-decoration: none; /* Bỏ gạch chân */
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .login-prompt a:hover {
        color: var(--accent-color);
        text-decoration: none; /* Thêm dòng này để đảm bảo không có gạch chân khi hover */
    }

/* Nội dung phản hồi */
.reply-content {
    background: #ffffff; /* Màu nền giống comment */
    border: 1px solid #000000;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
    color: #000000;
    line-height: 1.6;
    font-size: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.replied-content::before {
    content: '"';
    position: absolute;
    left: -2px;
    top: -5px;
    font-size: 2rem;
    color: var(--primary-color);
    opacity: 0.2;
}

    /* Nút tương tác (Sửa, Xóa, Phản hồi) */
    .comment-actions {
        display: flex;
        gap: 12px;
        margin-top: 1rem;
        justify-content: center; /* Thêm dòng này */
        padding: 0.8rem 0;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .edit-btn,
    .delete-btn,
    .reply-btn {
        padding: 0.6rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 100px; /* Thêm độ rộng tối thiểu cho nút */
        justify-content: center; /* Căn giữa nội dung trong nút */
    }

    /* Nút Sửa */
    .edit-btn {
        color: var(--accent-color);
        background: rgba(41,98,255,0.1);
    }

    .edit-btn:hover {
        background: var(--accent-color);
        color: white;
        transform: translateY(-2px);
    }

    /* Nút Xóa */
    .delete-btn {
        color: #dc3545;
        background: rgba(220,53,69,0.1);
    }

    .delete-btn:hover {
        background: #dc3545;
        color: white;
        transform: translateY(-2px);
    }

    /* Nút Phản hồi */
    .reply-btn {
        color: var(--text-primary);
        background: var(--background-light);
        border: 1px solid rgba(0,0,0,0.1);
    }

    .reply-btn:hover {
        background: var(--text-primary);
        color: #ffffff;
        transform: translateY(-2px);
    }

    /* Icon trong nút */
    .comment-actions i {
        font-size: 0.9rem;
    }

    /* Form phản hồi */
    .reply-form-container {
        margin-top: 1rem;
        padding: 1rem;
        background: var(--background-light);
        border-radius: 12px;
        border-left: 4px solid var(--primary-color);
    }

    .reply-form textarea {
        width: 100%;
        min-height: 100px;
        padding: 1rem;
        border: 2px solid rgba(0,0,0,0.08);
        border-radius: 10px;
        font-size: 0.95rem;
        resize: vertical;
        transition: all 0.3s ease;
        background: white;
    }

    .reply-form textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(26,35,126,0.1);
    }

    /* Button group trong form phản hồi */
    .button-group {
        display: flex;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-md);
        padding-top: var(--spacing-sm);
        border-top: 1px solid rgba(0,0,0,0.05);
        display: flex;
        gap: 10px;
        margin-top: 15px;
        justify-content: center;
    }
    /* Nút Gửi */
        .reply-form .button-group .btn-primary {
            background-color: #28a745;
            color: white;
        }

        .reply-form .button-group .btn-primary:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        /* Nút Hủy */
        .reply-form .button-group .btn-secondary {
            background-color: #dc3545;
            color: white;
            margin-left: 10px;
        }

        .reply-form .button-group .btn-secondary:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

    /* Hiệu ứng loading cho nút */
    .btn-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.8;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin: -8px 0 0 -8px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: button-loading-spinner 0.8s linear infinite;
    }

    @keyframes button-loading-spinner {
        to {
            transform: rotate(360deg);
        }
    }
    /* Khung sửa bình luận */
    .edit-form {
        background: var(--white);
        padding: 1.2rem;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        margin: 1rem 0;
        border: 1px solid rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }

    .edit-form:focus-within {
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    /* Textarea sửa bình luận */
    .edit-form textarea {
        width: 100%;
        min-height: 100px;
        padding: 1rem;
        border: 2px solid rgba(0,0,0,0.08);
        border-radius: 10px;
        font-size: 0.95rem;
        line-height: 1.6;
        resize: vertical;
        transition: all 0.3s ease;
        background: var(--background-light);
        margin-bottom: 1rem;
    }

    .edit-form textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(26,35,126,0.1);
        background: var(--white);
    }

    /* Button group trong form sửa */
    .edit-form .button-group {
        display: flex;
        gap: 10px;
        margin-top: 1rem;
        padding-top: 0.8rem;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    /* Nút Lưu */
    .edit-form button[type="submit"] {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(26,35,126,0.2);
    }

    .edit-form button[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(26,35,126,0.3);
    }

    .edit-form button[type="submit"]:active {
        transform: translateY(0);
    }

    /* Nút Hủy */
    .edit-form button[type="button"] {
        background: #dc3545;
        color: #ffffff;
        padding: 0.8rem 1.5rem;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .edit-form button[type="button"]:hover {
        background: #dc3545;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Icon trong nút */
    .edit-form button i {
        font-size: 0.9rem;
    }

    /* Hiệu phần loading cho nút */
    .edit-form button.loading {
        position: relative;
        pointer-events: none;
        opacity: 0.8;
    }

    .edit-form button.loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin: -8px 0 0 -8px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: button-loading-spinner 0.8s linear infinite;
    }

    /* Animation cho loading */
    @keyframes button-loading-spinner {
        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive */
    @media (max-width: 576px) {
        .edit-form {
            padding: 1rem;
        }

        .edit-form .button-group {
            flex-direction: column;
        }

        .edit-form button {
            width: 100%;
            justify-content: center;
        }
    }

    /* Style cho actions của phản hồi */
    .reply-actions {
        display: flex;
        gap: 8px;
        margin-top: 0.8rem;
        justify-content: center; /* Thêm dòng này */
        padding: 0.6rem 0;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .reply-actions button {
        padding: 0.5rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Khung hiển thị nội dung phản hồi */
    #replyToReplyForm {
        margin-top: 1rem;
        padding: 1rem;
        background: #28a745;  /* Màu nền xanh lá */
        border-radius: 10px;
        border-left: 3px solid var(--primary-color);
        border: 1px solid #000000;  /* Thêm viền đen */
    }


    /* Hiệu ứng hover cho các nút trong phản hồi */
    .reply-actions .edit-btn:hover,
    .reply-actions .delete-btn:hover,
    .reply-actions .reply-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .success-notification,
    .error-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .success-notification {
        background-color: #28a745;
    }

    .error-notification {
        background-color: #dc3545;
    }

    .success-notification i,
    .error-notification i {
        font-size: 1.2em;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

/* Newsletter Form */
.newsletter-form .form-group {
    position: relative;
    margin-top: 1rem;
}

.newsletter-form .form-group a {
    display: inline-block;
    padding: 0.875rem 2rem;
    background: #ffffff;
    color: #333333;
    border: 2px solid #333333;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
}

.newsletter-form .form-group a:hover {
    background: #333333;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.newsletter-form .form-group a:active {
    transform: translateY(0);
    background: #eeeeee;
}

/* Contact info icons */
.contact-info i {
    width: 20px;
    text-align: center;
}

    /* Social Links */
    .social-links {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .social-link {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4a90e2;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .social-link:hover {
        background: #4a90e2;
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(74, 144, 226, 0.2);
    }

    /* Contact Info */
    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 0.8rem; /* Giảm từ 1.2rem xuống 0.8rem */
    }

    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 0.8rem; /* Giảm từ 1rem xuống 0.8rem */
    }

    .contact-item p {
        margin: 0;
        color: #666666;
        font-size: 0.95rem;
        white-space: pre-line; /* Thêm dòng này để cho phép xuống dòng */
    }

    /* Newsletter Form */
    .newsletter-form .form-group {
        position: relative;
        margin-top: 1rem;
    }

    .newsletter-form .form-group a {
        display: inline-block;
        padding: 0.875rem 2rem;
        background: #ffffff; /* Nền trắng */
        color: #333333; /* Chữ đen */
        border: 2px solid #333333; /* Viền đen */
        border-radius: 8px;
        text-decoration: none; /* Bỏ gạch chân */
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .newsletter-form .form-group a:active {
        transform: translateY(0);
        background: #eeeeee; /* Nền xám đậm hơn khi click */
    }

    .newsletter-form input {
        width: 100%;
        padding: 0.875rem 1rem;
        padding-right: 3rem;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        color: #333333;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .newsletter-form input:focus {
        outline: none;
        border-color: #4a90e2;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    }

    .newsletter-form input::placeholder {
        color: #999999;
    }

    .newsletter-form button {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #4a90e2;
        cursor: pointer;
        padding: 0.5rem;
        transition: all 0.3s ease;
    }

    .newsletter-form button:hover {
        color: #2171d0;
        transform: translateY(-50%) scale(1.1);
    }


    /* Header icons */
    .main-nav i {
        margin-right: 0.5rem;
    }

    /* Temple card icons */
    .temple-info i {
        margin-right: 0.5rem;
    }

    /* Comment section icons */
    .comment-actions i,
    .reply-actions i,
    .button-group i {
        margin-right: 0.5rem;
    }

    /* Specific overrides */
    .btn-primary i,
    .button-group button[type="submit"] i,
    .social-link i,
    .read-more-btn i {
        color: white;
    }

    /* Loading spinner icon */
    .btn-loading i {
        animation: button-loading-spinner 0.8s linear infinite;
    }

    /* Hover effects */
    a:hover i,
    button:hover i,
    .social-link:hover i {
        transform: scale(1.1);
    }

    /* Icon alignment in flex containers */
    .comment-actions,
    .reply-actions,
    .button-group,
    .footer-links a,
    .contact-item,
    .social-links {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Newsletter form icon */
    .newsletter-form button i {
        margin: 0;
    }

    /* Admin response icon */
    .admin-avatar i {
        color: var(--white);
        width: auto;
    }

    /* Notification icons */
    .success-notification i,
    .error-notification i {
        color: white;
        font-size: 1.2em;
        margin-right: 0.5rem;
    }

    /* Temple details icons */
    .temple-details i {
        width: 20px;
        text-align: center;
    }

    /* Contact info icons in footer */
    .contact-info i {
        margin-top: 0.2rem;
    }

    /* Footer links icons */
    .footer-links i {
        font-size: 0.8rem;
    }

    /* Edit and delete button icons */
    .edit-btn i,
    .delete-btn i {
        color: inherit;
    }

    /* Reply button icons */
    .reply-btn i {
        color: inherit;
    }

    /* Loading animation */
    @keyframes button-loading-spinner {
        to {
            transform: rotate(360deg);
        }
    }

    /* Thêm vào phần style */
    .temple-list {
        position: relative;
    }

    .temple-item {
        display: none; /* Ẩn tất cả temple-item ban đầu */
    }

    .temple-item.visible {
        display: block; /* Hiển thị các temple-item có class visible */
    }

    .load-more-btn {
        display: block;
        margin: 30px auto;
        padding: 12px 35px;
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .load-more-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26,35,126,0.2);
    }

    .load-more-btn i {
        margin-left: 8px;
    }

    .load-more-btn.hidden {
        display: none;
    }

    /* Temple Header */
    .temple-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 140px 0 60px; /* Tăng padding-top lên để tránh bị main header che */
        text-align: center;
        margin-bottom: 40px;
    }

    .temple-title {
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 700;
        padding: 0 20px;
        line-height: 1.4; /* Thêm line-height để tránh chữ bị dính */
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .temple-header {
            padding: 120px 0 50px;
        }
    }

    @media (max-width: 768px) {
        .temple-header {
            padding: 100px 0 40px;
        }
        
        .temple-title {
            font-size: 2rem;
        }
    }

    @media (max-width: 576px) {
        .temple-header {
            padding: 90px 0 30px;
        }
        
        .temple-title {
            font-size: 1.8rem;
        }
    }
    /* Style cho form phản hồi */
    .reply-form-container {
        margin: 15px 0;
        padding: 1.2rem;
        background: var(--white);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .reply-form-container:focus-within {
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transform: translateY(-1px);
    }

    .reply-form textarea {
        width: 100%;
        min-height: 100px;
        margin: 12px 0;
        padding: 1rem;
        border: 2px solid rgba(0,0,0,0.08);
        border-radius: 8px;
        font-size: 0.95rem;
        line-height: 1.6;
        resize: vertical;
        transition: all 0.3s ease;
        background: var(--background-light);
    }

    .reply-form textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(26,35,126,0.1);
        background: var(--white);
    }

    .replied-to {
        background: var(--background-light);
        padding: 1rem 1.2rem;
        border-left: 4px solid var(--primary-color);
        margin-bottom: 1rem;
        border-radius: 0 8px 8px 0;
        position: relative;
    }

    .replied-to small {
        color: var(--primary-color);
        font-weight: 600;
        font-size: 0.9rem;
        display: block;
        margin-bottom: 0.5rem;
    }

    .replied-content {
        color: var(--text-light);
        font-style: italic;
        font-size: 0.9rem;
        line-height: 1.5;
        position: relative;
        padding-left: 1rem;
    }

    .replied-content::before {
        content: '"';
        position: absolute;
        left: 0;
        top: -2px;
        font-size: 1.2rem;
        color: var(--text-light);
        opacity: 0.5;
    }

    .button-group {
        display: flex;
        gap: 12px;
        margin-top: 1rem;
        padding-top: 0.8rem;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .button-group button {
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .button-group button i {
        font-size: 1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(26,35,126,0.2);
    }

    .btn-secondary {
        background: white;
        color: var(--text-primary);
        border: 1px solid rgba(0,0,0,0.1);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(26,35,126,0.3);
    }

    .btn-secondary:hover {
        background: var(--background-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .reply-form-container {
            padding: 1rem;
        }
        
        .button-group {
            flex-direction: column;
        }
        
        .button-group button {
            width: 100%;
            justify-content: center;
        }
    }

    /* Thêm vào phần style của bạn */
    .btn-primary {
        background-color: #1a237e !important; /* Xanh đậm */
        color: white !important;
        border: none;
    }

    .btn-primary:hover {
        background-color: #0d47a1 !important; /* Xanh đậm hơn khi hover */
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26,35,126,0.15);
    }

    .btn-secondary {
        background-color: white;
        color: #333;
        border: 1px solid #ddd;
    }

/* Search container styles */
.search-container {
    background: white;
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin: 2rem auto;
    max-width: 1200px;
}

.search-form {
    width: 100%;
}

.search-fields {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-field {
    flex: 1;
    min-width: 200px;
}

.search-field input,
.search-field select {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 1px solid #ddd;
    border-radius: var(--radius-md);
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-field input:focus,
.search-field select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(26, 35, 126, 0.1);
    outline: none;
}

#searchButton {
    padding: 0.8rem 1.5rem;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

#searchButton:hover {
    background: var(--secondary-color);
    transform: translateY(-1px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .search-fields {
        flex-direction: column;
    }
    
    .search-field {
        width: 100%;
    }
    
    #searchButton {
        width: 100%;
        justify-content: center;
    }
}

    /* Chỉnh sửa style cho temple-address */
    .temple-address {
        display: flex;
        align-items: flex-start;
        gap: 1px;
        margin-bottom: 1px;
    }

    /* Định dạng chung cho các thông tin chi tiết */
    .temple-details p,
    .temple-address {
        display: flex;
        align-items: center;
        gap: 3px;  /* Tăng khoảng cách giữa icon và text */
        margin-bottom: 15px;
        font-size: 15px;
        color: #000000;
        padding: 3px 6px;  /* Tăng padding để có không gian thoáng hơn */
        background-color: #ffffff;
        border-radius: 4px;
        transition: all 0.3s ease;
        font-weight: 500;
        letter-spacing: 0.3px;
        white-space: nowrap; /* Ngăn text xuống dòng */
    }

    /* Định dạng icon */
    .temple-details p i,
    .temple-address i {
        width: 18px;  /* Tăng kích thước icon */
        height: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;  /* Tăng font size của icon */
        flex-shrink: 0;  /* Ngăn icon bị co lại */
    }

    /* Màu sắc riêng cho từng icon */
    .temple-address i { color: #2962ff; }           /* Đỏ cho địa chỉ */
    .temple-details p:nth-child(1) i { color: #2962ff; }  /* Tím cho trụ trì */
    .temple-details p:nth-child(2) i { color: #2962ff; }  /* Xanh dương cho điện thoại */
    .temple-details p:nth-child(3) i { color: #2962ff; }  /* Xanh lá cho email */

     /* Footer */
     .main-footer {
            background-color: #f8f9fa;
            color: #343a40; 
            padding: 80px 0 40px;
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
            color: #000000;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #2962ff;
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
            color: #343a40;
            font-size: 1.3rem;
            text-decoration: none;
            font-weight: bold;
        }

        .contact-item p {
            color: #000000;
            margin: 0;
            font-size: 1.2rem;
        }

        .footer-bottom {
            margin-top: 70px;
            padding-top: 35px;
            text-align: center;
            font-size: 1.1rem;
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
                gap: 40px;
            }

            .footer-section h3::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .contact-item {
                justify-content: center;
            }

            .contact-item:hover {
                transform: none;
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

            .footer-section h3 {
                font-size: 1.5rem;
            }

            .footer-content-wrapper {
                font-size: 1.1rem;
            }

            .contact-item i {
                font-size: 1.3rem;
            }

            .contact-item p {
                font-size: 1.1rem;
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
                    <li><a href="dschua.php">Chùa Khmer</a></li>
                    <li><a href="sukien.php">Lễ hội</a></li>
                    <li><a href="taikhoan.php">Tài khoản</a></li>
                    <li><a href="dangnhap.php">Đăng nhập</a></li>
                    <li><a href="dangky.php">Đăng ký</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>
    
<!-- Tìm kiếm -->
<div class="main-content">
    <!-- Form tìm kiếm -->
    <div class="search-wrapper" style="max-width: 800px; margin: 40px auto; display: flex; align-items: center; background: white; padding: 10px 20px; border-radius: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <input type="text" id="templeSearch" placeholder="Tìm kiếm chùa..." style="flex: 1; border: none; padding: 15px; font-size: 18px; outline: none; color: var(--text-primary); background: transparent;">
        <button type="button" id="searchButton" style="background: #2962ff; color: white; border: none; padding: 15px 35px; border-radius: 30px; cursor: pointer; margin-left: 15px; font-size: 18px; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(26,35,126,0.3);">
            <i class="fas fa-search"></i>
        </button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('templeSearch');
        const searchButton = document.getElementById('searchButton');
        const templeCards = document.querySelectorAll('.temple-card');

        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase();
            
            templeCards.forEach(card => {
                const templeName = card.querySelector('h3').textContent.toLowerCase();
                const templeAddress = card.querySelector('.temple-address').textContent.toLowerCase();
                
                if (templeName.includes(searchTerm) || templeAddress.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        searchButton.addEventListener('click', performSearch);
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    });
    </script>

    <!-- Section chùa nổi bật -->
    <section class="featured-temple-section">
        <h2 class="section-title">Chùa Nổi Bật</h2>
        <div class="temples-grid">

<!-- Chùa 1 -->
<div class="temple-card">
    <div class="temple-image">
        <img src="image/ChuaAng/5.jpg" alt="Chùa Âng">
    </div>
    <div class="temple-info">
        <h3 style="font-size: 2.34rem;">Chùa Âng</h3>
        <p class="temple-address" style="font-size: 1.2rem;">
            <b>Địa chỉ:</b> xã Lương Hoà, huyện Châu Thành, TP. Trà Vinh
        </p>
        <div class="temple-details">
            <p style="font-size: 1.2rem;"><b>Điện thoại:</b> 02943851123</p>
            <p style="font-size: 1.2rem;"><b>Email:</b> chuaang@gmail.com</p>
            <a href="chitietchua.php?id=1" class="read-more-btn" style="font-size: 1rem;">Xem thêm tại đây</a>
        </div>
    </div>
</div>

<!-- Chùa 2 -->
<div class="temple-card">
    <div class="temple-image">
        <img src="image/ChuaHang/5.jpg" alt="Chùa Hang">
    </div>
    <div class="temple-info">
        <h3 style="font-size: 2.34rem;">Chùa Hang</h3>
        <p class="temple-address" style="font-size: 1.2rem;">
            <b>Địa chỉ:</b> TT. Châu Thành, huyện Châu Thành, tỉnh Trà Vinh
        </p>
        <div class="temple-details">
            <p style="font-size: 1.2rem;"><b>Điện thoại:</b> 02943855456</p>
            <p style="font-size: 1.2rem;"><b>Email:</b> chuahang@gmail.com</p>
            <a href="chitietchua.php?id=2" class="read-more-btn" style="font-size: 1rem;">Xem thêm tại đây</a>
        </div>
    </div>
</div>

<!-- Chùa 3 -->
<div class="temple-card">
    <div class="temple-image">
        <img src="image/ChuaSamrongEk/Chuasamrongek10.jpg" alt="Chùa Samrong Ek">
    </div>
    <div class="temple-info">
        <h3 style="font-size: 2.34rem;">Chùa Samrong Ek</h3>
        <p class="temple-address" style="font-size: 1.2rem;">
            <b>Địa chỉ:</b> Phường 8, TP. Trà Vinh
        </p>
        <div class="temple-details">
            <p style="font-size: 1.2rem;"><b>Điện thoại:</b> 02943854321</p>
            <p style="font-size: 1.2rem;"><b>Email:</b> chuasamrongek@gmail.com</p>
            <a href="chitietchua.php?id=4" class="read-more-btn" style="font-size: 1rem;">Xem thêm tại đây</a>
        </div>
    </div>
</div>
</section>

    <section class="comments-section">
    <div class="container">
        <div class="comments-container">
            <h2 class="section-title">Bình luận</h2>
            
            <!-- Form bình luận -->
            <?php if ($is_logged_in): ?>
                <form id="commentForm" class="comment-form">
                    <textarea name="noi_dung" placeholder="Nhập bình luận của bạn..." required></textarea>
                    <button type="submit" class="btn btn-primary">Gửi bình luận</button>
                </form>
            <?php else: ?>
                <p class="login-prompt">Vui lòng <a href="dangnhap.php">Đăng nhập</a> để được bình luận.</p>
            <?php endif; ?>

<!-- Danh sách bình luận -->
<div class="comments-list">
    <?php foreach ($comments as &$comment): ?>
        <?php if ($comment['trang_thai'] == 1): ?> 
            <div class="comment-item" data-id="<?php echo $comment['id']; ?>">
                <div class="comment-header">
                    <?php if ($comment['user_avatar']): ?>
                        <img src="<?php echo htmlspecialchars($comment['user_avatar']); ?>" 
                             alt="<?php echo htmlspecialchars($comment['ho_ten']); ?>" 
                             class="user-avatar">
                    <?php else: ?>
                        <div class="comment-avatar">
                            <?php echo strtoupper(substr($comment['ho_ten'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="comment-meta">
                        <div class="comment-author"><?php echo htmlspecialchars($comment['ho_ten']); ?></div>
                        <div class="comment-date">
                            <?php 
                                $date = new DateTime($comment['ngay_tao']);
                                echo $date->format('d/m/Y H:i');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="comment-content" id="comment-content-<?php echo $comment['id']; ?>">
                    <?php echo nl2br(htmlspecialchars($comment['noi_dung'])); ?>
                </div>

                <?php if (!empty($comment['phan_hoi'])): ?>
                    <div class="admin-response">
                        <div class="admin-header">
                            <div class="admin-avatar">QTV</div>
                            <div class="admin-info">
                                <strong>Quản trị viên</strong>
                                <div class="admin-date">
                                    <?php echo isset($comment['ngay_phan_hoi']) ? $comment['ngay_phan_hoi'] : ''; ?>
                                </div>
                            </div>
                        </div>
                        <div class="admin-content">
                            <?php echo nl2br(htmlspecialchars($comment['phan_hoi'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Các nút chức năng cho bình luận -->
                <div class="comment-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_id'] == $comment['id_nguoi_dung']): ?>
                            <button class="edit-btn" onclick="showEditForm(<?php echo $comment['id']; ?>, '<?php echo htmlspecialchars($comment['noi_dung']); ?>')">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button class="delete-btn" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] == 'admin'): ?>
                            <button class="toggle-status-btn" onclick="toggleCommentStatus(<?php echo $comment['id']; ?>, <?php echo $comment['trang_thai'] == 0 ? 'true' : 'false'; ?>)">
                                <i class="fas fa-<?php echo $comment['trang_thai'] == 1 ? 'eye-slash' : 'eye'; ?>"></i>
                                <?php echo $comment['trang_thai'] == 1 ? 'Ẩn' : 'Hiển thị'; ?>
                            </button>
                        <?php endif; ?>
                        <button class="reply-btn" onclick="showReplyForm(this, <?php echo $comment['id']; ?>)">
                            <i class="fas fa-reply"></i> Phản hồi
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Form phản hồi sẽ được chèn vào đây -->
                <div class="reply-form-wrapper"></div>

                <!-- Danh sách phản hồi -->
                <div class="replies-container" id="replies-<?php echo $comment['id']; ?>">
                    <?php foreach ($comment['replies'] as $reply): ?>
                        <?php if ($reply['trang_thai'] == 1): ?>  <!-- Chỉ hiển thị khi trang_thai = 1 -->
                            <div class="reply-item" data-id="<?php echo $reply['id']; ?>">
                                <div class="reply-header">
                                    <?php if ($reply['user_avatar']): ?>
                                        <img src="<?php echo htmlspecialchars($reply['user_avatar']); ?>" 
                                             alt="<?php echo htmlspecialchars($reply['ho_ten']); ?>" 
                                             class="user-avatar">
                                    <?php else: ?>
                                        <div class="reply-avatar">
                                            <?php echo strtoupper(substr($reply['ho_ten'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="reply-meta">
                                        <div class="reply-author"><?php echo htmlspecialchars($reply['ho_ten']); ?></div>
                                        <div class="reply-date">
                                            <?php 
                                                $date = new DateTime($reply['ngay_tao']);
                                                echo $date->format('d/m/Y H:i');
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="reply-content" id="reply-content-<?php echo $reply['id']; ?>">
                                    <?php echo nl2br(htmlspecialchars($reply['noi_dung'])); ?>
                                </div>

                                <!-- Hiển thị nội dung phản hồi của quản trị viên nếu có -->
                                <?php if (!empty($reply['phan_hoi'])): ?>
                                    <div class="admin-response">
                                        <div class="admin-header">
                                            <div class="admin-avatar">QTV</div>
                                            <div class="admin-info">
                                                <strong>Quản trị viên</strong>
                                                <!-- Đã xóa dòng hiển thị ngay_phan_hoi -->
                                            </div>
                                        </div>
                                        <div class="admin-content">
                                            <?php echo nl2br(htmlspecialchars($reply['phan_hoi'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Các nút chức năng cho phản hồi -->
                                <div class="reply-actions">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <?php if ($_SESSION['user_id'] == $reply['id_nguoi_dung']): ?>
                                            <button class="edit-btn" onclick="showReplyEditForm(<?php echo $reply['id']; ?>, '<?php echo htmlspecialchars($reply['noi_dung']); ?>')">
                                                <i class="fas fa-edit"></i> Sửa
                                            </button>
                                            <button class="delete-btn" onclick="deleteReply(<?php echo $reply['id']; ?>)">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        <?php endif; ?>
                                        <button class="reply-btn" onclick="showReplyToReplyForm(<?php echo $comment['id']; ?>, <?php echo $reply['id']; ?>, '<?php echo htmlspecialchars($reply['ho_ten']); ?>')">
                                            <i class="fas fa-reply"></i> Phản hồi
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <!-- Form phản hồi sẽ được chèn vào đây -->
                                <div class="reply-form-wrapper"></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
        </div>
    </div>
</section>

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
                            <p style="display: inline-block; margin: 0; white-space: nowrap; color: #000000;">Xã Phong Phú, Huyện Cầu Kè, Thành phố Trà Vinh</p>
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
// Thêm biến isLoggedIn ở đầu file
const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

// Biến kiểm tra trạng thái loading
let isLoading = false;

// Cập nhật hàm createCommentHTML để thêm các nút tương tác
function createCommentHTML(comment) {
    const currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    const isCommentOwner = currentUserId === parseInt(comment.nguoi_dung_id);
    
    const avatarHtml = comment.user_avatar 
        ? `<img src="${escapeHtml(comment.user_avatar)}" alt="${escapeHtml(comment.ho_ten)}" class="user-avatar">` 
        : `<div class="comment-avatar">${escapeHtml(comment.ho_ten.substring(0, 2).toUpperCase())}</div>`;

    const actionButtons = isCommentOwner ? `
        <div class="comment-actions">
            <button class="edit-btn" onclick="showEditForm(${comment.id}, '${escapeHtml(comment.noi_dung)}')">
                <i class="fas fa-edit"></i> Sửa
            </button>
            <button class="delete-btn" onclick="deleteComment(${comment.id})">
                <i class="fas fa-trash"></i> Xóa
            </button>
            <button class="reply-btn" onclick="showReplyForm(${comment.id})">
                <i class="fas fa-reply"></i> Phản hồi
            </button>
        </div>
    ` : `
        <div class="comment-actions">
            <button class="reply-btn" onclick="showReplyForm(${comment.id})">
                <i class="fas fa-reply"></i> Phản hồi
            </button>
        </div>
    `;

    // Phần hiển thị phản hồi của admin
    const adminReplyHtml = comment.phan_hoi ? `
        <div class="admin-reply">
                <div class="admin-avatar">
                    <img src="image/qtv.jpg" alt="Admin Avatar">
                </div>
                <div class="admin-meta">
                    <div class="admin-name">Quản trị viên</div>
                </div>
            </div>
            <div class="admin-reply-content">
                ${nl2br(escapeHtml(comment.phan_hoi))}
            </div>
        </div>
    ` : '';

    return `
        <div class="comment-item" data-id="${comment.id}">
            <div class="comment-header">
                ${avatarHtml}
                <div class="comment-meta">
                    <div class="comment-author">${escapeHtml(comment.ho_ten)}</div>
                    <div class="comment-date">
                        ${comment.ngay_cap_nhat ? 
                            ` ${comment.ngay_cap_nhat}` : 
                            comment.ngay_tao}
                    </div>
                </div>
            </div>
            <div class="comment-content" id="comment-content-${comment.id}">
                ${nl2br(escapeHtml(comment.noi_dung))}
            </div>
            ${actionButtons}
            ${adminReplyHtml}
            <div class="reply-form-wrapper"></div>
            <div class="replies-container" id="replies-${comment.id}"></div>
        </div>
    `;
}

// Sửa lại hàm createReplyHTML
function createReplyHTML(reply) {
    const currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    const isReplyOwner = currentUserId === parseInt(reply.id_nguoi_dung);
    
    const avatarHtml = reply.user_avatar 
        ? `<img src="${escapeHtml(reply.user_avatar)}" alt="${escapeHtml(reply.ho_ten)}" class="user-avatar">` 
        : `<div class="reply-avatar">${escapeHtml(reply.ho_ten.substring(0, 2).toUpperCase())}</div>`;

    // Tạo các nút tương tác dựa trên quyền sở hữu
    const actionButtons = isReplyOwner ? `
        <div class="reply-actions">
            <button class="edit-btn" onclick="showReplyEditForm(${reply.id}, '${escapeHtml(reply.noi_dung)}')">
                <i class="fas fa-edit"></i> Sửa
            </button>
            <button class="delete-btn" onclick="deleteReply(${reply.id})">
                <i class="fas fa-trash"></i> Xóa
            </button>
            <button class="reply-btn" onclick="showReplyToReplyForm(${reply.id_binh_luan_goc}, ${reply.id}, '${escapeHtml(reply.ho_ten)}')">
                <i class="fas fa-reply"></i> Phản hồi
            </button>
        </div>
    ` : `
        <div class="reply-actions">
            <button class="reply-btn" onclick="showReplyToReplyForm(${reply.id_binh_luan_goc}, ${reply.id}, '${escapeHtml(reply.ho_ten)}')">
                <i class="fas fa-reply"></i> Phản hồi
            </button>
        </div>
    `;

    return `
        <div class="reply-item" data-id="${reply.id}">
            <div class="reply-header">
                ${avatarHtml}
                <div class="reply-meta">
                    <div class="reply-author">${escapeHtml(reply.ho_ten)}</div>
                    <div class="reply-date">
                        ${reply.ngay_cap_nhat ? 
                            `Đã chỉnh sửa ${reply.ngay_cap_nhat}` : 
                            reply.ngay_tao}
                    </div>
                </div>
            </div>
            <div class="reply-content" id="reply-content-${reply.id}">
                ${nl2br(escapeHtml(reply.noi_dung))}
            </div>
            ${actionButtons}
        </div>
    `;
}

// Hàm hiển thị form phản hồi
function showReplyForm(button, commentId) {
    if (!isLoggedIn) {
        window.location.href = 'dangnhap.php';
        return;
    }

    // Tìm wrapper cho form phản hồi trong comment hiện tại
    const replyFormWrapper = $(button).closest('.comment-item').find('.reply-form-wrapper').first();
    
    // Ẩn tất cả các form phản hồi khác
    $('.reply-form-wrapper').not(replyFormWrapper).empty();
    
    // Nếu form đã tồn tại, ẩn nó đi
    if (replyFormWrapper.children().length > 0) {
        replyFormWrapper.empty();
        return;
    }

    // Lấy thông tin người được trả lời
    const commentAuthor = $(button).closest('.comment-item').find('.comment-author').text().trim();
    const commentContent = $(button).closest('.comment-item').find('.comment-content').text().trim();

    // Tạo form phản hồi - Thay đổi ở đây
    const replyFormHtml = `
        <div class="reply-form-container">
            <form class="reply-form">
                <textarea name="reply_content" placeholder="Nhập phản hồi của bạn..." required></textarea>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="$(this).closest('.reply-form-container').remove()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    `;

    // Chèn form vào wrapper
    replyFormWrapper.html(replyFormHtml);
    
    // Thêm event listener cho form - Thêm mới
    replyFormWrapper.find('form').on('submit', function(e) {
        e.preventDefault();
        const submitButton = $(this).find('button[type="submit"]');
        handleReplySubmit(submitButton[0], commentId, commentAuthor, commentContent);
    });
    
    // Focus vào textarea
    replyFormWrapper.find('textarea').focus();
}

// Cập nhật hàm handleReplySubmit
function handleReplySubmit(button, commentId, replyToAuthor, replyToContent) {
    const form = $(button).closest('form');
    const content = form.find('textarea[name="reply_content"]').val().trim();
    
    if (!content) {
        alert('Vui lòng nhập nội dung phản hồi');
        return;
    }

    // Disable nút gửi và hiển thị loading
    $(button).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'reply_comment',
            id_binh_luan_goc: commentId,
            noi_dung: content,
            ten_nguoi_duoc_tra_loi: replyToAuthor,
            noi_dung_tra_loi: replyToContent
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Tìm container chứa các phản hồi
                const repliesContainer = $(`#replies-${commentId}`);
                
                // Tạo HTML cho phản hồi mới
                const replyHtml = createReplyHTML(response.reply);
                
                // Thêm phản hồi mới vào container
                repliesContainer.append(replyHtml);
                
                // Xóa form phản hồi
                form.closest('.reply-form-container').remove();
                
                // Hiển thị thông báo thành công
                showNotification('success', 'Đã gửi phản hồi thành công');
            } else {
                showNotification('error', response.message || 'Có lỗi xảy ra khi gửi phản hồi');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi gửi phản hồi';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            showNotification('error', errorMessage);
        },
        complete: function() {
            // Enable nút gửi và reset text
            $(button).prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Gửi');
        }
    });
}

// Hàm hiển thị form chỉnh sửa
function showEditForm(commentId, content) {
    const commentContent = $(`#comment-content-${commentId}`);
    const currentContent = content.replace(/<br\s*\/?>/g, '\n');
    
    commentContent.html(`
        <form onsubmit="submitEdit(event, ${commentId})" class="edit-form">
            <textarea name="edit_content" required>${currentContent}</textarea>
            <div class="button-group">
                <button type="submit">Lưu</button>
                <button type="button" onclick="cancelEdit(${commentId}, '${content}')">Hủy</button>
            </div>
        </form>
    `);
}

// Hàm hủy chỉnh sửa
function cancelEdit(commentId, originalContent) {
    $(`#comment-content-${commentId}`).html(originalContent);
}

// Hàm submit chỉnh sửa bình luận
function submitEdit(event, commentId) {
    event.preventDefault();
    const form = event.target;
    const content = form.edit_content.value.trim();

    if (!content) {
        alert('Vui lòng nhập nội dung bình luận');
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'edit_comment',
            comment_id: commentId,
            noi_dung: content
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const commentContent = $(`#comment-content-${commentId}`);
                commentContent.html(response.comment.noi_dung);
            } else {
                alert(response.message || 'Có lỗi xảy ra khi cập nhật bình luận');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi cập nhật bình luận';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            alert(errorMessage);
        },
        complete: function() {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-save"></i> Lưu';
        }
    });
}

// Hàm xóa bình luận
function deleteComment(commentId) {
    if (!confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
        return;
    }

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'delete_comment',
            comment_id: commentId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Xóa phần tử HTML của bình luận
                $(`[data-id="${commentId}"]`).fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert(response.message || 'Có lỗi xảy ra khi xóa bình luận');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi xóa bình luận';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            alert(errorMessage);
        }
    });
}

// Hàm hiển thị form phản hồi
function showReplyForm(button, commentId) {
    if (!isLoggedIn) {
        window.location.href = 'dangnhap.php';
        return;
    }

    // Tìm wrapper cho form phản hồi trong comment hiện tại
    const replyFormWrapper = $(button).closest('.comment-item').find('.reply-form-wrapper').first();
    
    // Ẩn tất cả các form phản hồi khác
    $('.reply-form-wrapper').not(replyFormWrapper).empty();
    
    // Nếu form đã tồn tại, ẩn nó đi
    if (replyFormWrapper.children().length > 0) {
        replyFormWrapper.empty();
        return;
    }

    // Lấy thông tin người được trả lời
    const commentAuthor = $(button).closest('.comment-item').find('.comment-author').text().trim();
    const commentContent = $(button).closest('.comment-item').find('.comment-content').text().trim();

    // Tạo form phản hồi - Thay đổi ở đây
    const replyFormHtml = `
        <div class="reply-form-container">
            <form class="reply-form">
                <textarea name="reply_content" placeholder="Nhập phản hồi của bạn..." required></textarea>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="$(this).closest('.reply-form-container').remove()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    `;

    // Chèn form vào wrapper
    replyFormWrapper.html(replyFormHtml);
    
    // Thêm event listener cho form - Thêm mới
    replyFormWrapper.find('form').on('submit', function(e) {
        e.preventDefault();
        const submitButton = $(this).find('button[type="submit"]');
        handleReplySubmit(submitButton[0], commentId, commentAuthor, commentContent);
    });
    
    // Focus vào textarea
    replyFormWrapper.find('textarea').focus();
}

// Hủy sửa phản hồi
function cancelReplyEdit(replyId, originalContent) {
    $(`#reply-content-${replyId}`).html(originalContent);
}

// Submit sửa phản hồi
function submitReplyEdit(event, replyId) {
    event.preventDefault();
    const form = event.target;
    const content = form.edit_content.value.trim();

    if (!content) {
        alert('Vui lòng nhập nội dung phản hồi');
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'edit_reply',
            reply_id: replyId,
            noi_dung: content
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Tìm đến phần tử cha chứa form và ẩn form đi
                const replyItem = form.closest('.reply-item');
                const editForm = replyItem.querySelector('.edit-form');
                if (editForm) {
                    editForm.style.display = 'none';
                }
                
                // Hiển thị lại khung nội dung và cập nhật nội dung mới
                const contentContainer = replyItem.querySelector(`#reply-content-${replyId}`);
                if (contentContainer) {
                    contentContainer.style.display = 'block';
                    contentContainer.innerHTML = nl2br(escapeHtml(content));
                }
                
                showNotification('success', 'Đã cập nhật phản hồi thành công');
            } else {
                showNotification('error', response.message || 'Có lỗi xảy ra khi cập nhật phản hồi');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi cập nhật phản hồi';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            showNotification('error', errorMessage);
        },
        complete: function() {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-save"></i> Lưu thay đổi';
        }
    });
}

// Xóa phản hồi
function deleteReply(replyId) {
    if (!confirm('Bạn có chắc chắn muốn xóa phản hồi này?')) {
        return;
    }

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'delete_reply',
            reply_id: replyId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $(`[data-id="${replyId}"]`).fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert(response.message || 'Có lỗi xảy ra khi xóa phản hồi');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi xóa phản hồi';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            alert(errorMessage);
        }
    });
}

// Hiển thị form phản hồi cho phản hồi
function showReplyToReplyForm(commentId, replyId, replyAuthor) {
    if (!isLoggedIn) {
        window.location.href = 'dangnhap.php';
        return;
    }

    const replyItem = $(`[data-id="${replyId}"]`);
    const replyFormWrapper = replyItem.find('.reply-form-wrapper').first();
    
    // Ẩn tất cả form phản hồi khác
    $('.reply-form-wrapper').not(replyFormWrapper).empty();
    
    // Nếu form đã tồn tại, ẩn nó đi
    if (replyFormWrapper.children().length > 0) {
        replyFormWrapper.empty();
        return;
    }

    const replyFormHtml = `
        <div class="reply-form-container">
            <form class="reply-form">
                <div class="reply-to-info">
                    <i class="fas fa-reply"></i> Đang trả lời ${escapeHtml(replyAuthor)}
                </div>
                <textarea name="reply_content" placeholder="Nhập phản hồi của bạn..." required></textarea>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="$(this).closest('.reply-form-container').remove()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    `;

    replyFormWrapper.html(replyFormHtml);
    
    // Xử lý submit form
    replyFormWrapper.find('form').on('submit', function(e) {
        e.preventDefault();
        const submitButton = $(this).find('button[type="submit"]');
        handleReplySubmit(submitButton[0], commentId, replyAuthor, $(`#reply-content-${replyId}`).text().trim());
    });
    
    replyFormWrapper.find('textarea').focus();
}

// Hàm xử lý khi click nút phản hồi
function showReplyForm(button, commentId) {
    if (!isLoggedIn) {
        window.location.href = 'dangnhap.php';
        return;
    }

    // Tìm wrapper cho form phản hồi trong comment hiện tại
    const replyFormWrapper = $(button).closest('.comment-item').find('.reply-form-wrapper').first();
    
    // Ẩn tất cả các form phản hồi khác
    $('.reply-form-wrapper').not(replyFormWrapper).empty();
    
    // Nếu form đã tồn tại, ẩn nó đi
    if (replyFormWrapper.children().length > 0) {
        replyFormWrapper.empty();
        return;
    }

    // Lấy thông tin người được trả lời
    const commentAuthor = $(button).closest('.comment-item').find('.comment-author').text().trim();
    const commentContent = $(button).closest('.comment-item').find('.comment-content').text().trim();

    // Tạo form phản hồi
    const replyFormHtml = `
        <div class="reply-form-container">
            <form class="reply-form" onsubmit="submitReply(event, ${commentId}, '${escapeHtml(commentAuthor)}', '${escapeHtml(commentContent)}')">
                <textarea name="reply_content" placeholder="Nhập phản hi của bạn..." required></textarea>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="$(this).closest('.reply-form-container').remove()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    `;

    // Chèn form vào wrapper
    replyFormWrapper.html(replyFormHtml);
    
    // Focus vào textarea
    replyFormWrapper.find('textarea').focus();
}

// Hàm xử lý khi click nút Gửi trong form phản hồi
function handleReplySubmit(button, commentId, replyToAuthor, replyToContent) {
    event.preventDefault(); // Ngăn form submit

    const form = $(button).closest('form');
    const content = form.find('textarea[name="reply_content"]').val().trim();
    
    if (!content) {
        alert('Vui lòng nhập nội dung phản hồi');
        return;
    }

    // Disable nút gửi và hiển thị loading
    $(button).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'reply_comment',
            id_binh_luan_goc: commentId,
            noi_dung: content,
            ten_nguoi_duoc_tra_loi: replyToAuthor,
            noi_dung_tra_loi: replyToContent
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Tìm container chứa các phản hồi
                const repliesContainer = $(`#replies-${commentId}`);
                
                // Tạo HTML cho phản hồi mới
                const replyHtml = createReplyHTML(response.reply);
                
                // Thêm phản hồi mới vào container
                repliesContainer.append(replyHtml);
                
                // Xóa form phản hồi
                form.closest('.reply-form-container').remove();
                
                // Hiển thị thông báo thành công
                showNotification('success', 'Đã gửi phản hồi thành công');
            } else {
                showNotification('error', response.message || 'Có lỗi xảy ra khi gửi phản hồi');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi gửi phản hồi';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            showNotification('error', errorMessage);
        },
        complete: function() {
            // Enable nút gửi và reset text
            $(button).prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Gửi');
        }
    });
}

// Hàm tạo HTML cho phản hồi mới
function createReplyHTML(reply) {
    const currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    const isReplyOwner = currentUserId === parseInt(reply.id_nguoi_dung);
    
    const avatarHtml = reply.user_avatar 
        ? `<img src="${escapeHtml(reply.user_avatar)}" alt="${escapeHtml(reply.ho_ten)}" class="user-avatar">` 
        : `<div class="reply-avatar">${escapeHtml(reply.ho_ten.substring(0, 2).toUpperCase())}</div>`;

    return `
        <div class="reply-item" data-id="${reply.id}">
            <div class="reply-header">
                ${avatarHtml}
                <div class="reply-meta">
                    <div class="reply-author">${escapeHtml(reply.ho_ten)}</div>
                    <div class="reply-date">${reply.ngay_tao}</div>
                </div>
            </div>
            <div class="reply-content" id="reply-content-${reply.id}">
                ${nl2br(escapeHtml(reply.noi_dung))}
            </div>
            <div class="reply-actions">
                ${isReplyOwner ? `
                    <button class="edit-btn" onclick="showReplyEditForm(${reply.id}, '${escapeHtml(reply.noi_dung)}')">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="delete-btn" onclick="deleteReply(${reply.id})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                ` : ''}
                <button class="reply-btn" onclick="showReplyForm(this, ${reply.id_binh_luan_goc})">
                    <i class="fas fa-reply"></i> Phản hồi
                </button>
            </div>
            <div class="reply-form-wrapper"></div>
        </div>
    `;
}

// Ẩn form phản hồi cho phản hồi
function hideReplyToReplyForm(replyId) {
    $(`#replyToReplyForm-${replyId}`).remove();
}

// Hàm tiện ích
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function nl2br(str) {
    return str.replace(/\n/g, '<br>');
}

// Thêm hàm hiển thị thông báo
function showNotification(type, message) {
    const notificationClass = type === 'success' ? 'success-notification' : 'error-notification';
    const notification = $(`
        <div class="${notificationClass}">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(() => {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

document.getElementById('searchButton').addEventListener('click', function() {
    const searchQuery = document.getElementById('templeSearch').value.toLowerCase();
    
    // Gửi request AJAX để tìm kiếm
    $.ajax({
        url: 'search_temples.php',
        method: 'POST',
        data: { query: searchQuery },
        success: function(results) {
            const templesGrid = document.querySelector('.temples-grid');
            
            // Thêm class search-results
            templesGrid.classList.add('search-results');
            templesGrid.innerHTML = ''; // Xóa nội dung hiện tại
            
            results.forEach(temple => {
                // Tạo temple card với cấu trúc giống hệt ban đầu
                const templeCard = `
                    <div class="temple-card">
                        <div class="temple-image">
                            <img src="${temple.image}" alt="${temple.name}">
                        </div>
                        <div class="temple-info">
                            <h3>${temple.name}</h3>
                            <p class="temple-address">
                                <i class="fas fa-map-marker-alt"></i>
                                Địa chỉ: ${temple.address}
                            </p>
                            <div class="temple-details">
                                <p><i class="fas fa-user"></i> Trụ trì: ${temple.monk}</p>
                                <p><i class="fas fa-phone"></i> Điện thoại: ${temple.phone}</p>
                                <p><i class="fas fa-envelope"></i> Email: ${temple.email}</p>
                            </div>
                            <p class="temple-description">
                                ${temple.description}
                            </p>
                            <a href="chitietchua.php?id=${temple.id}" class="read-more-btn">Xem thêm</a>
                        </div>
                    </div>
                `;
                
                templesGrid.innerHTML += templeCard;
            });
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
});

// Hàm hiển thị form phản hồi
function showReplyForm(button, commentId) {
    if (!isLoggedIn) {
        window.location.href = 'dangnhap.php';
        return;
    }

    // Tìm wrapper cho form phản hồi trong comment hiện tại
    const replyFormWrapper = $(button).closest('.comment-item').find('.reply-form-wrapper').first();
    
    // Ẩn tất cả các form phản hồi khác
    $('.reply-form-wrapper').not(replyFormWrapper).empty();
    
    // Nếu form đã tồn tại, ẩn nó đi
    if (replyFormWrapper.children().length > 0) {
        replyFormWrapper.empty();
        return;
    }

    // Lấy thông tin người được trả lời
    const commentAuthor = $(button).closest('.comment-item').find('.comment-author').text().trim();
    const commentContent = $(button).closest('.comment-item').find('.comment-content').text().trim();

    // Tạo form phản hồi
    const replyFormHtml = `
        <div class="reply-form-container">
            <form class="reply-form" onsubmit="return false">
                <textarea name="reply_content" placeholder="Nhập phản hồi của bạn..." required></textarea>
                <div class="button-group">
                    <button type="button" class="btn btn-primary send-reply" onclick="handleReplySubmit(this, ${commentId}, '${escapeHtml(commentAuthor)}', '${escapeHtml(commentContent)}')">
                        <i class="fas fa-paper-plane"></i> Gửi
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="$(this).closest('.reply-form-container').remove()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    `;

    // Chèn form vào wrapper
    replyFormWrapper.html(replyFormHtml);
    
    // Focus vào textarea
    replyFormWrapper.find('textarea').focus();
}

// Hàm xử lý submit phản hồi
function handleReplySubmit(button, commentId, replyToAuthor, replyToContent) {
    const form = $(button).closest('form');
    const content = form.find('textarea[name="reply_content"]').val().trim();
    
    if (!content) {
        alert('Vui lòng nhập nội dung phản hồi');
        return;
    }

    // Disable nút gửi và hiển thị loading
    $(button).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'reply_comment',
            id_binh_luan_goc: commentId,
            noi_dung: content,
            ten_nguoi_duoc_tra_loi: replyToAuthor,
            noi_dung_tra_loi: replyToContent
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Tìm container chứa các phản hồi
                const repliesContainer = $(`#replies-${commentId}`);
                
                // Tạo HTML cho phản hồi mới
                const replyHtml = createReplyHTML(response.reply);
                
                // Thêm phản hồi mới vào container
                repliesContainer.append(replyHtml);
                
                // Xóa form phản hồi
                form.closest('.reply-form-container').remove();
                
                // Hiển thị thông báo thành công
                showNotification('success', 'Đã gửi phản hồi thành công');
            } else {
                showNotification('error', response.message || 'Có lỗi xảy ra khi gửi phản hồi');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi gửi phản hồi';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            showNotification('error', errorMessage);
        },
        complete: function() {
            // Enable nút gửi và reset text
            $(button).prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Gửi');
        }
    });
}
// Hàm hiển thị form phản hồi
function showReplyForm(button, commentId) {
    if (!isLoggedIn) {
        window.location.href = 'dangnhap.php';
        return;
    }

    // Tìm wrapper cho form phản hồi trong comment hiện tại
    const replyFormWrapper = $(button).closest('.comment-item').find('.reply-form-wrapper').first();
    
    // Ẩn tất cả các form phản hồi khác
    $('.reply-form-wrapper').not(replyFormWrapper).empty();
    
    // Nếu form đã tồn tại, ẩn nó đi
    if (replyFormWrapper.children().length > 0) {
        replyFormWrapper.empty();
        return;
    }

    // Lấy thông tin người được trả lời
    const commentAuthor = $(button).closest('.comment-item').find('.comment-author').text().trim();
    const commentContent = $(button).closest('.comment-item').find('.comment-content').text().trim();

    // Tạo form phản hồi
    const replyFormHtml = `
        <div class="reply-form-container">
            <form class="reply-form" onsubmit="return false">
                <textarea name="reply_content" placeholder="Nhập phản hồi của bạn..." required></textarea>
                <div class="button-group">
                    <button type="button" class="btn btn-primary send-reply" onclick="handleReplySubmit(this, ${commentId}, '${escapeHtml(commentAuthor)}', '${escapeHtml(commentContent)}')">
                        <i class="fas fa-paper-plane"></i> Gửi
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="$(this).closest('.reply-form-container').remove()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    `;

    // Chèn form vào wrapper
    replyFormWrapper.html(replyFormHtml);
    // Focus vào textarea
    replyFormWrapper.find('textarea').focus();
}

// Hàm xử lý submit phản hồi
function handleReplySubmit(button, commentId, replyToAuthor, replyToContent) {
    const form = $(button).closest('form');
    const content = form.find('textarea[name="reply_content"]').val().trim();
    
    if (!content) {
        alert('Vui lòng nhập nội dung phản hồi');
        return;
    }

    // Disable nút gửi và hiển thị loading
    $(button).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'reply_comment',
            id_binh_luan_goc: commentId,
            noi_dung: content,
            ten_nguoi_duoc_tra_loi: replyToAuthor,
            noi_dung_tra_loi: replyToContent
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Tìm container chứa các phản hồi
                const repliesContainer = $(`#replies-${commentId}`);
                
                // Tạo HTML cho phản hồi mới
                const replyHtml = createReplyHTML(response.reply);
                
                // Thêm phản hồi mới vào container
                repliesContainer.append(replyHtml);
                
                // Xóa form phản hồi
                form.closest('.reply-form-container').remove();
                
                // Hiển thị thông báo thành công
                showNotification('success', 'Đã gửi phản hồi thành công');
            } else {
                showNotification('error', response.message || 'Có lỗi xảy ra khi gửi phản hồi');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi gửi phản hồi';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            showNotification('error', errorMessage);
        },
        complete: function() {
            // Enable nút gửi và reset text
            $(button).prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Gửi');
        }
    });
}

// Các hàm hỗ trợ
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function nl2br(str) {
    return str.replace(/\n/g, '<br>');
}

function showNotification(type, message) {
    const notificationClass = type === 'success' ? 'success-notification' : 'error-notification';
    const notification = $(`
        <div class="${notificationClass}">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(() => {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}


// phản hồi
$(document).ready(function() {
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!isLoggedIn) {
            window.location.href = 'dangnhap.php';
            return;
        }

        const form = $(this);
        const submitButton = form.find('button[type="submit"]');
        const content = form.find('textarea[name="noi_dung"]').val().trim();
        
        if (!content) {
            alert('Vui lòng nhập nội dung bình luận');
            return;
        }

        submitButton.prop('disabled', true);
        submitButton.html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'comment',
                noi_dung: content
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Tạo HTML cho bình luận mới
                    const commentHtml = createCommentHTML(response.comment);
                    
                    // Thêm bình luận mới vào đầu danh sách
                    const newComment = $(commentHtml).prependTo('.comments-list');
                    
                    // Gán sự kiện cho nút phản hồi trong bình luận mới
                    newComment.find('.reply-btn').on('click', function() {
                        showReplyForm(this, response.comment.id);
                    });
                    
                    // Reset form
                    form[0].reset();
                    
                    // Hiển thị thông báo thành công
                    showNotification('success', 'Đã thêm bình luận thành công');
                } else {
                    showNotification('error', response.message || 'Có lỗi xảy ra khi gửi bình luận');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Có lỗi xảy ra khi gửi bình luận';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch(e) {}
                showNotification('error', errorMessage);
            },
            complete: function() {
                submitButton.prop('disabled', false);
                submitButton.html('Gửi bình luận');
            }
        });
    });
});

// Sửa lại hàm showReplyForm
function showReplyForm(button, commentId) {
    if (!isLoggedIn) {
        window.location.href = 'dangnhap.php';
        return;
    }

    const replyFormWrapper = $(button).closest('.comment-item').find('.reply-form-wrapper').first();
    
    // Ẩn tất cả form phản hồi khác
    $('.reply-form-wrapper').not(replyFormWrapper).empty();
    
    // Nếu form đã tồn tại, ẩn nó đi
    if (replyFormWrapper.children().length > 0) {
        replyFormWrapper.empty();
        return;
    }

    const replyFormHtml = `
        <div class="reply-form-container">
            <form class="reply-form">
                <textarea name="reply_content" placeholder="Nhập phản hồi của bạn..." required></textarea>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="$(this).closest('.reply-form-container').remove()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    `;

    replyFormWrapper.html(replyFormHtml);
    
    // Xử lý submit form
    replyFormWrapper.find('form').on('submit', function(e) {
        e.preventDefault();
        const submitButton = $(this).find('button[type="submit"]');
        const content = $(this).find('textarea[name="reply_content"]').val().trim();
        handleReplySubmit(submitButton[0], commentId, content);
    });
    
    replyFormWrapper.find('textarea').focus();
}

// Sửa lại hàm showReplyToReplyForm
function showReplyToReplyForm(commentId, replyId, replyAuthor) {
    if (!isLoggedIn) {
        window.location.href = 'dangnhap.php';
        return;
    }

    const replyItem = $(`[data-id="${replyId}"]`);
    const replyFormWrapper = replyItem.find('.reply-form-wrapper').first();
    
    // Ẩn tất cả form phản hồi khác
    $('.reply-form-wrapper').not(replyFormWrapper).empty();
    
    // Nếu form đã tồn tại, ẩn nó đi
    if (replyFormWrapper.children().length > 0) {
        replyFormWrapper.empty();
        return;
    }

    const replyFormHtml = `
        <div class="reply-form-container">
            <form class="reply-form">
                <textarea name="reply_content" placeholder="Nhập phản hồi của bạn..." required></textarea>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="$(this).closest('.reply-form-container').remove()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    `;

    replyFormWrapper.html(replyFormHtml);
    
    // Xử lý submit form
    replyFormWrapper.find('form').on('submit', function(e) {
        e.preventDefault();
        const submitButton = $(this).find('button[type="submit"]');
        const content = $(this).find('textarea[name="reply_content"]').val().trim();
        handleReplySubmit(submitButton[0], commentId, content);
    });
    
    replyFormWrapper.find('textarea').focus();
}

// Hàm hiển thị form chỉnh sửa bình luận
function showEditForm(commentId, content) {
    const commentContent = $(`#comment-content-${commentId}`);
    // Lấy nội dung hiện tại và xử lý định dạng
    const currentContent = commentContent.html()
        .replace(/<br\s*\/?>/g, '\n')
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .trim(); // Thêm trim() để loại bỏ khoảng trắng thừa

    commentContent.attr('data-original', commentContent.html());
    
    const editFormHtml = `<form class="edit-form" onsubmit="return submitEdit(${commentId}, this)">
<textarea name="noi_dung" required>${currentContent}</textarea>
<div class="button-group">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Lưu thay đổi
    </button>
    <button type="button" class="btn btn-secondary" onclick="cancelEdit(${commentId})">
        <i class="fas fa-times"></i> Hủy
    </button>
</div>
</form>`;
    
    commentContent.html(editFormHtml);
    const textarea = commentContent.find('textarea')[0];
    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length); // Đặt con trỏ ở cuối
}
// Hàm xử lý submit chỉnh sửa bình luận
function submitEdit(commentId, form) {
    const submitBtn = $(form).find('button[type="submit"]');
    const textarea = $(form).find('textarea');
    const content = textarea.val().trim();
    
    if (!content) {
        showNotification('error', 'Vui lòng nhập nội dung bình luận');
        return false;
    }
    
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');
    
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'edit_comment',
            comment_id: commentId,
            noi_dung: content
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const commentContent = $(`#comment-content-${commentId}`);
                commentContent.html(response.comment.noi_dung);
                
                // Cập nhật thời gian chỉnh sửa
                const timeElement = commentContent.siblings('.comment-meta').find('.edit-time');
                if (timeElement.length) {
                    timeElement.text(`Đã chỉnh sửa lúc ${response.comment.ngay_cap_nhat}`);
                } else {
                    commentContent.siblings('.comment-meta').append(
                        `<span class="edit-time"> • Đã chỉnh sửa lúc ${response.comment.ngay_cap_nhat}</span>`
                    );
                }
                
                showNotification('success', response.message);
            } else {
                showNotification('error', response.message);
                cancelEdit(commentId);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi cập nhật bình luận';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            showNotification('error', errorMessage);
            cancelEdit(commentId);
        }
    });
    
    return false;
}

// Hàm hiển thị form chỉnh sửa phản hồi
function showReplyEditForm(replyId, content) {
    const replyContent = $(`#reply-content-${replyId}`);
    const currentContent = replyContent.html()
        .replace(/<br\s*\/?>/g, '\n')
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .trim();
    
    replyContent.attr('data-original', replyContent.html());
    
    const editFormHtml = `<form class="edit-form" onsubmit="return submitReplyEdit(${replyId}, this)">
<textarea name="noi_dung" required>${currentContent}</textarea>
<div class="button-group">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Lưu thay đổi
    </button>
    <button type="button" class="btn btn-secondary" onclick="cancelReplyEdit(${replyId})">
        <i class="fas fa-times"></i> Hủy
    </button>
</div>
</form>`;   
    replyContent.html(editFormHtml);
    const textarea = replyContent.find('textarea')[0];
    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length); // Đặt con trỏ ở cuối
}
// Hàm xử lý submit chỉnh sửa phản hồi
function submitReplyEdit(replyId, form) {
    const submitBtn = $(form).find('button[type="submit"]');
    const textarea = $(form).find('textarea');
    const content = textarea.val().trim(); 
    if (!content) {
        showNotification('error', 'Vui lòng nhập nội dung phản hồi');
        return false;
    }
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');  
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'edit_reply',
            reply_id: replyId,
            noi_dung: content
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const replyContent = $(`#reply-content-${replyId}`);
                replyContent.html(response.reply.noi_dung);
                
                // Cập nhật thời gian chỉnh sửa
                const timeElement = replyContent.siblings('.reply-meta').find('.edit-time');
                if (timeElement.length) {
                    timeElement.text(`Đã chỉnh sửa lúc ${response.reply.ngay_cap_nhat}`);
                } else {
                    replyContent.siblings('.reply-meta').append(
                        `<span class="edit-time"> • Đã chỉnh sửa lúc ${response.reply.ngay_cap_nhat}</span>`
                    );
                } 
                showNotification('success', response.message);
            } else {
                showNotification('error', response.message);
                cancelReplyEdit(replyId);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi cập nhật phản hồi';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            showNotification('error', errorMessage);
            cancelReplyEdit(replyId);
        }
    });   
    return false;
}
// Hàm hủy chỉnh sửa bình luận
function cancelEdit(commentId) {
    const commentContent = $(`#comment-content-${commentId}`);
    commentContent.html(commentContent.attr('data-original'));
}
// Hàm hủy chỉnh sửa phản hồi
function cancelReplyEdit(replyId) {
    const replyContent = $(`#reply-content-${replyId}`);
    replyContent.html(replyContent.attr('data-original'));
}
// Hàm hiển thị thông báo
function showNotification(type, message) {
    const notification = $(`
        <div class="${type}-notification">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        </div>
    `).appendTo('body');
    
    setTimeout(() => {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}
/*Tìm kiếm*/
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('templeSearch');
    const searchButton = document.getElementById('searchButton');
    const templesGrid = document.querySelector('.temples-grid');
    const templeCards = document.querySelectorAll('.temple-card');  
    // Hàm tìm kiếm
    function searchTemples() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let hasResults = false;
        templeCards.forEach(card => {
            const templeName = card.querySelector('h3').textContent.toLowerCase();
            const templeAddress = card.querySelector('.temple-address').textContent.toLowerCase();
            // Kiểm tra nếu searchTerm có trong tên hoặc địa chỉ
            const isMatch = templeName.includes(searchTerm) || templeAddress.includes(searchTerm);
            if (isMatch) {
                card.style.display = '';
                hasResults = true;
            } else {
                card.style.display = 'none';
            }
        }); 
        // Hiển thị thông báo không tìm thấy kết quả
        let noResultsMsg = templesGrid.querySelector('.no-results');
        if (!hasResults) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results';
                noResultsMsg.innerHTML = `
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>Không tìm thấy chùa nào phù hợp với từ khóa "${searchInput.value}"</p>
                `;
                templesGrid.appendChild(noResultsMsg);
            }
            noResultsMsg.style.display = 'block';
        } else if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
    // Xử lý sự kiện click nút tìm kiếm
    searchButton.addEventListener('click', searchTemples);
    // Xử lý sự kiện nhấn Enter trong input
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchTemples();
        }
    });
    // Xử lý sự kiện input để reset kết quả tìm kiếm khi xóa hết text
    searchInput.addEventListener('input', function() {
        if (this.value === '') {
            templeCards.forEach(card => card.style.display = '');
            const noResultsMsg = templesGrid.querySelector('.no-results');
            if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        }
    });
});
</script>
</body>
</html>
