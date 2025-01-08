<?php
session_start();
// Kiểm tra session
if (!isset($_SESSION['admin_id'])) {
    header('Location: dangnhapAdmin.php');
    exit();
}
// Thêm vào đầu file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Trong phần xử lý phản hồi, thêm log
error_log("POST data: " . print_r($_POST, true));
// Cấu hình database và upload
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'chua_khmer');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Kết nối CSDL
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$danhSachBinhLuan = getComments($conn);
error_log("Số lượng bình luận: " . count($danhSachBinhLuan));
error_log("Dữ liệu bình luận: " . print_r($danhSachBinhLuan, true));

// Đảm bảo có kết nối database
if (!isset($conn)) {
    die("Không có kết nối database");
}


// Thêm vào đầu file sau phần kết nối database
if (isset($_POST['action']) && $_POST['action'] === 'phan_hoi_binh_luan') {
    header('Content-Type: application/json');
    
    try {
        // Kiểm tra dữ liệu đầu vào
        if (!isset($_POST['comment_id']) || !isset($_POST['phan_hoi'])) {
            throw new Exception('Thiếu thông tin cần thiết');
        }

        $commentId = (int)$_POST['comment_id'];
        $phanHoi = trim($_POST['phan_hoi']);
        
        if (empty($phanHoi)) {
            throw new Exception('Vui lòng nhập nội dung phản hồi');
        }

        // Log để debug
        error_log("Processing comment reply - ID: $commentId, Content: $phanHoi");

        $sql = "UPDATE binh_luan 
                SET phan_hoi = ?, 
                    nguoi_phan_hoi = 'Quản trị viên', 
                    ngay_cap_nhat = NOW() 
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Lỗi prepare statement: ' . $conn->error);
        }

        $stmt->bind_param("si", $phanHoi, $commentId);
        
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi cập nhật: ' . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Phản hồi thành công',
                'phan_hoi' => $phanHoi,
                'nguoi_phan_hoi' => 'Quản trị viên',
                'ngay_cap_nhat' => date('d/m/Y H:i')
            ]);
            error_log("Reply updated successfully");
        } else {
            throw new Exception('Không tìm thấy bình luận hoặc không có thay đổi');
        }

    } catch (Exception $e) {
        error_log("Error in comment reply: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}


// Helper Functions
function uploadImage($file, $uploadDir = UPLOAD_DIR) {
    if (!isset($file) || $file['error'] !== 0) {
        return null;
    }

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $targetFile = $uploadDir . uniqid() . '.' . $imageFileType;
    
    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        throw new Exception('Không thể tải lên tệp');
    }

    return $targetFile;
}

// Xử lý tìm kiếm
$timKiem = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClauseNguoiDung = '';
$whereClauseChua = '';

if ($timKiem) {
    $timKiem = $conn->real_escape_string($timKiem);
    $whereClauseNguoiDung = "WHERE ho_ten LIKE '%$timKiem%' 
                            OR email LIKE '%$timKiem%' 
                            OR ten_dang_nhap LIKE '%$timKiem%'";
}

// Lấy danh sách
$sql = "SELECT * FROM su_kien ORDER BY id DESC";
$result = $conn->query($sql);
$su_kien_list = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $su_kien_list[] = $row;
    }
}

$sql = "SELECT * FROM nguoi_dung $whereClauseNguoiDung ORDER BY ngay_tao DESC";
$danhSachNguoiDung = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT * FROM dschua $whereClauseChua ORDER BY ngay_them DESC";
$danhSachChua = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$danhSachBinhLuan = getComments($conn);

// XỬ LÝ CHÙA
/// Xử lý thêm mới hoặc sửa chùa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['them_chua']) || isset($_POST['sua_chua'])) {
        try {
            $tenChua = trim($_POST['ten_chua']);
            $diaChi = trim($_POST['dia_chi']);
            $dienThoai = trim($_POST['dien_thoai']);
            $email = trim($_POST['email']);
            $trangThai = isset($_POST['trang_thai']) ? (int)$_POST['trang_thai'] : 1;

            // Xử lý upload ảnh nếu có
            $targetFile = null;
            if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
                $targetFile = uploadImage($_FILES['hinh_anh']);
            }

            // Kiểm tra dữ liệu đầu vào
            if (empty($tenChua) || empty($diaChi)) {
                throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
            }

            if (isset($_POST['them_chua'])) {
                // Thêm mới chùa
                $sql = $targetFile 
                    ? "INSERT INTO dschua (ten_chua, dia_chi, dien_thoai, email, hinh_anh, trang_thai, ngay_them) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW())"
                    : "INSERT INTO dschua (ten_chua, dia_chi, dien_thoai, email, trang_thai, ngay_them) 
                       VALUES (?, ?, ?, ?, ?, NOW())";

                $stmt = $conn->prepare($sql);
                
                if ($targetFile) {
                    $stmt->bind_param("sssssi", 
                        $tenChua, $diaChi, $dienThoai, 
                        $email, $targetFile, $trangThai
                    );
                } else {
                    $stmt->bind_param("ssssi", 
                        $tenChua, $diaChi, $dienThoai, 
                        $email, $trangThai
                    );
                }

                if (!$stmt->execute()) {
                    throw new Exception("Lỗi khi thêm chùa: " . $stmt->error);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Thêm chùa mới thành công'
                ]);

            } elseif (isset($_POST['sua_chua'])) {
                // Sửa chùa
                $chuaId = (int)$_POST['chua_id'];
                $sql = $targetFile 
                    ? "UPDATE dschua SET ten_chua=?, dia_chi=?, dien_thoai=?, email=?, hinh_anh=?, trang_thai=? WHERE id=?"
                    : "UPDATE dschua SET ten_chua=?, dia_chi=?, dien_thoai=?, email=?, trang_thai=? WHERE id=?";

                $stmt = $conn->prepare($sql);
                
                if ($targetFile) {
                    $stmt->bind_param("sssssii", 
                        $tenChua, $diaChi, $dienThoai, 
                        $email, $targetFile, $trangThai, $chuaId
                    );
                } else {
                    $stmt->bind_param("ssssii", 
                        $tenChua, $diaChi, $dienThoai, 
                        $email, $trangThai, $chuaId
                    );
                }

                if (!$stmt->execute()) {
                    throw new Exception("Lỗi khi sửa chùa: " . $stmt->error);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật chùa thành công'
                ]);
            }

        } catch (Exception $e) {
            // Xóa file ảnh nếu có lỗi xảy ra
            if (isset($targetFile) && file_exists($targetFile)) {
                unlink($targetFile);
            }
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
}

// Xử lý sửa chùa
if (isset($_POST['sua_chua'])) {
    try {
        $chuaId = $_POST['chua_id'];
        $tenChua = trim($_POST['ten_chua']);
        $diaChi = trim($_POST['dia_chi']);
        $dienThoai = trim($_POST['dien_thoai']);
        $email = trim($_POST['email']);
        $trangThai = isset($_POST['trang_thai']) ? (int)$_POST['trang_thai'] : 1;

        // Kiểm tra dữ liệu đầu vào
        if (empty($chuaId) || empty($tenChua) || empty($diaChi)) {
            throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
        }

        // Xử lý upload ảnh mới nếu có
        $targetFile = null;
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
            $targetFile = uploadImage($_FILES['hinh_anh']);
            
            // Lấy và xóa ảnh cũ
            $sql = "SELECT hinh_anh FROM dschua WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $chuaId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (!empty($row['hinh_anh']) && file_exists($row['hinh_anh'])) {
                    unlink($row['hinh_anh']);
                }
            }
        }

        // Cập nhật thông tin chùa
        if ($targetFile) {
            $sql = "UPDATE dschua SET 
                    ten_chua = ?, 
                    dia_chi = ?,  
                    dien_thoai = ?, 
                    email = ?, 
                    hinh_anh = ?,
                    trang_thai = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssii", 
                $tenChua, $diaChi, $dienThoai, 
                $email, $targetFile, $trangThai, $chuaId
            );
        } else {
            $sql = "UPDATE dschua SET 
                    ten_chua = ?, 
                    dia_chi = ?, 
                    dien_thoai = ?, 
                    email = ?, 
                    trang_thai = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssii", 
                $tenChua, $diaChi, $dienThoai, 
                $email, $trangThai, $chuaId
            );
        }

        if (!$stmt->execute()) {
            throw new Exception("Lỗi khi cập nhật thông tin chùa: " . $stmt->error);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật thông tin chùa thành công'
        ]);

    } catch (Exception $e) {
        // Xóa file ảnh mới nếu có lỗi xảy ra
        if (isset($targetFile) && file_exists($targetFile)) {
            unlink($targetFile);
        }
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Trong phần xử lý POST request
if (isset($_POST['xoa_chua'])) {
    $chuaId = $_POST['id'];
    
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    try {
        // Xóa chi tiết chùa trước
        $sqlXoaChiTiet = "DELETE FROM chitiet_chua WHERE chua_id = ?";
        $stmtXoaChiTiet = $conn->prepare($sqlXoaChiTiet);
        $stmtXoaChiTiet->bind_param("i", $chuaId);
        $stmtXoaChiTiet->execute();
        
        // Sau đó xóa chùa
        $sqlXoaChua = "DELETE FROM dschua WHERE id = ?";
        $stmtXoaChua = $conn->prepare($sqlXoaChua);
        $stmtXoaChua->bind_param("i", $chuaId);
        $stmtXoaChua->execute();
        
        // Nếu không có lỗi, commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Xóa chùa và nội dung chi tiết thành công'
        ]);
    } catch (Exception $e) {
        // Nếu có lỗi, rollback transaction
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý xóa ảnh chùa
if (isset($_POST['xoa_anh_chua'])) {
    header('Content-Type: application/json');
    try {
        $id = (int)$_POST['id'];
        
        $sql = "SELECT hinh_anh FROM dschua WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Không tìm thấy thông tin chùa');
        }
        
        $chua = $result->fetch_assoc();
        $oldImage = $chua['hinh_anh'];
        
        if ($oldImage && file_exists($oldImage)) {
            unlink($oldImage);
        }
        
        $sql = "UPDATE dschua SET hinh_anh = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa ảnh thành công'
            ]);
        } else {
            throw new Exception('Không thể cập nhật thông tin chùa');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý thay đổi trạng thái chùa
if (isset($_POST['thay_doi_trang_thai_chua'])) {
    header('Content-Type: application/json');
    try {
        $chuaId = (int)$_POST['chua_id'];
        $trangThai = (int)$_POST['trang_thai'];
        
        $check_sql = "SELECT id FROM dschua WHERE id = ? LIMIT 1";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $chuaId);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows === 0) {
            throw new Exception('Chùa không tồn tại');
        }
        
        $sql = "UPDATE dschua SET trang_thai = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $trangThai, $chuaId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        } else {
            throw new Exception('Không thể cập nhật trạng thái');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// XỬ LÝ SỰ KIỆN
// Xử lý thêm sự kiện
if (isset($_POST['them_su_kien']) || isset($_POST['sua_su_kien'])) {
    header('Content-Type: application/json');
    try {
        $suKienId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;
        
        // Xử lý upload ảnh
        $hinhAnh = null;
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/events/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileInfo = pathinfo($_FILES['hinh_anh']['name']);
            $fileName = uniqid() . '.' . $fileInfo['extension'];
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $uploadFile)) {
                $hinhAnh = $uploadFile;
            } else {
                throw new Exception('Không thể upload file');
            }
        }
        
        if ($suKienId) {
            // Cập nhật sự kiện
            $sql = "UPDATE su_kien SET 
                    ten_su_kien = ?, 
                    y_nghia = ?,
                    thoi_gian_to_chuc = ?,
                    cac_nghi_thuc = ?,
                    am_thuc_truyen_thong = ?,
                    luu_y_khach_tham_quan = ?";
            
            if ($hinhAnh) {
                $sql .= ", hinh_anh = ?";
            }
            
            $sql .= " WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            
            if ($hinhAnh) {
                $stmt->bind_param("sssssssi", 
                    $_POST['ten_su_kien'],
                    $_POST['y_nghia'],
                    $_POST['thoi_gian_to_chuc'],
                    $_POST['cac_nghi_thuc'],
                    $_POST['am_thuc_truyen_thong'],
                    $_POST['luu_y_khach_tham_quan'],
                    $hinhAnh,
                    $suKienId
                );
            } else {
                $stmt->bind_param("ssssssi", 
                    $_POST['ten_su_kien'],
                    $_POST['y_nghia'],
                    $_POST['thoi_gian_to_chuc'],
                    $_POST['cac_nghi_thuc'],
                    $_POST['am_thuc_truyen_thong'],
                    $_POST['luu_y_khach_tham_quan'],
                    $suKienId
                );
            }
        } else {
            // Thêm sự kiện mới
            $sql = "INSERT INTO su_kien (
                ten_su_kien, y_nghia, thoi_gian_to_chuc, 
                cac_nghi_thuc, am_thuc_truyen_thong, 
                luu_y_khach_tham_quan, hinh_anh
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", 
                $_POST['ten_su_kien'],
                $_POST['y_nghia'],
                $_POST['thoi_gian_to_chuc'],
                $_POST['cac_nghi_thuc'],
                $_POST['am_thuc_truyen_thong'],
                $_POST['luu_y_khach_tham_quan'],
                $hinhAnh
            );
        }
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => $suKienId ? 'Cập nhật lễ hội thành công' : 'Thêm lễ hội thành công'
            ]);
        } else {
            throw new Exception('Không thể ' . ($suKienId ? 'cập nhật' : 'thêm') . ' sự kiện');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý sửa sự kiện
if (isset($_POST['sua_su_kien'])) {
    header('Content-Type: application/json');
    try {
        $id = (int)$_POST['event_id'];
        $ten_su_kien = trim($_POST['ten_su_kien']);
        $y_nghia = trim($_POST['y_nghia']);
        $thoi_gian_to_chuc = trim($_POST['thoi_gian_to_chuc']);
        $cac_nghi_thuc = trim($_POST['cac_nghi_thuc']);
        $am_thuc_truyen_thong = trim($_POST['am_thuc_truyen_thong']);
        $luu_y_khach_tham_quan = trim($_POST['luu_y_khach_tham_quan']);

        if (empty($ten_su_kien)) {
            throw new Exception('Vui lòng nhập tên sự kiện');
        }

        $check_sql = "SELECT id FROM su_kien WHERE id = ? LIMIT 1";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows === 0) {
            throw new Exception('Sự kiện không tồn tại');
        }

        $sql = "UPDATE su_kien SET 
                ten_su_kien = ?, y_nghia = ?, thoi_gian_to_chuc = ?, 
                cac_nghi_thuc = ?, am_thuc_truyen_thong = ?, 
                luu_y_khach_tham_quan = ? WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", 
            $ten_su_kien, $y_nghia, $thoi_gian_to_chuc,
            $cac_nghi_thuc, $am_thuc_truyen_thong, $luu_y_khach_tham_quan,
            $id
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật lễ hội thành công'
            ]);
        } else {
            throw new Exception('Không thể cập nhật lễ hội: ' . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý xóa sự kiện
if (isset($_POST['xoa_su_kien'])) {
    header('Content-Type: application/json');
    try {
        $eventId = (int)$_POST['id'];
        
        // Kiểm tra sự kiện có tồn tại không
        $check_sql = "SELECT id FROM su_kien WHERE id = ? LIMIT 1";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $eventId);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows === 0) {
            throw new Exception('Sự kiện không tồn tại');
        }
        
        // Thực hiện xóa
        $sql = "DELETE FROM su_kien WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa lễ hội thành công'
            ]);
        } else {
            throw new Exception('Không thể xóa lễ hội: ' . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý thay đổi trạng thái sự kiện
if (isset($_POST['thay_doi_trang_thai_su_kien'])) {
    header('Content-Type: application/json');
    try {
        // Thêm log để debug
        error_log("Received event status change request: " . print_r($_POST, true));
        
        if (!isset($_POST['event_id']) || !isset($_POST['trang_thai'])) {
            throw new Exception('Thiếu thông tin cần thiết');
        }

        $suKienId = (int)$_POST['event_id'];
        $trangThai = (int)$_POST['trang_thai'];
        
        // Log thông tin
        error_log("Processing event ID: $suKienId, New status: $trangThai");
        
        // Kiểm tra sự kiện tồn tại
        $check_sql = "SELECT id FROM su_kien WHERE id = ? LIMIT 1";
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            throw new Exception('Lỗi prepare statement: ' . $conn->error);
        }
        
        $check_stmt->bind_param("i", $suKienId);
        if (!$check_stmt->execute()) {
            throw new Exception('Lỗi kiểm tra sự kiện: ' . $check_stmt->error);
        }
        
        if ($check_stmt->get_result()->num_rows === 0) {
            throw new Exception('Sự kiện không tồn tại');
        }
        
        // Cập nhật trạng thái
        $sql = "UPDATE su_kien SET trang_thai = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Lỗi prepare statement: ' . $conn->error);
        }
        
        $stmt->bind_param("ii", $trangThai, $suKienId);
        
        if (!$stmt->execute()) {
            throw new Exception('Lỗi cập nhật trạng thái: ' . $stmt->error);
        }
        
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật trạng thái lễ hội thành công'
            ]);
            error_log("Event status updated successfully");
        } else {
            throw new Exception('Không có thay đổi nào được thực hiện');
        }
        
    } catch (Exception $e) {
        error_log("Error in event status update: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý lấy thông tin sự kiện
if (isset($_POST['lay_thong_tin_su_kien'])) {
    header('Content-Type: application/json');
    try {
        $suKienId = (int)$_POST['event_id'];
        
        $sql = "SELECT * FROM su_kien WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $suKienId);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $suKien = $result->fetch_assoc();
            
            if ($suKien) {
                echo json_encode([
                    'success' => true,
                    'data' => $suKien
                ]);
            } else {
                throw new Exception('Không tìm thấy sự kiện');
            }
        } else {
            throw new Exception('Không thể lấy thông tin sự kiện');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}


// XỬ LÝ NGƯỜI DÙNG VÀ BÌNH LUẬN
function getComments($conn) {
    try {
        $sql = "SELECT bl.*, 
                nd.ho_ten as ten_nguoi_dung,
                nd.email as email_nguoi_dung,
                nd.ten_dang_nhap
                FROM binh_luan bl
                LEFT JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id 
                ORDER BY bl.ngay_tao DESC";
        
        $result = $conn->query($sql);
        
        if (!$result) {
            error_log("Lỗi truy vấn: " . $conn->error);
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Lỗi trong getComments: " . $e->getMessage());
        return [];
    }
}

// Xử lý xóa người dùng
if (isset($_POST['xoa_nguoi_dung'])) {
    header('Content-Type: application/json');
    try {
        $userId = (int)$_POST['user_id'];
        
        $sql = "SELECT id, vai_tro FROM nguoi_dung WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) {
            throw new Exception('Người dùng không tồn tại');
        }
        
        if ($user['vai_tro'] == 1) {
            throw new Exception('Không thể xóa tài khoản admin');
        }
        
        $sql = "DELETE FROM nguoi_dung WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa người dùng thành công'
            ]);
        } else {
            throw new Exception('Không thể xóa người dùng');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý xóa bình luận
if (isset($_POST['xoa_binh_luan'])) {
    header('Content-Type: application/json');
    try {
        $commentId = (int)$_POST['comment_id'];
        
        $sql = "DELETE FROM binh_luan WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $commentId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa bình luận thành công'
            ]);
        } else {
            throw new Exception('Không thể xóa bình luận');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Sửa lại phần xử lý phản hồi bình luận
if (isset($_POST['action']) && $_POST['action'] === 'phan_hoi_binh_luan') {
    header('Content-Type: application/json');
    try {
        if (!isset($_POST['comment_id']) || !isset($_POST['phan_hoi'])) {
            throw new Exception('Thiếu thông tin cần thiết');
        }

        $commentId = (int)$_POST['comment_id'];
        $phanHoi = trim($_POST['phan_hoi']);
        
        if (empty($phanHoi)) {
            throw new Exception('Vui lòng nhập nội dung phản hồi');
        }

        // Log để debug
        error_log("Processing comment reply - ID: $commentId, Content: $phanHoi");

        $sql = "UPDATE binh_luan 
                SET phan_hoi = ?, 
                    nguoi_phan_hoi = 'Quản trị viên', 
                    ngay_cap_nhat = NOW() 
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Lỗi prepare statement: ' . $conn->error);
        }

        $stmt->bind_param("si", $phanHoi, $commentId);
        
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi cập nhật: ' . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Phản hồi thành công',
                'phan_hoi' => $phanHoi,
                'nguoi_phan_hoi' => 'Quản trị viên',
                'ngay_cap_nhat' => date('d/m/Y H:i')
            ]);
            error_log("Reply updated successfully");
        } else {
            throw new Exception('Không tìm thấy bình luận hoặc không có thay đổi');
        }

    } catch (Exception $e) {
        error_log("Error in comment reply: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

/* Trang thai binh luan */
if (isset($_POST['action']) && $_POST['action'] === 'cap_nhat_trang_thai_binh_luan') {
    $commentId = $_POST['comment_id'];
    $trangThai = $_POST['trang_thai'];
    
    try {
        // Thực hiện cập nhật trong database
        $sql = "UPDATE binh_luan SET trang_thai = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$trangThai, $commentId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý thêm/sửa chi tiết chùa
if (isset($_POST['action']) && $_POST['action'] === 'them_chi_tiet_chua') {
    try {
        $ten_chua = $_POST['ten_chua'];
        
        // Kiểm tra xem chùa có tồn tại không
        $check_chua = "SELECT id FROM dschua WHERE ten_chua = ?";
        $stmt = $conn->prepare($check_chua);
        $stmt->bind_param("s", $ten_chua);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy thông tin chùa'
            ]);
            exit;
        }
        
        $chua = $result->fetch_assoc();
        $chua_id = $chua['id'];
        
        // Lấy dữ liệu từ form
        $gioi_thieu = $_POST['gioi_thieu'] ?? '';
        $lich_su = $_POST['lich_su'] ?? '';
        $kien_truc = $_POST['kien_truc'] ?? '';
        $di_tich = $_POST['di_tich'] ?? '';
        $le_hoi = $_POST['le_hoi'] ?? '';
        $video_gioi_thieu = $_POST['video_gioi_thieu'] ?? '';

        // Xử lý upload ảnh
        $hinh_anh_gioi_thieu = '';
        $hinh_anh_lich_su = '';
        $hinh_anh_kien_truc = '';
        $hinh_anh_di_tich = '';

        // Upload ảnh giới thiệu
        if(isset($_FILES['hinh_anh_gioi_thieu']) && $_FILES['hinh_anh_gioi_thieu']['error'] == 0) {
            $target_dir = "uploads/";
            $file_extension = pathinfo($_FILES['hinh_anh_gioi_thieu']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            if(move_uploaded_file($_FILES['hinh_anh_gioi_thieu']['tmp_name'], $target_file)) {
                $hinh_anh_gioi_thieu = $target_file;
            }
        }

        // Upload ảnh lịch sử
        if(isset($_FILES['hinh_anh_lich_su']) && $_FILES['hinh_anh_lich_su']['error'] == 0) {
            $target_dir = "uploads/";
            $file_extension = pathinfo($_FILES['hinh_anh_lich_su']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            if(move_uploaded_file($_FILES['hinh_anh_lich_su']['tmp_name'], $target_file)) {
                $hinh_anh_lich_su = $target_file;
            }
        }

        // Upload ảnh kiến trúc
        if(isset($_FILES['hinh_anh_kien_truc']) && $_FILES['hinh_anh_kien_truc']['error'] == 0) {
            $target_dir = "uploads/";
            $file_extension = pathinfo($_FILES['hinh_anh_kien_truc']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            if(move_uploaded_file($_FILES['hinh_anh_kien_truc']['tmp_name'], $target_file)) {
                $hinh_anh_kien_truc = $target_file;
            }
        }

        // Upload ảnh di tích
        if(isset($_FILES['hinh_anh_di_tich']) && $_FILES['hinh_anh_di_tich']['error'] == 0) {
            $target_dir = "uploads/";
            $file_extension = pathinfo($_FILES['hinh_anh_di_tich']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            if(move_uploaded_file($_FILES['hinh_anh_di_tich']['tmp_name'], $target_file)) {
                $hinh_anh_di_tich = $target_file;
            }
        }

        // Kiểm tra xem đã có chi tiết chưa
        $check_sql = "SELECT id FROM chitiet_chua WHERE chua_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $chua_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            // Cập nhật
            $sql = "UPDATE chitiet_chua SET 
                    gioi_thieu = ?, 
                    lich_su = ?, 
                    kien_truc = ?, 
                    di_tich = ?, 
                    le_hoi = ?, 
                    video_gioi_thieu = ?,
                    hinh_anh_gioi_thieu = COALESCE(NULLIF(?, ''), hinh_anh_gioi_thieu),
                    hinh_anh_lich_su = COALESCE(NULLIF(?, ''), hinh_anh_lich_su),
                    hinh_anh_kien_truc = COALESCE(NULLIF(?, ''), hinh_anh_kien_truc),
                    hinh_anh_di_tich = COALESCE(NULLIF(?, ''), hinh_anh_di_tich)
                    WHERE chua_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssi", 
                $gioi_thieu, 
                $lich_su, 
                $kien_truc, 
                $di_tich, 
                $le_hoi, 
                $video_gioi_thieu,
                $hinh_anh_gioi_thieu,
                $hinh_anh_lich_su,
                $hinh_anh_kien_truc,
                $hinh_anh_di_tich,
                $chua_id
            );
            $message = 'Cập nhật chi tiết chùa thành công';
        } else {
            // Thêm mới
            $sql = "INSERT INTO chitiet_chua (chua_id, gioi_thieu, lich_su, kien_truc, di_tich, le_hoi, video_gioi_thieu, 
                    hinh_anh_gioi_thieu, hinh_anh_lich_su, hinh_anh_kien_truc, hinh_anh_di_tich) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssssssss", 
                $chua_id, 
                $gioi_thieu, 
                $lich_su, 
                $kien_truc, 
                $di_tich, 
                $le_hoi, 
                $video_gioi_thieu,
                $hinh_anh_gioi_thieu,
                $hinh_anh_lich_su,
                $hinh_anh_kien_truc,
                $hinh_anh_di_tich
            );
            $message = 'Thêm chi tiết chùa thành công';
        }

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => $message
            ]);
        } else {
            throw new Exception($stmt->error);
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ]);
    }
    exit;
}

// PHP xử lý lưu chi tiết chùa
if (isset($_POST['action']) && $_POST['action'] === 'luu_chi_tiet_chua') {
    header('Content-Type: application/json');
    try {
        if (empty($_POST['chua_id'])) {
            throw new Exception('Thiếu thông tin ID chùa');
        }

        $chuaId = (int)$_POST['chua_id'];
        
        // Các trường cần cập nhật
        $fields = [
            'gioi_thieu',
            'lich_su',
            'kien_truc',
            'di_tich',
            'le_hoi',
            'video_gioi_thieu',
            'hinh_anh_gioi_thieu',
            'hinh_anh_lich_su',
            'hinh_anh_kien_truc',
            'hinh_anh_di_tich'
        ];
        
        // Kiểm tra xem đã có chi tiết chùa chưa
        $checkSql = "SELECT id FROM chitiet_chua WHERE chua_id = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("i", $chuaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // UPDATE - Cập nhật dữ liệu hiện có
            $updateFields = [];
            $updateValues = [];
            $types = "";
            
            foreach ($fields as $field) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                    $target_dir = "uploads/";
                    $file_extension = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_file)) {
                        $updateFields[] = "$field = ?";
                        $updateValues[] = $target_file;
                        $types .= "s";
                    }
                } elseif (isset($_POST[$field])) {
                    $updateFields[] = "$field = ?";
                    $updateValues[] = $_POST[$field];
                    $types .= "s";
                }
            }
            
            $sql = "UPDATE chitiet_chua SET " . implode(", ", $updateFields) . " WHERE chua_id = ?";
            $updateValues[] = $chuaId;
            $types .= "i";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Lỗi prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param($types, ...$updateValues);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi khi cập nhật: " . $stmt->error);
            }
        } else {
            // INSERT - Thêm mới dữ liệu
            $insertFields = ['chua_id'];
            $insertValues = [$chuaId];
            $placeholders = ['?'];
            $types = "i";
            
            foreach ($fields as $field) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                    $target_dir = "uploads/";
                    $file_extension = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_file)) {
                        $insertFields[] = $field;
                        $insertValues[] = $target_file;
                        $placeholders[] = "?";
                        $types .= "s";
                    }
                } elseif (isset($_POST[$field])) {
                    $insertFields[] = $field;
                    $insertValues[] = $_POST[$field];
                    $placeholders[] = "?";
                    $types .= "s";
                }
            }
            
            $sql = "INSERT INTO chitiet_chua (" . implode(", ", $insertFields) . ") 
                    VALUES (" . implode(", ", $placeholders) . ")";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Lỗi prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param($types, ...$insertValues);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi khi thêm mới: " . $stmt->error);
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Lưu thông tin chi tiết thành công'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý đăng xuất
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: dangnhapAdmin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Trị - Chùa Khmer</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
/* Reset và biến CSS */
:root {
    --primary-color: #4a90e2;
    --secondary-color: #f5f6fa;
    --danger-color: #e74c3c;
    --success-color: #2ecc71;
    --warning-color: #f1c40f;
    --text-color: #2c3e50;
    --border-color: #e1e8ef;
    --sidebar-width: 250px;
    --header-height: 60px;
    --border-radius: 8px;
    --transition: all 0.3s ease;
    --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Thêm font chữ mới và style mặc định */
* {
    font-family: 'Montserrat', 'Roboto', sans-serif;
    font-weight: 600;
}

/* Cập nhật style cho headings */
h1, h2, h3, h4, h5, h6 {
    font-family: 'Montserrat', 'Roboto', sans-serif;
    font-weight: 700;
}

/* Cập nhật style cho labels */
.form-group label {
    font-family: 'Montserrat', 'Roboto', sans-serif;
    font-weight: 600;
    font-size: 15px;
    color: var(--text-color);
}

/* Cập nhật style cho inputs và textareas */
input, textarea, select {
    font-family: 'Montserrat', 'Roboto', sans-serif;
    font-weight: 500;
}

/* Cập nhật style cho buttons */
button, .btn {
    font-family: 'Montserrat', 'Roboto', sans-serif;
    font-weight: 600;
}

/* Cập nhật style cho table */
table {
    font-family: 'Montserrat', 'Roboto', sans-serif;
    font-weight: 500;
}

/* Cập nhật style cho table headers */
table th {
    font-weight: 700;
}

/* Cập nhật style cho table cells */
table td {
    font-weight: 500;
}


/* Cập nhật Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    overflow-y: auto;
    padding: 20px;
}

.modal-content {
    position: relative;
    background: #fff;
    width: 95%;
    max-width: 900px;
    margin: 20px auto;
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--box-shadow);
}

/* Style cho modal title */
#modalTitle {
    color: #000; /* Thay đổi từ var(--primary-color) thành màu đen */
    font-size: 28px;
    font-weight: 600;
    text-align: center;
    margin: 0 auto 30px;
    padding: 15px 30px;
    border: 2px solid #000;
    border-radius: var(--border-radius);
    background: #fff;
    min-width: 300px;
    display: block;
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
}

/* Container cho modal title */
.modal-title-container {
    width: 100%;
    text-align: center;
    margin-bottom: 30px;
}

/* Style cho labels */
.form-group label {
    display: block;
    margin-bottom: 6px; /* Giảm margin dưới */
    font-weight: 500;
    color: var(--text-color);
    font-size: 16px; /* Giảm kích thước font */
}

/* Style cho dấu sao required */
.form-group label .required {
    color: var(--danger-color);
    margin-left: 3px;
    font-size: 11px; /* Giảm kích thước dấu sao */
}

/* Điều chỉnh khong cách giữa các form groups */
.form-group {
    margin-bottom: 15px; /* Giảm margin bottom */
}

/* Điều chỉnh kích thước input fields */
.form-group input {
    width: 97%;
    min-width: 300px; /* Đảm bảo độ rộng tối thiểu */
    height: 38px; /* Tăng chiều cao */
    padding: 8px 15px;
    font-size: 14px;
}

/* Điều chỉnh textarea */
.form-group textarea {
    width: 97%;
    min-height: 100px;
    padding: 12px 15px;
    font-size: 14px;
    line-height: 1.6;
    border: 2px solid #000;
    border-radius: var(--border-radius);
    resize: vertical;
}

/* Điều chỉnh container chứa input */
.form-group .input-container {
    flex: 1;
    min-width: 8px; /* Đảm bảo độ rộng tối thiểu */
}

/* Điều chỉnh style khi focus */
.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    outline: none;
}

/* Điều chỉnh style cho placeholder */
.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #999;
    font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="tel"],
.form-group select {
    width: 97%;
    height: 40px;
    padding: 8px 15px;
    font-size: 14px;
    border: 2px solid #000;
    border-radius: var(--border-radius);
}

/* Style riêng cho select trạng thái */
#eventForm select#trang_thai {
    width: 200px; /* Giảm độ rộng */
    min-width: 150px; /* Đảm bảo không quá nhỏ */
    height: 40px;
    padding: 8px 15px;
    font-size: 14px;
    border: 2px solid #000;
    border-radius: var(--border-radius);
    background-color: white;
    cursor: pointer;
    display: block; /* Đảm bảo select box hiển thị đúng */
}

/* Đảm bảo container của trạng thái không bị ảnh hưởng bởi style chung */
#eventForm .form-group.status-group {
    width: auto;
    margin-bottom: 20px;
}

/* Responsive cho select trạng thái */
@media (max-width: 768px) {
    #eventForm select#trang_thai {
        width: 150px;
    }
}

/* Style riêng cho select trạng thái trong cả hai form */
#formChua select#trang_thai,
#eventForm select#trang_thai {
    width: 200px; /* Giảm độ rộng */
    min-width: 150px; /* Đảm bảo không quá nhỏ */
    height: 40px;
    padding: 8px 15px;
    font-size: 14px;
    border: 2px solid #000;
    border-radius: var(--border-radius);
    background-color: white;
    cursor: pointer;
    display: block; /* Đảm bảo select box hiển thị đúng */
}

/* Đảm bảo container của trạng thái không bị ảnh hưởng bởi style chung */
#formChua .form-group.status-group,
#eventForm .form-group.status-group {
    width: auto;
    margin-bottom: 20px;
}

/* Responsive cho select trạng thái */
@media (max-width: 768px) {
    #formChua select#trang_thai,
    #eventForm select#trang_thai {
        width: 150px;
    }
}

/* Hover và Focus states cho textarea */
.data-table textarea:hover {
    border-color: var(--primary-color);
}
/* Style cho textarea trong bảng */
.data-table textarea {
    width: 97%;
    min-height: 100px;
    padding: 12px 15px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 14px;
    line-height: 1.6;
    resize: vertical;
    background-color: #fff;
    transition: var(--transition);
}

/* Cập nht bảng dữ liệu */
.data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 10px;
}

.data-table thead th {
    background: var(--primary-color);
    color: #fff;
    padding: 18px;
    font-weight: 700;
    text-align: left;
    font-size: 14px;
    border-top: none;
    position: sticky;
    top: 0;
    z-index: 10;
    text-transform: none; /* Remove automatic capitalization */
    letter-spacing: 0.5px;
}
.data-table td {
    padding: 15px;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}

.data-table thead th:first-child {
    border-top-left-radius: var(--border-radius);
}

.data-table thead th:last-child {
    border-top-right-radius: var(--border-radius);
}

.data-table tbody tr {
    transition: var(--transition);
    font-size: 18px;
}

.data-table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Style cho text content trong cột nội dung */
.data-table td:nth-child(6) .content-text {
    white-space: pre-line;
    line-height: 1.6;
    max-height: 97px;
    overflow-y: auto;
}
/* Đặt buttons trong table cell */
.data-table td:last-child {
    display: flex;
    gap: 5px;
    justify-content: flex-start;
    align-items: center;
    padding: 8px 15px;
    white-space: normal;
    max-width: none;
}

/* Scrollbar tùy chỉnh cho textarea và content */
.data-table textarea::-webkit-scrollbar,
.data-table td:nth-child(6) .content-text::-webkit-scrollbar {
    width: 8px;
}

.data-table textarea::-webkit-scrollbar-track,
.data-table td:nth-child(6) .content-text::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.data-table textarea::-webkit-scrollbar-thumb,
.data-table td:nth-child(6) .content-text::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.data-table textarea::-webkit-scrollbar-thumb:hover,
.data-table td:nth-child(6) .content-text::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.data-table th,
.data-table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Định dạng hình ảnh */
.temple-image {
    max-width: 100px;
    height: auto;
    border-radius: 4px;
    cursor: pointer;
    transition: transform 0.2s;
}

.temple-image:hover {
    transform: scale(1.1);
}

/* Upload ảnh */
.image-upload-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: var(--border-radius);
    border: 2px solid #000; /* Thêm viền đen */
    text-align: center;
    margin-top: 10px;
}

.image-preview {
    width: 200px;
    height: 150px;
    margin: 15px auto;
    border-radius: var(--border-radius);
    overflow: hidden;
    background: #fff;
    box-shadow: var(--box-shadow);
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Buttons trong form actions */
.form-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
}

.btn {
    padding: 5px 10px; /* Tăng padding cho nút */
    border-radius: var(--border-radius);
    font-weight: 400;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    border: 2px solid #000; /* Viền đen */
}

/* Icon trong button */
.btn i {
    font-size: 16px;
}
/* Nút Lưu trong form */
.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: #357abd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 144, 226, 0.2);
}

/* Nút Thêm chùa mới */
.btn-add {
    background: white !important;
    color: black !important;
    border: 2px solid #000;
}

.btn-add:hover {
    background: #f0f0f0 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Nút Xóa */
.btn-danger {
    background: var(--danger-color);
    color: #ffffff; /* Màu chữ trắng */
    font-weight: bold; /* Làm đậm chữ */
}

.btn-danger:hover {
    background: #c0392b;
    color: #ffffff; /* Gi màu chữ trắng khi hover */
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.2);
}

/* Nút Hủy */
.btn-secondary {
    background: black; /* Màu xám nhạt */
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d; /* Màu đậm hơn khi hover */
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(149, 165, 166, 0.2);
}
/* Nút Sửa */
.btn-warning {
    background: var(--warning-color);
    color: #000000; /* Màu chữ đen */
    font-weight: bold; /* Làm đậm chữ */
}

.btn-warning:hover {
    background: #f39c12;
    color: #000000; /* Giữ màu chữ đen khi hover */
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(241, 196, 15, 0.3);
}

/* Hiệu ứng khi button bị disable */
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none !important;
}

/* Style cho tất cả các buttons trong bảng */
.data-table .btn {
    padding: 6px 6px !important; /* Giảm padding ngang */
    font-size: 12px !important;
    min-width: 35px; /* Giảm độ rộng tối thiểu */
    height: 28px;
    line-height: 1;
    white-space: nowrap; /* Ngăn text xuống dòng */
}

.data-table .btn i {
    font-size: 10px !important;
    margin-right: 2px; /* Giảm khoảng cách giữa icon và text */
}

/* Điều chỉnh khoảng cách giữa các nút */
.data-table td:last-child {
    display: flex;
    gap: 2px; /* Giảm khoảng cách giữa các nút */
    align-items: center;
    padding: 4px 6px; /* Giảm padding của cell */
    white-space: nowrap;
}

/* Điều chỉnh các nút cụ thể */
.data-table .btn-warning,
.data-table .btn-danger,
.data-table .btn-primary {
    min-width: 25px; /* Giảm độ rộng tối thiểu cho các nút cụ thể */
}

/* Thêm style cho các nút có icon + text */
.data-table .btn-with-icon {
    padding-left: 4px !important;
    padding-right: 4px !important;
}

/* Container cho input file */
.file-input-container {
    border: 2px solid #000;
    padding: 16px;
    border-radius: var(--border-radius);
    background: white;
    width: 97%;
    margin: 10px 0;
    display: inline-block;
}

/* Style cho input file */
input[type="file"] {
    width: 100%;
    cursor: pointer;
    padding: 8px 0;
}

/* Style cho button "Choose File" */
::-webkit-file-upload-button {
    border: 2px solid #000;
    padding: 8px 16px;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
    font-weight: 500;
    margin-right: 10px;
    transition: all 0.3s ease;
}

::-webkit-file-upload-button:hover {
    background: #f0f0f0;
}

/* Style cho Firefox */
::file-selector-button {
    border: 2px solid #000;
    padding: 8px 16px;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
    font-weight: 500;
    margin-right: 10px;
    transition: all 0.3s ease;
}

::file-selector-button:hover {
    background: #f0f0f0;
}

/* Ẩn thanh progress mặc định */
input[type="file"]::-webkit-progress-bar {
    display: none;
}

input[type="file"]::-webkit-progress-value {
    display: none;
}

input[type="file"]::-webkit-progress {
    display: none;
}

/* Responsive cho buttons */
@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
        margin-bottom: 10px;
    }
    
    .data-table td:last-child {
        flex-direction: column;
        gap: 8px;
    }
    
    .data-table .btn {
        width: 100%;
        margin: 0;
    }
}
/* Search Container */
.search-container {
    position: relative;
    display: flex;
    align-items: center;
    margin: 10px 0px;
}

.search-input {
    padding: 8px 12px 8px 35px;
    border: 2px solid #2c3e50; /* Thêm viền đen */
    border-radius: 4px;
    font-size: 14px;
    width: 140px;
    transition: all 0.3s ease;
    background-color: white;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    width: 300px;
}

.search-icon {
    position: absolute;
    left: 10px;
    color: #2c3e50; /* Đổi màu icon thành đen */
    font-size: 14px;
}

/* Thêm hover effect */
.search-input:hover {
    border-color: var(--primary-color);
}
/* Style cho placeholder */
.search-input::placeholder {
    color: #000000; /* Thay đổi từ #999 thnh #000000 */
    font-size: 14px;
}

/* Sidebar Styles */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 280px;
    height: 100vh;
    background: #888;
}

.sidebar-header {
    padding: 30px 20px;
    text-align: center;
    background: #888;
}

/* Cập nhật màu cho logo/avatar */
.admin-avatar {
    width: 80px;
    height: 80px;
    margin: 0 auto 25px;
    background: #ffffff; /* Màu vàng của logo */
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: 2px solid black;  /* Thêm viền đen với độ dày 2px */
    border-radius: 50%; 
}

.admin-avatar i {
    font-size: 35px;
    color: #000; /* Màu đen cho icon */
}

/* Cập nhật style cho nút đăng xuất */
.logout-btn {
    color: #c0392b;
    margin-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    font-weight: bold;
}

.logout-btn i {
    color: #fff; /* Icon cũng có màu vàng */
}

.logout-btn:hover i {
    color: #fff; /* Icon chuyển sang màu trắng khi hover */
}

.sidebar-header h2 {
    margin: 0;
    font-size: 27px;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.admin-title {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.7);
    margin: 5px 0 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.sidebar-menu {
    list-style: none;
    padding: 20px 0;
    margin: 0;
}

/* Sidebar menu styles */
.menu-item {
    margin: 10px 0;
}

.menu-item a {
    display: flex;
    align-items: center;
    padding: 10px 65px;
    color: #ffffff;
    text-decoration: none;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
}

.menu-item i {
    margin-right: 20px;
    width: 20px;
    text-align: center;
}

/* Style riêng cho menu link (trang chủ) */
.menu-link {
    transition: all 0.3s ease;
}

.menu-link:hover {
    background: #7f8c8d;
    color: #fff;
    transform: translateX(5px);
}

.menu-link.active {
    background: rgba(255, 255, 255, 0.2);
    border-left: 4px solid #fff;
}

/* Style riêng cho logout link (không có hover) */
.logout-link {
    cursor: pointer;
}
/* Điều chỉnh main content để không bị đè bởi sidebar */
.main-content {
    margin-left: 280px;
    padding: 20px;
    transition: all 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        width: 0;
        overflow: hidden;
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .sidebar.active {
        width: 280px;
    }
}

.xoa-nguoi-dung {
    padding: 8px 12px !important; /* Giảm padding */
    font-size: 14px !important; /* Giảm kích thước chữ */
}

.xoa-nguoi-dung i {
    font-size: 12px !important; /* Giảm kích thước icon */
}
/* Style cho buttons trong bảng sự kiện */
.sua-su-kien, .xoa-su-kien {
    padding: 8px 12px !important; /* Giảm padding */
    font-size: 14px !important; /* Giảm kích thước ch */
}

.sua-su-kien i, .xoa-su-kien i {
    font-size: 12px !important; /* Giảm kích thước icon */
}
/* Style cho tất cả các buttons trong bảng */
.data-table .btn {
    padding: 8px 12px !important; /* Giảm padding */
    font-size: 14px !important; /* Giảm kích thước chữ */
}

.data-table .btn i {
    font-size: 12px !important; /* Giảm kích thước icon */
}


/* Specific styles for each button type if needed */
.data-table .btn-warning,
.data-table .btn-danger {
    min-width: 80px; /* Đảm bảo độ rộng tối thiểu */
}

/* Điều chỉnh khoảng cách giữa các nút */
.data-table td:last-child {
    display: flex;
    gap: 5px;
    align-items: center;
}
/* Style cho phản hồi bình luận */
.reply-container {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.admin-reply-text {
    flex: 1;
    height: 36px; /* Giảm chiều cao để vừa với nút */
    padding: 8px;
    border: 2px solid #000;
    border-radius: var(--border-radius);
    resize: none;
    font-size: 14px;
}

.btn-save-reply {
    padding: 8px 12px;
    background: var(--primary-color);
    color: white;
    border: 2px solid #000;
    border-radius: var(--border-radius);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    white-space: nowrap;
    height: 36px; /* Đảm bảo chiều cao bằng với textarea */
}

.btn-save-reply:hover {
    background: #357abd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 144, 226, 0.2);
}

.btn-save-reply i {
    font-size: 12px;
}

/* Style cho khung nhập phản hồi */
.admin-reply-text {
    width: 100%;
    min-width: 100px;
    max-width: 100px;
    height: 60px;
    padding: 8px;
    border: 2px solid #000;
    border-radius: var(--border-radius);
    font-size: 10px;              /* Giảm cỡ chữ xuống 10px cho phản hồi */
    line-height: 1.3;             /* Khoảng cách dòng phù hợp */
    background-color: #fff;
    resize: none;
}

/* Responsive cho màn hình lớn */
@media (max-width: 1200px) {
    .content-text,
    .admin-reply-text {
        min-width: 150px;
        max-width: 250px;
        height: 50px;
    }
}

/* Responsive cho màn hình nhỏ */
@media (max-width: 768px) {
    .content-text,
    .admin-reply-text {
        min-width: 120px;
        max-width: 200px;
        height: 45px;
    }
}

/* Style cho form phản hồi */
.comment-display {
    width: 97%;
    min-height: 50px;
    padding: 12px 15px;
    border: 2px solid #000;
    border-radius: var(--border-radius);
    background-color: #f8f9fa;
    font-size: 14px;
    line-height: 1.6;
    white-space: pre-wrap;
    word-wrap: break-word;
    margin-bottom: 15px;
}

#reply_content {
    width: 97%;
    min-height: 100px;
    padding: 12px 15px;
    border: 2px solid #000;
    border-radius: var(--border-radius);
    font-size: 14px;
    line-height: 1.6;
    resize: vertical;
    background-color: #fff;
    transition: var(--transition);
}

#reply_content:hover {
    border-color: var(--primary-color);
}

#reply_content:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    outline: none;
}
h2 {
    font-size: 30px;
}

/* Style cho hình ảnh sự kiện */
.event-image {
    max-width: 100px;
    max-height: 100px;
    object-fit: cover;
    border-radius: 5px;
}

.image-upload-container {
    margin-top: 10px;
}

.image-preview {
    margin-top: 15px;
    max-width: 200px;
    max-height: 150px;
    overflow: hidden;
    border: 2px solid #000; /* Change to solid black border */
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
#btnThemSuKien {
    background-color: #ffffff;
    color: #000000;
    border: 1px solid #ddd;
    padding: 6px 7px;
    border-radius: 2px;
    cursor: pointer;
    font-weight: 500;
    border: 1px solid #000000; 
    border-radius: 8px;  
}

#btnThemSuKien:hover {
    background-color: #f0f0f0;
}

#btnThemSuKien i {
    color: #000000;
    margin-right: 1px;
}
.button-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-add-details {
    background: white !important;
    color: black !important;
    border: 2px solid #000;
}

.btn-add-details:hover {
    background: #f0f0f0 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>   
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="admin-avatar">
        <i class="fas fa-shield-alt"></i>  
        </div>
             <h2>Quản Trị Viên</h2>
        </div>
    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="index.php">
                <i class="fas fa-home"></i>
                <span>Trang chủ</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="?action=logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </a>
        </li>
    </ul>
</div>

<div class="main-content">
    <!-- Bảng quản lý người dùng -->
<div class="table-container">
    <div class="section-header">
        <h2>QUẢN LÝ NGƯỜI DÙNG</h2>
        <div class="search-container">
            <input type="text" id="timKiemNguoiDung" class="search-input" placeholder="Tìm kiếm người dùng">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tên đăng nhập</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($danhSachNguoiDung as $nguoiDung): ?>
            <tr>
                <td><?php echo htmlspecialchars($nguoiDung['ten_dang_nhap']); ?></td>
                <td><?php echo htmlspecialchars($nguoiDung['ho_ten']); ?></td>
                <td><?php echo htmlspecialchars($nguoiDung['email']); ?></td>
                <td><?php echo $nguoiDung['vai_tro'] == 1 ? 'Admin' : 'Người dùng'; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($nguoiDung['ngay_tao'])); ?></td>
                <td>
                    <?php if($nguoiDung['vai_tro'] != 1): ?>
                        <button class="btn btn-danger xoa-nguoi-dung" data-id="<?php echo $nguoiDung['id']; ?>">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="table-container">
<!-- HTML structure -->
<div class="table-container">
    <div class="section-header">
        <h2>QUẢN LÝ CHÙA</h2>
        <div class="button-group">
            <button class="btn btn-primary btn-add">
                <i class="fas fa-plus"></i> Thêm chùa mới
            </button>      
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tên chùa</th>
                <th>Địa chỉ</th>
                <th>Điện thoại</th>
                <th>Email</th>
                <th>Hình ảnh</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($danhSachChua as $chua): ?>
            <tr>
                <td><?php echo htmlspecialchars($chua['ten_chua']); ?></td>
                <td><?php echo htmlspecialchars($chua['dia_chi']); ?></td>
                <td><?php echo htmlspecialchars($chua['dien_thoai']); ?></td>
                <td><?php echo htmlspecialchars($chua['email']); ?></td>
                <td>
                    <?php if($chua['hinh_anh']): ?>
                        <img src="<?php echo htmlspecialchars($chua['hinh_anh']); ?>" alt="Hình ảnh chùa" class="temple-image">
                    <?php endif; ?>
                </td>
                <td>
                    <select class="trang-thai-chua" data-id="<?php echo $chua['id']; ?>">
                        <option value="1" <?php echo $chua['trang_thai'] == 1 ? 'selected' : ''; ?>>Hiển thị</option>
                        <option value="0" <?php echo $chua['trang_thai'] == 0 ? 'selected' : ''; ?>>Ẩn</option>
                    </select>
                </td>
                <td>
                <button class="btn btn-warning sua-chua" data-id="<?php echo $chua['id']; ?>">
                    <i class="fas fa-edit"></i> Sửa
                </button>
                <button class="btn btn-danger xoa-chua" data-id="<?php echo $chua['id']; ?>">
                    <i class="fas fa-trash"></i> Xóa
                </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modalChua" class="modal">
    <div class="modal-content">
        <div class="modal-title-container">
            <h2 id="modalTitle">Sửa thông tin chùa</h2>
        </div>
        
        <div class="form-container">
        <form id="formChua" enctype="multipart/form-data">
    <input type="hidden" id="chua_id" name="chua_id">
    
    <div class="form-group">
        <label>Tên chùa:<span class="required">*</span></label>
        <input type="text" id="ten_chua" name="ten_chua" required>
    </div>

    <div class="form-group">
        <label>Địa chỉ:<span class="required">*</span></label>
        <input type="text" id="dia_chi" name="dia_chi" required>
    </div>

    <div class="form-group">
        <label>Điện thoại:</label>
        <input type="tel" id="dien_thoai" name="dien_thoai">
    </div>

    <div class="form-group">
        <label>Email:</label>
        <input type="email" id="email" name="email">
    </div>

    <div class="form-group">
        <label>Hình ảnh:</label>
        <div class="image-upload-container">
            <input type="file" id="hinh_anh" name="hinh_anh" accept="image/*">
            <div id="image-preview" class="image-preview"></div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="btnSave">
            <i class="fas fa-save"></i> Lưu
        </button>
        <button type="button" id="remove-image" class="btn btn-danger" style="display: none;">
            <i class="fas fa-trash"></i> Xóa ảnh
        </button>
        <button type="button" class="btn btn-secondary close-modal">
            <i class="fas fa-times"></i> Hủy
        </button>
    </div>
</form>
        </div>
    </div>
</div>

<!-- Bảng quản lý chi tiết chùa -->
<div class="table-container">
    <div class="section-header">
        <h2>QUẢN LÝ NỘI DUNG CHI TIẾT CHÙA</h2>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Tên chùa</th>
                <th>Giới thiệu</th>
                <th>Lịch sử</th>
                <th>Kiến trúc</th>
                <th>Di tích</th>
                <th>Lễ hội</th>
                <th>Video</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php 
            $sql = "SELECT c.ten_chua, ct.* 
                    FROM dschua c 
                    LEFT JOIN chitiet_chua ct ON c.id = ct.chua_id 
                    ORDER BY c.id DESC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $hasDetails = !empty($row['gioi_thieu']) || !empty($row['lich_su']) || 
                                !empty($row['kien_truc']) || !empty($row['di_tich']) || 
                                !empty($row['le_hoi']) || !empty($row['video_gioi_thieu']);
                    ?>
                    <tr data-hinh-anh-gioi-thieu="<?php echo htmlspecialchars($row['hinh_anh_gioi_thieu'] ?? ''); ?>"
                        data-hinh-anh-lich-su="<?php echo htmlspecialchars($row['hinh_anh_lich_su'] ?? ''); ?>"
                        data-hinh-anh-kien-truc="<?php echo htmlspecialchars($row['hinh_anh_kien_truc'] ?? ''); ?>"
                        data-hinh-anh-di-tich="<?php echo htmlspecialchars($row['hinh_anh_di_tich'] ?? ''); ?>">
                        <td><?php echo htmlspecialchars($row['ten_chua']); ?></td>
                        <td class="content-text">
                            <?php echo !empty($row['gioi_thieu']) ? htmlspecialchars($row['gioi_thieu']) : ''; ?>
                        </td>
                        <td class="content-text">
                            <?php echo !empty($row['lich_su']) ? htmlspecialchars($row['lich_su']) : ''; ?>
                        </td>
                        <td class="content-text">
                            <?php echo !empty($row['kien_truc']) ? htmlspecialchars($row['kien_truc']) : ''; ?>
                        </td>
                        <td class="content-text">
                            <?php echo !empty($row['di_tich']) ? htmlspecialchars($row['di_tich']) : ''; ?>
                        </td>
                        <td class="content-text">
                            <?php echo !empty($row['le_hoi']) ? htmlspecialchars($row['le_hoi']) : ''; ?>
                        </td>
                        <td>
                            <?php echo !empty($row['video_gioi_thieu']) ? htmlspecialchars($row['video_gioi_thieu']) : ''; ?>
                        </td>
                        <td>
                            <?php if ($hasDetails): ?>
                                <button class="btn btn-warning sua-chi-tiet-chua" 
                                        data-ten-chua="<?php echo htmlspecialchars($row['ten_chua']); ?>">
                                    <i class="fas fa-edit"></i> Sửa chi tiết
                                </button>
                            <?php else: ?>
                                <button type="button" 
                                        class="btn btn-primary btn-add-details" 
                                        data-ten-chua="<?php echo htmlspecialchars($row['ten_chua']); ?>">
                                    <i class="fas fa-plus"></i> Thêm chi tiết
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'></td></tr>";
            }
        ?>
        </tbody>
    </table>
</div>

<!-- Modal form chi tiết chùa -->
<div id="chiTietChuaModal" class="modal">
    <div class="modal-content">
        <div class="modal-title-container">
            <h2 id="modalChiTietTitle">Thêm nội dung chi tiết chùa</h2>
        </div>
        
        <form id="formChiTietChua" method="post">
            <input type="hidden" id="ten_chua_chi_tiet" name="ten_chua" value="">
            
            <div class="form-group">
                <label for="gioi_thieu">Giới thiệu:</label>
                <textarea id="gioi_thieu" name="gioi_thieu" class="form-control" rows="4"></textarea>
                <div class="image-upload-container">
                    <label for="hinh_anh_gioi_thieu">Hình ảnh giới thiệu:</label>
                    <input type="file" id="hinh_anh_gioi_thieu" name="hinh_anh_gioi_thieu" accept="image/*" onchange="previewImage(this, 'preview-hinh-anh-gioi-thieu')">
                    <input type="hidden" id="delete_hinh_anh_gioi_thieu" name="delete_hinh_anh_gioi_thieu" value="0">
                    <div id="preview-hinh-anh-gioi-thieu" class="image-preview"></div>
                    <button type="button" class="btn btn-danger btn-remove-image" data-preview="preview-hinh-anh-gioi-thieu" data-delete="delete_hinh_anh_gioi_thieu">
                        <i class="fas fa-trash"></i> Xóa ảnh
                    </button>
                    <button type="button" class="btn btn-primary btn-new-image" onclick="document.getElementById('hinh_anh_gioi_thieu').click()">
                        <i class="fas fa-plus"></i> Thêm ảnh mới
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="lich_su">Lịch sử:</label>
                <textarea id="lich_su" name="lich_su" class="form-control" rows="4"></textarea>
                <div class="image-upload-container">
                    <label for="hinh_anh_lich_su">Hình ảnh lịch sử:</label>
                    <input type="file" id="hinh_anh_lich_su" name="hinh_anh_lich_su" accept="image/*" onchange="previewImage(this, 'preview-hinh-anh-lich-su')">
                    <input type="hidden" id="delete_hinh_anh_lich_su" name="delete_hinh_anh_lich_su" value="0">
                    <div id="preview-hinh-anh-lich-su" class="image-preview"></div>
                    <button type="button" class="btn btn-danger btn-remove-image" data-preview="preview-hinh-anh-lich-su" data-delete="delete_hinh_anh_lich_su">
                        <i class="fas fa-trash"></i> Xóa ảnh
                    </button>
                    <button type="button" class="btn btn-primary btn-new-image" onclick="document.getElementById('hinh_anh_lich_su').click()">
                        <i class="fas fa-plus"></i> Thêm ảnh mới
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="kien_truc">Kiến trúc:</label>
                <textarea id="kien_truc" name="kien_truc" class="form-control" rows="4"></textarea>
                <div class="image-upload-container">
                    <label for="hinh_anh_kien_truc">Hình ảnh kiến trúc:</label>
                    <input type="file" id="hinh_anh_kien_truc" name="hinh_anh_kien_truc" accept="image/*" onchange="previewImage(this, 'preview-hinh-anh-kien-truc')">
                    <input type="hidden" id="delete_hinh_anh_kien_truc" name="delete_hinh_anh_kien_truc" value="0">
                    <div id="preview-hinh-anh-kien-truc" class="image-preview"></div>
                    <button type="button" class="btn btn-danger btn-remove-image" data-preview="preview-hinh-anh-kien-truc" data-delete="delete_hinh_anh_kien_truc">
                        <i class="fas fa-trash"></i> Xóa ảnh
                    </button>
                    <button type="button" class="btn btn-primary btn-new-image" onclick="document.getElementById('hinh_anh_kien_truc').click()">
                        <i class="fas fa-plus"></i> Thêm ảnh mới
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="di_tich">Di tích:</label>
                <textarea id="di_tich" name="di_tich" class="form-control" rows="4"></textarea>
                <div class="image-upload-container">
                    <label for="hinh_anh_di_tich">Hình ảnh di tích:</label>
                    <input type="file" id="hinh_anh_di_tich" name="hinh_anh_di_tich" accept="image/*" onchange="previewImage(this, 'preview-hinh-anh-di-tich')">
                    <input type="hidden" id="delete_hinh_anh_di_tich" name="delete_hinh_anh_di_tich" value="0">
                    <div id="preview-hinh-anh-di-tich" class="image-preview"></div>
                    <button type="button" class="btn btn-danger btn-remove-image" data-preview="preview-hinh-anh-di-tich" data-delete="delete_hinh_anh_di_tich">
                        <i class="fas fa-trash"></i> Xóa ảnh
                    </button>
                    <button type="button" class="btn btn-primary btn-new-image" onclick="document.getElementById('hinh_anh_di_tich').click()">
                        <i class="fas fa-plus"></i> Thêm ảnh mới
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="le_hoi">Lễ hội:</label>
                <textarea id="le_hoi" name="le_hoi" class="form-control" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="video_gioi_thieu">Link video giới thiệu:</label>
                <input type="text" id="video_gioi_thieu" name="video_gioi_thieu" class="form-control">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="btnSubmitChiTiet">
                    <i class="fas fa-save"></i> Lưu
                </button>
                <button type="button" class="btn btn-secondary close-modal">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function showExistingImage(imageUrl, previewId) {
    if (imageUrl) {
        const preview = document.getElementById(previewId);
        preview.innerHTML = `<img src="${imageUrl}" alt="Current Image" style="max-width: 200px; max-height: 200px;">`;
    }
}

// Xử lý sự kiện xóa ảnh
document.querySelectorAll('.btn-remove-image').forEach(button => {
    button.addEventListener('click', function() {
        const previewId = this.dataset.preview;
        const deleteFieldId = this.dataset.delete;
        const preview = document.getElementById(previewId);
        const fileInput = preview.previousElementSibling.previousElementSibling; // Skip hidden input
        const deleteField = document.getElementById(deleteFieldId);
        
        preview.innerHTML = '';
        fileInput.value = '';
        deleteField.value = '1'; // Đánh dấu ảnh cần xóa
    });
});
</script>

<style>
.image-upload-container {
    margin-top: 10px;
    position: relative;
}

.image-preview {
    margin-top: 10px;
    min-height: 100px;
    border: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

.image-preview img {
    max-width: 200px;
    max-height: 200px;
    object-fit: contain;
}

.btn-remove-image {
    margin-top: 10px;
    position: absolute;
    right: 10px;
    top: 10px;
    z-index: 100;
    background-color: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-remove-image:hover {
    background-color: rgba(200, 35, 51, 1);
}

.btn-remove-image i {
    margin-right: 5px;
}

.btn-new-image {
    margin-top: 10px;
    margin-left: 10px;
    padding: 5px 10px;
}

.btn-new-image i {
    margin-right: 5px;
}
</style>



<!-- Form quản lý sự kiện -->
<div class="table-container">
    <div class="section-header">
        <h2>QUẢN LÝ LỄ HỘI</h2>
        <button type="button" id="btnThemSuKien" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm Lễ Hội
        </button>
    </div>
<!-- Modal form sự kiện -->
<div id="eventFormContainer" class="modal">
    <div class="modal-content">
        <div class="modal-title-container">
            <h2 class="form-title">Thêm lễ hội</h2> 
        </div>
        
        <div class="form-container">
            <form id="eventForm" enctype="multipart/form-data">
                <input type="hidden" id="event_id" name="event_id">
                
                <!-- Các trường form hiện tại -->
                <div class="form-group">
                    <label for="ten_su_kien">Tên lễ hội<span class="required">*</span></label>
                    <input type="text" id="ten_su_kien" name="ten_su_kien" required>
                </div>
                
                <!-- Các trường form khác -->
                <div class="form-group">
                    <label for="y_nghia">Ý nghĩa<span class="required">*</span></label>
                    <textarea id="y_nghia" name="y_nghia" required></textarea>
                </div>

                <div class="form-group">
                    <label for="thoi_gian_to_chuc">Thời gian tổ chức<span class="required">*</span></label>
                    <input type="text" id="thoi_gian_to_chuc" name="thoi_gian_to_chuc" required>
                </div>
                
                <div class="form-group">
                    <label for="cac_nghi_thuc">Các nghi thức<span class="required">*</span></label>
                    <textarea id="cac_nghi_thuc" name="cac_nghi_thuc" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="am_thuc_truyen_thong">Ẩm thực truyền thống<span class="required">*</span></label>
                    <textarea id="am_thuc_truyen_thong" name="am_thuc_truyen_thong" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="luu_y_khach_tham_quan">Lưu ý<span class="required">*</span></label>
                    <textarea id="luu_y_khach_tham_quan" name="luu_y_khach_tham_quan" required></textarea>
                </div>

                <!-- Thêm trường upload hình ảnh -->
                <div class="form-group">
                    <label>Hình ảnh:</label>
                    <div class="image-upload-container">
                        <input type="file" id="hinh_anh_su_kien" name="hinh_anh" accept="image/*">
                        <div id="image-preview-su-kien" class="image-preview"></div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="btnSaveEvent">
                        <i class="fas fa-save"></i> Lưu lễ hội
                    </button>
                    <button type="button" id="remove-image-su-kien" class="btn btn-danger" style="display: none;">
                        <i class="fas fa-trash"></i> Xóa ảnh
                    </button>
                    <button type="button" id="btnHuySuKien" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Bảng hiển thị lễ hội -->
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Tên lễ hội</th>
                <th>Ý nghĩa</th>
                <th>Thời gian tổ chức</th>
                <th>Các nghi thức</th>
                <th>Ẩm thực truyền thống</th>
                <th>Lưu ý</th>
                <th>Hình ảnh</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($su_kien_list as $su_kien): ?>
        <tr>
            <td><?php echo htmlspecialchars($su_kien['ten_su_kien']); ?></td>
            <td class="content-text"><?php echo htmlspecialchars($su_kien['y_nghia']); ?></td>
                <td><?php echo htmlspecialchars($su_kien['thoi_gian_to_chuc']); ?></td>
                <td class="content-text"><?php echo htmlspecialchars($su_kien['cac_nghi_thuc']); ?></td>
                <td class="content-text"><?php echo htmlspecialchars($su_kien['am_thuc_truyen_thong']); ?></td>
                <td class="content-text"><?php echo htmlspecialchars($su_kien['luu_y_khach_tham_quan']); ?></td>
        </td>
                <td>
                <?php if($su_kien['hinh_anh']): ?>
                    <img src="<?php echo htmlspecialchars($su_kien['hinh_anh']); ?>" alt="Hình ảnh sự kiện" class="event-image">
                <?php endif; ?>
                </td>
                <td>
                    <select class="trang-thai-su-kien" data-id="<?php echo $su_kien['id']; ?>">
                        <option value="1" <?php echo ($su_kien['trang_thai'] ?? 1) == 1 ? 'selected' : ''; ?>>Hiển thị</option>
                        <option value="0" <?php echo ($su_kien['trang_thai'] ?? 1) == 0 ? 'selected' : ''; ?>>Ẩn</option>
                    </select>
                </td>
                </td>
                <td>
                    <button class="btn btn-warning sua-su-kien" data-id="<?php echo $su_kien['id']; ?>">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="btn btn-danger xoa-su-kien" data-id="<?php echo $su_kien['id']; ?>">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<div class="table-container">
    <div class="section-header">
        <h2>QUẢN LÝ BÌNH LUẬN</h2>
        <div class="search-container">
            <input type="text" id="timKiemBinhLuan" class="search-input" placeholder="Tìm kiếm bình luận">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Người bình luận</th>
                <th>Email</th>
                <th>Nội dung bình luận</th>
                <th>Phản hồi</th>
                <th>Ngày bình luận</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($danhSachBinhLuan as $binhLuan): ?>
            <tr>
                <td>
                    <?php echo htmlspecialchars($binhLuan['ten_nguoi_dung'] ?? $binhLuan['ten_dang_nhap'] ?? 'Ẩn danh'); ?>
                </td>
                <td><?php echo htmlspecialchars($binhLuan['email_nguoi_dung'] ?? ''); ?></td>
                <td>
                    <div class="comment-content">
                        <?php echo nl2br(htmlspecialchars($binhLuan['noi_dung'])); ?>
                    </div>
                </td>
               <td>
                <?php if (!empty($binhLuan['phan_hoi'])): ?>
                    <div class="comment-text">
                        <?php echo nl2br(htmlspecialchars($binhLuan['phan_hoi'])); ?>
                    </div>
                <?php else: ?>
                    <em class="no-reply">Chưa có phản hồi</em>
                <?php endif; ?>
            </td>
            <td>
                <?php echo date('d/m/Y', strtotime($binhLuan['ngay_tao'])); ?>
            </td>
                <td>
                    <select class="trang-thai-binh-luan" data-id="<?php echo $binhLuan['id']; ?>">
                        <option value="1" <?php echo ($binhLuan['trang_thai'] ?? 0) == 1 ? 'selected' : ''; ?>>Hiển thị</option>
                        <option value="0" <?php echo ($binhLuan['trang_thai'] ?? 0) == 0 ? 'selected' : ''; ?>>Ẩn</option>
                    </select>
                </td>
                <td>
                    <button class="btn btn-primary btn-save-reply" data-id="<?php echo $binhLuan['id']; ?>">
                        <i class="fas fa-reply"></i> Phản hồi
                    </button>
                    <button class="btn btn-danger xoa-binh-luan" data-id="<?php echo $binhLuan['id']; ?>">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal form phản hồi bình luận -->
<div id="replyFormContainer" class="modal">
    <div class="modal-content">
        <div class="modal-title-container">
            <h2 id="replyModalTitle">Phản hồi bình luận</h2>
        </div>
        
        <div class="form-container">
            <form id="replyForm">
                <input type="hidden" id="comment_id" name="comment_id">
                
                <div class="form-group">
                    <label>Nội dung bnh luận:</label>
                    <div id="originalComment" class="comment-display"></div>
                </div>
                
                <div class="form-group">
                    <label for="reply_content">Nội dung phản hồi:<span class="required">*</span></label>
                    <textarea id="reply_content" name="reply_content" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-reply"></i> Gửi phản hồi
                    </button>
                    <button type="button" class="btn btn-secondary close-modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>

// Khai báo các biến toàn cục
const modal = $("#modalChua");
const form = $("#formChua");
const fileInput = $("#hinh_anh");
const imagePreview = $("#image-preview");
const removeImageBtn = $("#remove-image");
let isProcessing = false;

$(document).ready(function() {
    // Cấu hình thông báo toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right", 
        "timeOut": "3000",
        "extendedTimeOut": "1000"
    };

    // Khởi tạo các chức năng quản lý
    khoiTaoQuanLyChua();
    khoiTaoQuanLySuKien();
    khoiTaoQuanLyNguoiDung();
    khoiTaoQuanLyBinhLuan();
    khoiTaoQuanLyChiTietChua();
});

// QUẢN LÝ CHÙA
function khoiTaoQuanLyChua() {
    // Xử lý preview ảnh khi chọn file
    $("#hinh_anh").change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#image-preview").html(`<img src="${e.target.result}" alt="Xem trước" style="max-width: 100%; height: 100%;">`);
                $("#remove-image").show();
            };
            reader.readAsDataURL(file);
        } else {
            xoaFormAnh();
        }
    });

    // Xử lý thêm mới chùa
    $(document).on('click', '.btn-add', function() {
        const sectionTitle = $(this).closest('.section-header').find('h2').text();
        if (!sectionTitle.includes('sự kiện')) {
            $("#modalTitle").text("Thêm thông tin chùa");
            xoaFormChua();
            $("#modalChua").show();
        }
    });

// Cập nhật cấu hình toastr và xử lý form submit
$(document).ready(function() {
    // Cấu hình toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
    };
});

// Cập nhật xử lý form submit
$('#formChua').on('submit', function(e) {
    e.preventDefault();
    
    // Kiểm tra dữ liệu trước khi submit
    var tenChua = $('#ten_chua').val().trim();
    var diaChi = $('#dia_chi').val().trim();
    
    // Kiểm tra nếu các trường bắt buộc không được điền
    if (!tenChua || !diaChi) {
        toastr.warning('Vui lòng điền đầy đủ thông tin bắt buộc');
        return;
    }
    
    var formData = new FormData(this);
    var isEdit = $('#chua_id').val() !== '';
    
    // Thêm action vào formData
    formData.append(isEdit ? 'sua_chua' : 'them_chua', '1');
    
    // Disable nút submit để tránh submit nhiều lần
    var submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true);
    
    $.ajax({
    url: 'QTVindex.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
        try {
            var result = JSON.parse(response);
            if (result.success) {
                    // Hiển thị thông báo thành công
                    toastr.success(
                        isEdit ? 'Thao tác sửa thành công!' : 'Thêm chùa mới thành công!',
                    );
                    
                    // Đóng modal
                    $('#modalChua').hide();
                    
                    // Tải lại trang sau 1.5 giây
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                toastr.error(result.message || 'Có lỗi xảy ra, vui lòng thử lại');
            }
        } catch (e) {
            console.error('Lỗi:', e);
            toastr.error('Có lỗi xảy ra khi xử lý phản hồi từ server');
        }
    },
    error: function(xhr, status, error) {
        console.error('Lỗi Ajax:', error);
        toastr.error('Có lỗi xảy ra khi gửi yêu cầu: ' + error);
    }
  });
});


// Xử lý sửa chùa
$(document).on('click', '.sua-chua', function() {
    const row = $(this).closest('tr');
    const chuaId = $(this).data('id');
    
    // Reset form
    $("#formChua")[0].reset();
    $("#chua_id").val(chuaId);
    $("#modalTitle").text("Sửa thông tin chùa");
    
    // Lấy dữ liệu từ các ô trong hàng
    $("#ten_chua").val(row.find('td:eq(0)').text().trim());
    $("#dia_chi").val(row.find('td:eq(1)').text().trim());
    $("#dien_thoai").val(row.find('td:eq(2)').text().trim());
    $("#email").val(row.find('td:eq(3)').text().trim());
    $("#trang_thai").val(row.find('.trang-thai-chua').val());

    // Xử lý hiển thị ảnh
    const currentImage = row.find('td:eq(4) img').attr('src');
    if (currentImage) {
        $("#image-preview").html(`<img src="${currentImage}" alt="Xem trước" style="max-width: 100%; height: 100%;">`);
        $("#remove-image").show();
    } else {
        $("#image-preview").empty();
        $("#remove-image").hide();
    }
    $("#modalChua").show();
});

// Xóa ảnh
$("#remove-image").click(function() {
    $("#hinh_anh").val('');
    $("#image-preview").empty();
    $(this).hide();
});

    // Xử lý xóa chùa
$(document).on('click', '.xoa-chua', function(e) {
    e.preventDefault();
    if (isProcessing) return;
    
    const chuaId = $(this).data('id');
    const row = $(this).closest('tr');
    const tenChua = row.find('td:eq(0)').text().trim(); // Lấy tên chùa để xóa chi tiết tương ứng
    
    if (confirm('Bạn có chắc chắn muốn xóa chùa này? Tất cả thông tin chi tiết của chùa cũng sẽ bị xóa.')) {
        isProcessing = true;
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                'xoa_chua': '1',
                'id': chuaId,
                'ten_chua': tenChua // Thêm tên chùa vào dữ liệu gửi đi
            },
            success: function(response) {
                try {
                    const ketQua = typeof response === 'string' ? JSON.parse(response) : response;
                    if (ketQua.success) {
                        // Xóa hàng chùa
                        row.fadeOut(400, function() {
                            $(this).remove();
                        });
                        
                        // Xóa hàng chi tiết chùa tương ứng
                        const chiTietRow = $(`.data-table tr:contains("${tenChua}")`);
                        if (chiTietRow.length > 0) {
                            chiTietRow.fadeOut(400, function() {
                                $(this).remove();
                            });
                        }
                        
                        toastr.success('Xóa chùa và nội dung chi tiết chùa thành công');
                    } else {
                        toastr.error(ketQua.message || 'Có lỗi xảy ra');
                    }
                } catch (e) {
                    console.error('Lỗi:', e);
                    toastr.error('Có lỗi xảy ra khi xử lý phản hồi');
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi Ajax:', error);
                toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
            },
            complete: function() {
                isProcessing = false;
            }
        });
    }
});

    // Xử lý xóa ảnh
    $("#remove-image").click(function(e) {
        e.preventDefault();
        if (isProcessing) return;
        
        const chuaId = $("#chua_id").val();
        
        if (chuaId) {
            if (!confirm('Bạn có chắc chắn muốn xóa ảnh này?')) return;
            
            isProcessing = true;
            
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    'xoa_anh_chua': '1',
                    'id': chuaId
                },
                success: function(response) {
                    try {
                        const ketQua = typeof response === 'string' ? JSON.parse(response) : response;
                        if (ketQua.success) {
                            xoaFormAnh();
                            $(`.sua-chua[data-id="${chuaId}"]`).closest('tr').find('td:eq(6)').empty();
                            toastr.success('Xóa ảnh thành công');
                        } else {
                            toastr.error(ketQua.message || 'Có lỗi xảy ra');
                        }
                    } catch (e) {
                        console.error('Lỗi:', e);
                        toastr.error('Có lỗi xảy ra khi xử lý phản hồi');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Lỗi Ajax:', error);
                    toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
                },
                complete: function() {
                    isProcessing = false;
                }
            });
        } else {
            xoaFormAnh();
        }
    });

    // Xử lý thay đổi trạng thái chùa
    $(document).on('change', '.trang-thai-chua', function() {
        if (isProcessing) return;
        
        const chuaId = $(this).data('id');
        const trangThaiMoi = $(this).val();
        
        isProcessing = true;
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                'thay_doi_trang_thai_chua': '1',
                'chua_id': chuaId,
                'trang_thai': trangThaiMoi
            },
            success: function(response) {
                try {
                    const ketQua = typeof response === 'string' ? JSON.parse(response) : response;
                    if (ketQua.success) {
                        toastr.success('Cập nhật trạng thái thành công');
                    } else {
                        toastr.error(ketQua.message || 'Có lỗi xảy ra');
                    }
                } catch (e) {
                    console.error('Lỗi:', e);
                    toastr.error('Có lỗi xảy ra khi xử lý phản hồi');
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi Ajax:', error);
                toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
            },
            complete: function() {
                isProcessing = false;
            }
        });
    });
}

// QUẢN LÝ SỰ KIỆN
function khoiTaoQuanLySuKien() {
    // Xử lý nút thêm mới sự kiện
    $('#btnThemSuKien').click(function() {
        // Reset form và cập nhật giao diện cho thêm mới
        $('#eventForm')[0].reset();
        $('#event_id').val('');
        $('#image-preview-su-kien').empty();
        $('#remove-image-su-kien').hide();
        
        // Cập nhật tiêu đề modal
        $('.form-title').text('Thêm Lễ Hội Mới');
        
        // Xóa dữ liệu cũ trong các trường
        $('#ten_su_kien').val('');
        $('#y_nghia').val('');
        $('#thoi_gian_to_chuc').val('');
        $('#cac_nghi_thuc').val('');
        $('#am_thuc_truyen_thong').val('');
        $('#luu_y_khach_tham_quan').val('');
        
        // Hiển thị modal
        $('#eventFormContainer').show();
    });

    // Xử lý khi chọn ảnh mới
    $("#hinh_anh_su_kien").change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#image-preview-su-kien").html(`<img src="${e.target.result}" alt="Xem trước">`);
                $("#remove-image-su-kien").show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Xử lý nút sửa sự kiện
    $(document).on('click', '.sua-su-kien', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const suKienId = $(this).data('id');
        
        // Reset form trước khi điền dữ liệu mới
        $('#eventForm')[0].reset();
        $('#image-preview-su-kien').empty();
        $('#remove-image-su-kien').hide();
        
        // Lấy dữ liệu từ server
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                'lay_thong_tin_su_kien': true,
                'event_id': suKienId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const suKien = response.data;
                    
                    // Điền dữ liệu vào form
                    $('#event_id').val(suKien.id);
                    $('#ten_su_kien').val(suKien.ten_su_kien);
                    $('#y_nghia').val(suKien.y_nghia);
                    $('#thoi_gian_to_chuc').val(suKien.thoi_gian_to_chuc);
                    $('#cac_nghi_thuc').val(suKien.cac_nghi_thuc);
                    $('#am_thuc_truyen_thong').val(suKien.am_thuc_truyen_thong);
                    $('#luu_y_khach_tham_quan').val(suKien.luu_y_khach_tham_quan);
                    
                    // Xử lý hiển thị ảnh
                    if (suKien.hinh_anh) {
                        $('#image-preview-su-kien').html(`<img src="${suKien.hinh_anh}" alt="Xem trước">`);
                        $('#remove-image-su-kien').show();
                    }
                    
                    // Cập nhật tiêu đề modal
                    $('.form-title').text('Sửa Lễ Hội');
                    
                    // Hiển thị modal
                    $('#eventFormContainer').show();
                } else {
                    toastr.error(response.message || 'Có lỗi xảy ra khi lấy thông tin sự kiện');
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi Ajax:', error);
                toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
            }
        });
    });

    // Xử lý nút hủy trong form sự kiện
    $('#btnHuySuKien').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#eventFormContainer').hide();
        $('#eventForm')[0].reset();
        $('#event_id').val('');
        $('#image-preview-su-kien').empty();
        $('#remove-image-su-kien').hide();
    });

    // Xử lý nút đóng (X) trong form sự kiện
    $('#eventFormContainer .close').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#eventFormContainer').hide();
        $('#eventForm')[0].reset();
        return false;
    });

    // Xử lý xóa sự kiện
    $(document).on('click', '.xoa-su-kien', function(e) {
        e.preventDefault();
        if (isProcessing) return;
        
        const suKienId = $(this).data('id');
        const row = $(this).closest('tr');
        
        if (confirm('Bạn có chắc chắn muốn xóa lễ hội này?')) {
            isProcessing = true;
            
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    'xoa_su_kien': '1',
                    'id': suKienId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(400, function() {
                            $(this).remove();
                        });
                        toastr.success(response.message || 'Xóa lễ hội thành công');
                    } else {
                        toastr.error(response.message || 'Có lỗi xảy ra khi xóa lễ hội');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Lỗi Ajax:', error);
                    toastr.error('Có lỗi xảy ra khi gửi yêu cầu xóa');
                },
                complete: function() {
                    isProcessing = false;
                }
            });
        }
    });

    // Xử lý thay đổi trạng thái sự kiện
    $(document).on('change', '.trang-thai-su-kien', function() {
        const select = $(this);
        const suKienId = select.data('id');
        const trangThaiMoi = select.val();
        
        // Disable select trong khi đang xử lý
        select.prop('disabled', true);
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                'thay_doi_trang_thai_su_kien': true,
                'event_id': suKienId,
                'trang_thai': trangThaiMoi
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success('Cập nhật trạng thái thành công');
                } else {
                    // Nếu thất bại, reset về giá trị cũ
                    select.val(trangThaiMoi === '1' ? '0' : '1');
                    toastr.error(response.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
                }
            },
            error: function(xhr, status, error) {
                // Nếu có lỗi, reset về giá trị cũ
                select.val(trangThaiMoi === '1' ? '0' : '1');
                console.error('Lỗi Ajax:', error);
                toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
            },
            complete: function() {
                // Enable lại select sau khi xử lý xong
                select.prop('disabled', false);
            }
        });
    });

    // Xử lý xóa ảnh
    $("#remove-image-su-kien").click(function() {
        $("#hinh_anh_su_kien").val('');
        $("#image-preview-su-kien").empty();
        $(this).hide();
    });

    // Xử lý submit form
    $('#eventForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const suKienId = $('#event_id').val();
        
        formData.append(suKienId ? 'sua_su_kien' : 'them_su_kien', true);
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.success) {
                        toastr.success(result.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(result.message || 'Có lỗi xảy ra');
                    }
                } catch (e) {
                    console.error('Lỗi:', e);
                    toastr.error('Có lỗi xảy ra khi xử lý phản hồi');
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi Ajax:', error);
                toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
            }
        });
    });
}


// QUẢN LÝ NGƯỜI DÙNG
function khoiTaoQuanLyNguoiDung() {
    // Xử lý tìm kiếm người dùng
    $('#timKiemNguoiDung').on('input', function() {
        const tuKhoa = $(this).val().toLowerCase();
        const bangNguoiDung = $(this).closest('.table-container').find('.data-table tbody tr');
        
        bangNguoiDung.each(function() {
            const tenDangNhap = $(this).find('td:eq(0)').text().toLowerCase();
            const hoTen = $(this).find('td:eq(1)').text().toLowerCase();
            const email = $(this).find('td:eq(2)').text().toLowerCase();
            
            if (tenDangNhap.includes(tuKhoa) || 
                hoTen.includes(tuKhoa) || 
                email.includes(tuKhoa)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

 // Xử lý xóa người dùng
$(document).on('click', '.xoa-nguoi-dung', function(e) {
    e.preventDefault();
    if (isProcessing) return;
    
    const nguoiDungId = $(this).data('id');
    const row = $(this).closest('tr');
    
    if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
        isProcessing = true;
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                'xoa_nguoi_dung': '1',
                'user_id': nguoiDungId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    row.fadeOut(400, function() {
                        $(this).remove();
                    });
                    toastr.success(response.message || 'Xóa người dùng thành công');
                } else {
                    toastr.error(response.message || 'Có lỗi xảy ra khi xóa người dùng');
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi Ajax:', error);
                toastr.error('Có lỗi xảy ra khi gửi yêu cầu xóa');
            },
            complete: function() {
                isProcessing = false;
            }
        });
    }
});
}

// QUẢN LÝ BÌNH LUẬN
function khoiTaoQuanLyBinhLuan() {
    // Xử lý tìm kiếm bình luận
    $('#timKiemBinhLuan').on('input', function() {
        const tuKhoa = $(this).val().toLowerCase();
        const bangBinhLuan = $(this).closest('.table-container').find('.data-table tbody tr');
        
        bangBinhLuan.each(function() {
            const nguoiDung = $(this).find('td:eq(0)').text().toLowerCase();
            const noiDung = $(this).find('td:eq(2)').text().toLowerCase();
            
            if (nguoiDung.includes(tuKhoa) || noiDung.includes(tuKhoa)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Xử lý thay đổi trạng thái bình luận
    $(document).on('change', '.trang-thai-binh-luan', function() {
        const select = $(this);
        const binhLuanId = select.data('id');
        const trangThaiMoi = select.val();
        
        // Disable select trong khi đang xử lý
        select.prop('disabled', true);
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                'action': 'cap_nhat_trang_thai_binh_luan',
                'comment_id': binhLuanId,
                'trang_thai': trangThaiMoi
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success('Cập nhật trạng thái thành công');
                } else {
                    // Nếu thất bại, reset về giá trị cũ
                    select.val(trangThaiMoi === '1' ? '0' : '1');
                    toastr.error(response.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
                }
            },
            error: function(xhr, status, error) {
                // Nếu có lỗi, reset về giá trị cũ
                select.val(trangThaiMoi === '1' ? '0' : '1');
                console.error('Lỗi Ajax:', error);
                toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
            },
            complete: function() {
                // Enable lại select sau khi xử lý xong
                select.prop('disabled', false);
            }
        });
    });

// Xử lý hiển thị form phản hồi
$(document).on('click', '.btn-save-reply', function(e) {
    e.preventDefault();
    const row = $(this).closest('tr');
    const binhLuanId = $(this).data('id');
    const noiDungBinhLuan = row.find('.comment-content').text().trim();
    const phanHoiHienTai = row.find('.current-reply').text().trim();
    
    $('#comment_id').val(binhLuanId);
    $('#originalComment').text(noiDungBinhLuan);
    $('#reply_content').val(phanHoiHienTai);
    
    $('#replyFormContainer').show();
});

// Cập nhật phần xử lý submit form phản hồi
$('#replyForm').submit(function(e) {
    e.preventDefault();
    const binhLuanId = $('#comment_id').val();
    const phanHoiText = $('#reply_content').val().trim();
    
    if (!phanHoiText) {
        toastr.warning('Vui lòng nhập nội dung phản hồi');
        return;
    }
    
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            'action': 'phan_hoi_binh_luan',
            'comment_id': binhLuanId,
            'phan_hoi': phanHoiText
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success('Gửi phản hồi thành công');
                
                // Cập nhật trực tiếp nội dung phản hồi trong bảng
                const row = $(`.btn-save-reply[data-id="${binhLuanId}"]`).closest('tr');
                const tdPhanHoi = row.find('td:nth-child(4)'); // Cột phản hồi
                
                // Cập nhật nội dung phản hồi
                tdPhanHoi.html(`
                    <div class="comment-text">
                        ${phanHoiText.replace(/\n/g, '<br>')}
                    </div>
                `);
                
                // Đóng modal và xóa nội dung form
                $('#replyFormContainer').hide();
                $('#reply_content').val('');
                
                // Tải lại trang sau 1 giây
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                toastr.error(response.message || 'Có lỗi xảy ra khi lưu phản hồi');
            }
        },
        error: function(xhr, status, error) {
            console.error('Lỗi Ajax:', error);
            toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
        }
    });
});

// Xử lý đóng modal
$(document).on('click', '.close-modal', function() {
    $(this).closest('.modal').hide();
});

    // Xử lý xóa bình luận
    $(document).on('click', '.xoa-binh-luan', function(e) {
        e.preventDefault();
        
        const binhLuanId = $(this).data('id');
        const row = $(this).closest('tr');
        
        if (confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    'xoa_binh_luan': '1',
                    'comment_id': binhLuanId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(400, function() {
                            $(this).remove();
                        });
                        toastr.success('Xóa bình luận thành công');
                    } else {
                        toastr.error(response.message || 'Có lỗi xảy ra khi xóa bình luận');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Lỗi Ajax:', error);
                    toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
                }
            });
        }
    });
}

function khoiTaoQuanLyChiTietChua() {
// Xử lý nút thêm và sửa chi tiết
$(document).on('click', '.btn-add-details, .sua-chi-tiet-chua', function() {
    const tenChua = $(this).data('ten-chua');
    const isSua = $(this).hasClass('sua-chi-tiet-chua');
    
    console.log('Tên chùa:', tenChua, 'Là sửa:', isSua);
    
    if (!tenChua) {
        toastr.error('Không tìm thấy tên chùa');
        return;
    }
    
    // Reset form
    const form = $('#formChiTietChua')[0];
    form.reset();
    
    // Set tên chùa vào form
    $('#ten_chua_chi_tiet').val(tenChua);
    
    if (isSua) {
        // Lấy dữ liệu hiện có từ các cell trong row
        const row = $(this).closest('tr');
        $('#gioi_thieu').val(row.find('td:eq(1)').text().trim());
        $('#lich_su').val(row.find('td:eq(2)').text().trim());
        $('#kien_truc').val(row.find('td:eq(3)').text().trim());
        $('#di_tich').val(row.find('td:eq(4)').text().trim());
        $('#le_hoi').val(row.find('td:eq(5)').text().trim());
        $('#video_gioi_thieu').val(row.find('td:eq(6)').text().trim());

        // Hiển thị ảnh hiện tại nếu có
        const showImage = (imageUrl, previewId) => {
            if (imageUrl) {
                $(`#${previewId}`).html(`<img src="${imageUrl}" style="max-width: 200px;">`);
            }
        };

        showImage(row.data('hinh-anh-gioi-thieu'), 'preview-hinh-anh-gioi-thieu');
        showImage(row.data('hinh-anh-lich-su'), 'preview-hinh-anh-lich-su'); 
        showImage(row.data('hinh-anh-kien-truc'), 'preview-hinh-anh-kien-truc');
        showImage(row.data('hinh-anh-di-tich'), 'preview-hinh-anh-di-tich');
        
        $('#modalChiTietTitle').text('Sửa nội dung chi tiết chùa: ' + tenChua);
    } else {
        $('#modalChiTietTitle').text('Thêm nội dung chi tiết chùa: ' + tenChua);
    }
    
    $('#chiTietChuaModal').show();
});

// Xử lý preview ảnh khi chọn file
$('input[type="file"]').change(function() {
    const input = this;
    const previewId = 'preview-' + $(this).attr('id');
    const preview = $(`#${previewId}`);

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.html(`<img src="${e.target.result}" style="max-width: 200px;">`);
        }
        
        reader.readAsDataURL(input.files[0]);
    }
});

// Xử lý submit form chi tiết chùa
$('#formChiTietChua').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const tenChua = $('#ten_chua_chi_tiet').val();
        if (!tenChua) {
            toastr.error('Không tìm thấy tên chùa');
            return false;
        }
        
        const formData = new FormData(this);
        formData.append('action', 'them_chi_tiet_chua');
        
        // Thêm các file ảnh vào FormData
        const imageFields = ['hinh_anh_gioi_thieu', 'hinh_anh_lich_su', 'hinh_anh_kien_truc', 'hinh_anh_di_tich'];
        imageFields.forEach(field => {
            const fileInput = $(`#${field}`)[0];
            if (fileInput.files[0]) {
                formData.append(field, fileInput.files[0]);
            }
        });
        
        // Disable form để tránh submit nhiều lần
        const form = $(this);
        form.find('input, textarea, button').prop('disabled', true);
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.success) {
                        // Chỉ hiển thị một thông báo duy nhất
                        const isEdit = $('#modalChiTietTitle').text().includes('Sửa');
                        const message = isEdit 
                            ? 'Cập nhật thông tin chi tiết thành công!' 
                            : 'Thêm thông tin chi tiết mới thành công!';
                        
                        toastr.success(message);
                        
                        // Đóng modal
                        $('#chiTietChuaModal').hide();
                        
                        // Tải lại trang sau 1 giây
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(result.message || 'Có lỗi xảy ra');
                    }
                } catch (e) {
                    console.error('Lỗi:', e);
                    toastr.error('Có lỗi xảy ra khi xử lý phản hồi');
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi Ajax:', error);
                toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
            },
            complete: function() {
                // Enable lại form
                form.find('input, textarea, button').prop('disabled', false);
            }
        });
        
        return false;
    });

// Xử lý đóng modal
$('.close-modal').click(function() {
    $('#chiTietChuaModal').hide();
});
    
    // Thêm event listener để debug
    $('#btnSubmitChiTiet').click(function() {
        console.log('Submit button clicked');
    });
}

// CÁC HÀM TIỆN ÍCH
function guiAjax({url, method, data, thanhCong, thatBai}) {
    $.ajax({
        url: url,
        type: method,
        data: data,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const ketQua = typeof response === 'string' ? JSON.parse(response) : response;
                if (ketQua.success) {
                    if (thanhCong) thanhCong(ketQua);
                } else {
                    if (thatBai) thatBai(ketQua);
                    toastr.error(ketQua.message || 'Có lỗi xảy ra');
                }
            } catch (e) {
                console.error('Lỗi:', e);
                toastr.error('Có lỗi xảy ra khi xử lý phản hồi');
            }
        },
        error: function(xhr, status, error) {
            console.error('Lỗi Ajax:', error);
            toastr.error('Có lỗi xảy ra khi gửi yêu cầu');
        }
    });
}

function xoaFormChua() {
    $("#formChua")[0].reset();
    $("#chua_id").val("");
    xoaFormAnh();
    $("#trang_thai").val("1");
    $(".error-message").remove();
}

function xoaFormSuKien() {
    $('#eventForm')[0].reset();
    $('#event_id').val('');
}

function xoaFormAnh() {
    $("#hinh_anh").val('');
    $("#image-preview").empty();
    $("#remove-image").hide();
}

</script>
<?php
$conn->close();
?>
</body>
</html>
