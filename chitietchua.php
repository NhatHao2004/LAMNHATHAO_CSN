<?php
// Database connection using PDO
try {
    $dsn = "mysql:host=localhost;dbname=chua_khmer;charset=utf8mb4";
    $username = "root";
    $password = "";

    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Lấy ID chùa từ URL và kiểm tra
$chuaId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Kiểm tra xem ID có tồn tại trong CSDL không
$sql = "SELECT id FROM dschua WHERE id = :id AND trang_thai = 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $chuaId, PDO::PARAM_INT);
$stmt->execute();

if (!$stmt->fetch()) {
    // Nếu ID không tồn tại hoặc không hợp lệ, lấy ID đầu tiên từ CSDL
    $sql = "SELECT id FROM dschua WHERE trang_thai = 1 ORDER BY id ASC LIMIT 1";
    $stmt = $conn->query($sql);
    $defaultChua = $stmt->fetch();
    if ($defaultChua) {
        $chuaId = $defaultChua['id'];
    } else {
        die('Không có chùa nào trong cơ sở dữ liệu');
    }
}

// Lấy thông tin cơ bản của chùa (chỉ cần query một lần)
$sql = "SELECT * FROM dschua WHERE id = :id AND trang_thai = 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $chuaId, PDO::PARAM_INT);
$stmt->execute();
$chuaBasic = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chuaBasic) {
    die('Không tìm thấy thông tin chùa');
}

// Lấy thông tin chi tiết của chùa
$sql = "SELECT * FROM chitiet_chua WHERE chua_id = :chua_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':chua_id', $chuaId, PDO::PARAM_INT);
$stmt->execute();
$chuaDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không có thông tin chi tiết, sử dụng thông tin cơ bản
if (!$chuaDetails) {
    $chuaDetails = [
        'ten_chua' => $chuaBasic['ten_chua'],
        'gioi_thieu' => 'Đang cập nhật...',
        'lich_su' => 'Đang cập nhật...',
        'kien_truc' => 'Đang cập nhật...',
        'di_tich' => 'Đang cập nhật...',
        'le_hoi' => 'Đang cập nhật...',
        'video_gioi_thieu' => '',
        'hinh_anh_gioi_thieu' => '',
        'hinh_anh_lich_su' => '',
        'hinh_anh_kien_truc' => '',
        'hinh_anh_di_tich' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết <?php echo htmlspecialchars($chuaBasic['ten_chua']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background: #f0f2f5;
            color: #1a1a1a;
            padding: 30px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        h1 {
            font-size: 2.8em;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 40px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .temple-details section {
            margin-bottom: 40px;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .temple-details h2 {
            font-size: 1.8em;
            color: #1a1a1a;
            margin-bottom: 25px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e0e0e0;
            font-weight: 600;
        }

        .temple-basic-info {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            font-size: 1.1em;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .temple-image {
            text-align: center;
            margin-bottom: 30px;
            border-radius: 12px;
            width: 100%;
            height: 600px;
            position: relative;
            overflow: hidden;
        }

        .temple-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }

        .section-image {
            width: 100%;
            height: 500px;
            object-fit: contain;
            border-radius: 8px;
            margin: 20px 0;
            display: block;
            background-color: #f8f8f8;
            padding: 10px;
        }

        .back-button {
            margin-top: 40px;
            text-align: center;
        }

        .btn {
            font-size: 1.1em;
            padding: 12px 32px;
            background-color: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            display: inline-block;
        }

        .temple-details p {
            font-size: 1.1em;
            line-height: 1.8;
            color: #333;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 2em;
            }

            .temple-details h2 {
                font-size: 1.5em;
            }

            .btn {
                padding: 10px 24px;
                font-size: 1em;
            }

            .temple-image {
                height: 300px;
            }

            .section-image {
                height: 350px;
            }
        }

        iframe {
            width: 100%;
            height: 600px;
            border: none;
            border-radius: 8px;
            margin-top: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.85);
            padding: 20px;
        }

        .modal-content {
            max-width: 85%;
            max-height: 85vh;
            margin: auto;
            display: block;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 25px;
            color: #fff;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($chuaBasic['ten_chua']); ?></h1>

        <div class="temple-basic-info">
            <?php if (!empty($chuaBasic['hinh_anh'])): ?>
            <div class="temple-image">
                <img src="<?php echo htmlspecialchars($chuaBasic['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($chuaBasic['ten_chua']); ?>">
            </div>
            <?php endif; ?>


        <div class="temple-details">
            <?php if (!empty($chuaDetails['gioi_thieu'])): ?>
            <section>
                <h2>Giới thiệu</h2>
                <p><?php echo nl2br(htmlspecialchars($chuaDetails['gioi_thieu'])); ?></p>
                <?php if (!empty($chuaDetails['hinh_anh_gioi_thieu'])): ?>
                <img src="<?php echo htmlspecialchars($chuaDetails['hinh_anh_gioi_thieu']); ?>" alt="Hình ảnh giới thiệu" class="section-image">
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if (!empty($chuaDetails['lich_su'])): ?>
            <section>
                <h2>Lịch sử</h2>
                <p><?php echo nl2br(htmlspecialchars($chuaDetails['lich_su'])); ?></p>
                <?php if (!empty($chuaDetails['hinh_anh_lich_su'])): ?>
                <img src="<?php echo htmlspecialchars($chuaDetails['hinh_anh_lich_su']); ?>" alt="Hình ảnh lịch sử" class="section-image">
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if (!empty($chuaDetails['kien_truc'])): ?>
            <section>
                <h2>Kiến trúc</h2>
                <p><?php echo nl2br(htmlspecialchars($chuaDetails['kien_truc'])); ?></p>
                <?php if (!empty($chuaDetails['hinh_anh_kien_truc'])): ?>
                <img src="<?php echo htmlspecialchars($chuaDetails['hinh_anh_kien_truc']); ?>" alt="Hình ảnh kiến trúc" class="section-image">
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if (!empty($chuaDetails['di_tich'])): ?>
            <section>
                <h2>Di tích</h2>
                <p><?php echo nl2br(htmlspecialchars($chuaDetails['di_tich'])); ?></p>
                <?php if (!empty($chuaDetails['hinh_anh_di_tich'])): ?>
                <img src="<?php echo htmlspecialchars($chuaDetails['hinh_anh_di_tich']); ?>" alt="Hình ảnh di tích" class="section-image">
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if (!empty($chuaDetails['le_hoi'])): ?>
            <section>
                <h2>Lễ hội</h2>
                <p><?php echo nl2br(htmlspecialchars($chuaDetails['le_hoi'])); ?></p>
            </section>
            <?php endif; ?>

            <?php if (!empty($chuaDetails['video_gioi_thieu'])): ?>
                <section>
                    <h2>Video giới thiệu</h2>
                    <?php
                    // Hàm để lấy ID video từ URL YouTube
                    function getYoutubeVideoId($url) {
                        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
                        if (preg_match($pattern, $url, $matches)) {
                            return $matches[1];
                        }
                        return $url; // Trả về nguyên URL nếu không tìm thấy ID
                    }
                    
                    $videoId = getYoutubeVideoId($chuaDetails['video_gioi_thieu']);
                    ?>
                    <iframe 
                        width="850" 
                        height="490" 
                        src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </section>
                <?php endif; ?>
        </div>
        <div class="back-button">
            <a href="javascript:history.back()" class="btn">Quay lại</a>
        </div>
    </div>

    <!-- Modal for image preview -->
    <div id="imageModal" class="modal">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("modalImage");
        var span = document.getElementsByClassName("modal-close")[0];

        // Get all section images
        var images = document.getElementsByClassName("section-image");

        // Add click event to all section images
        for (var i = 0; i < images.length; i++) {
            images[i].onclick = function() {
                modal.style.display = "block";
                modalImg.src = this.src;
            }
        }

        // Close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>