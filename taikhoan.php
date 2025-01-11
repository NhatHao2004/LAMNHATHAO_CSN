<?php
session_start();

function checkAccountExists($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch() !== false;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap.php');
    exit;
}

// Kết nối database
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
} catch(PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Lấy thông tin người dùng
try {
    $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: dangnhap.php');
        exit;
    }
} catch(PDOException $e) {
    $error_message = "Lỗi lấy thông tin người dùng";
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        try {
            $ho_ten = trim($_POST['ho_ten']);
            $email = trim($_POST['email']);
            
            if (empty($ho_ten)) throw new Exception("Họ tên không được để trống");
            if (empty($email)) throw new Exception("Email không được để trống");
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Email không hợp lệ");
            
            $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                throw new Exception("Email đã được sử dụng");
            }
            
            $stmt = $conn->prepare("UPDATE nguoi_dung SET ho_ten = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$ho_ten, $email, $user_id]);
            
            if ($result) {
                $_SESSION['user_name'] = $ho_ten;
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
                    exit;
                }
            } else {
                throw new Exception("Không thể cập nhật thông tin");
            }
            
        } catch (Exception $e) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }
    }
    
    else if ($_POST['action'] === 'change_password') {
        try {
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if (empty($old_password)) throw new Exception("Vui lòng nhập mật khẩu cũ");
            if (empty($new_password)) throw new Exception("Vui lòng nhập mật khẩu mới");
            if ($new_password !== $confirm_password) throw new Exception("Mật khẩu mới không khớp");
            if (strlen($new_password) < 6) throw new Exception("Mật khẩu phải có ít nhất 6 ký tự");
            
            $stmt = $conn->prepare("SELECT mat_khau FROM nguoi_dung WHERE id = ?");
            $stmt->execute([$user_id]);
            $current_password = $stmt->fetchColumn();
            
            if (!password_verify($old_password, $current_password)) {
                throw new Exception("Mật khẩu cũ không đúng");
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?");
            $result = $stmt->execute([$hashed_password, $user_id]);
            
            if ($result) {
                if (isset($_POST['ajax'])) {
                    echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
                    exit;
                }
            } else {
                throw new Exception("Không thể cập nhật mật khẩu");
            }
            
        } catch (Exception $e) {
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }
    }

    else if ($_POST['action'] === 'logout') {
        // Chỉ xóa các session liên quan đến người dùng
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        // Thêm các session khác của người dùng nếu có
        
        header('Location: dangnhap.php');
        exit;
    }
}

// Xử lý upload avatar
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    try {
        $file = $_FILES['avatar'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception("Chỉ chấp nhận file ảnh (JPG, PNG, GIF)");
        }
        
        if ($file['size'] > $max_size) {
            throw new Exception("Kích thước file không được vượt quá 5MB");
        }
        
        $upload_dir = 'uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            if (!empty($user['avatar']) && file_exists($user['avatar'])) {
                unlink($user['avatar']);
            }
            
            $stmt = $conn->prepare("UPDATE nguoi_dung SET avatar = ? WHERE id = ?");
            $stmt->execute([$filepath, $user_id]);
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật avatar thành công']);
            exit;
        } else {
            throw new Exception("Không thể upload file");
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin tài khoản</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

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
                    <li><a href="dschua.php">Danh sách chùa</a></li>
                    <li><a href="sukien.php">Lễ hội</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="avatar-container">
                    <img src="<?php echo $user['avatar'] ?? 'image/default-avatar.png'; ?>" 
                         alt="Avatar" class="avatar" id="avatarPreview">
                    <label class="avatar-upload">
                        <input type="file" name="avatar" accept="image/*" onchange="uploadAvatar(this)">
                        <i class="fas fa-camera" style="color: white;"></i>
                    </label>
                </div>
                <h1><?php echo htmlspecialchars($user['ho_ten']); ?></h1>
            </div>

            <div id="messageContainer"></div>
            <div class="profile-content">
                <div class="two-columns">
                    <div class="profile-section">
                        <h2 class="section-title">Thông tin cá nhân</h2>
                        <form id="updateProfileForm" method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-group">
                                <label for="ho_ten">Họ tên</label>
                                <input type="text" id="ho_ten" name="ho_ten" 
                                    value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" 
                                    value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                        </form>
                    </div>

                    <div class="vertical-divider"></div>
                    <div class="profile-section">
                        <h2 class="section-title">Đổi mật khẩu</h2>
                        <form id="changePasswordForm" method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label for="old_password">Mật khẩu cũ</label>
                                <input type="password" id="old_password" name="old_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Mật khẩu mới</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                        </form>
                    </div>
                </div>

                <div class="buttons-container">
                    <form method="POST" action="" class="logout-form">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="btn btn-logout">Đăng xuất</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Định nghĩa hàm showMessage ở mức global
        function showMessage(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            const alertHtml = `<div class="alert ${alertClass}">${message}</div>`;
            
            $('#messageContainer').empty().html(alertHtml);
            
            setTimeout(function() {
                $('#messageContainer .alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 3000);
        }

        $(document).ready(function() {
            // Di chuyển các event handler khác vào đây
            $('#updateProfileForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).text('Đang cập nhật...');
                
                $.ajax({
                    url: 'taikhoan.php',
                    type: 'POST',
                    data: $(this).serialize() + '&ajax=1',
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            showMessage(result.success ? 'success' : 'error', result.message);
                            if (result.success) {
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            }
                        } catch (e) {
                            showMessage('error', 'Có lỗi xảy ra khi xử lý response');
                        }
                    },
                    error: function() {
                        showMessage('error', 'Có lỗi xảy ra khi cập nhật thông tin');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text('Cập nhật thông tin');
                    }
                });
            });

            $('#changePasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).text('Đang xử lý...');
                
                $.ajax({
                    url: 'taikhoan.php',
                    type: 'POST',
                    data: $(this).serialize() + '&ajax=1',
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            showMessage(result.success ? 'success' : 'error', result.message);
                            if (result.success) {
                                $('#changePasswordForm')[0].reset();
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            }
                        } catch (e) {
                            showMessage('error', 'Có lỗi xảy ra khi xử lý response');
                        }
                    },
                    error: function() {
                        showMessage('error', 'Có lỗi xảy ra khi đổi mật khẩu');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text('Đổi mật khẩu');
                    }
                });
            });
        });

        function uploadAvatar(input) {
            if (input.files && input.files[0]) {
                // Hiển thị preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);

                // Thêm loading indicator
                showMessage('success', 'Đang tải ảnh lên...');

                const formData = new FormData();
                formData.append('avatar', input.files[0]);

                $.ajax({
                    url: 'taikhoan.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            showMessage(result.success ? 'success' : 'error', 
                                result.success ? 'Cập nhật avatar thành công' : result.message);
                            if (result.success) {
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            console.log('Raw response:', response);
                            showMessage('error', 'Có lỗi xảy ra khi xử lý response');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', error);
                        showMessage('error', 'Có lỗi xảy ra khi upload avatar');
                    }
                });
            }
        }
    </script>
</body>
</html>
