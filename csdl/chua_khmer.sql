-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2024 at 04:01 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chua_khmer`
--

-- --------------------------------------------------------

--
-- Table structure for table `binh_luan`
--

CREATE TABLE `binh_luan` (
  `id` int(11) NOT NULL,
  `id_nguoi_dung` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `ngay_tao` datetime NOT NULL,
  `ngay_cap_nhat` datetime DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1,
  `id_binh_luan_goc` int(11) DEFAULT NULL,
  `noi_dung_tra_loi` text DEFAULT NULL,
  `ten_nguoi_duoc_tra_loi` varchar(255) DEFAULT NULL,
  `nguoi_phan_hoi` int(11) DEFAULT NULL,
  `phan_hoi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dschua`
--

CREATE TABLE `dschua` (
  `id` int(11) NOT NULL,
  `ten_chua` varchar(255) NOT NULL,
  `dia_chi` varchar(255) NOT NULL,
  `tru_tri` varchar(255) DEFAULT NULL,
  `dien_thoai` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `ngay_them` datetime DEFAULT current_timestamp(),
  `trang_thai` tinyint(1) DEFAULT 1 COMMENT 'Trạng thái hiển thị (1: hiện, 0: ẩn)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dschua`
--

INSERT INTO `dschua` (`id`, `ten_chua`, `dia_chi`, `tru_tri`, `dien_thoai`, `email`, `hinh_anh`, `ngay_them`, `trang_thai`) VALUES
(144, 'Chùa Âng', 'xã Lương Hòa, huyện Châu Thành, tỉnh Trà Vinh', 'Nguyễn Văn A', '02943851123', 'chuaang@gmail.com', 'uploads/675d3cfd2d435.jpg', '2024-12-14 09:02:28', 0),
(145, 'Chùa Hang', 'TT. Châu Thành, huyện Châu Thành, tỉnh Trà Vinh', 'Nguyễn Văn B', '02943855456', 'chuahang@gmail.com', 'uploads/675ce77b19d92.jpg', '2024-12-14 09:03:39', 0),
(146, 'Chùa Phướng', 'Đ. Nguyễn Đáng, Phường 7, TP. Trà Vinh', 'Nguyễn Văn C', '02943853789', 'chuaphuong@gmail.com', 'uploads/675ce7d3e3067.jpg', '2024-12-14 09:05:07', 0),
(147, 'Chùa Samrong Ek', 'Phường 8, tỉnh Trà Vinh', 'Nguyễn Văn D', '02943854321', 'chuasamrongek@gmail.com', 'uploads/675d3d13e2b7d.jpg', '2024-12-14 09:07:21', 0),
(148, 'Chùa Vàm Ray', 'Ấp Vàm Ray, huyện Trà Cú, TP. Trà Vinh', 'Nguyễn Văn E', '02943855654', 'chuavamray@gmail.com', 'uploads/675ce8b83aa12.jpg', '2024-12-14 09:08:56', 0),
(149, 'Chùa Pisesaram', 'xã Bình Phú, huyện Càng Long, tỉnh Trà Vinh', 'Nguyễn Văn F', '02943856987', 'chuapisesaram@gmail.com', 'uploads/675d3e041cdba.jpg', '2024-12-14 09:09:57', 0),
(150, 'Chùa Phnô Đôn', 'xã Đại An, huyện Trà Cú, tỉnh Trà Vinh', 'Nguyễn Văn G', '02943857123', 'chuaphnodon@gmail.com', 'uploads/675d3e1b253d1.jpg', '2024-12-14 09:13:46', 0),
(151, 'Chùa Chrôi Tansa', 'xã Kim Sơn, huyện Trà Cú, tỉnh Trà Vinh', 'Nguyễn Văn H', '02943858456', 'chuachroitansa@gmail.com', 'uploads/675d405d2f360.jpg', '2024-12-14 09:15:05', 0),
(152, 'Chùa Kom Pong', '50/1 Lê Lợi, Phường 4, TP.Trà Vinh', 'Nguyễn Văn M', '02943859789', 'chuakompong@gmail.com', 'uploads/675cea726b64f.jpg', '2024-12-14 09:16:18', 0),
(153, 'Chùa Sa Leng Cũ', 'Ấp Bến Chùa, xã Phước Hưng, huyện Trà Cú', 'Nguyễn Văn N', '02943850321', 'chuasalengcu@gmail.com', 'uploads/675ced4e15efc.jpg', '2024-12-14 09:28:30', 0),
(154, 'Chùa Veluvana', 'Ấp 2, xã Phong Phú, huyện Cầu Kè', 'Nguyễn Văn O', '02943851654', 'chuaveluvana@gmail.com', 'uploads/675cee03ac3cd.jpg', '2024-12-14 09:31:31', 0),
(155, 'Chùa Kinh Xáng', 'Ấp Kinh Xáng, xã Phong Phú, huyện Cầu Kè', 'Nguyễn Văn I', '02943851654', 'chuakinhxang@gmail.com', 'uploads/675d4087566a3.jpg', '2024-12-14 09:32:36', 0);

--
-- Triggers `dschua`
--
DELIMITER $$
CREATE TRIGGER `cap_nhat_trang_thai_khi_them` BEFORE INSERT ON `dschua` FOR EACH ROW BEGIN
    IF NEW.trang_thai IS NULL THEN
        SET NEW.trang_thai = 1;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `kiem_tra_trang_thai_khi_sua` BEFORE UPDATE ON `dschua` FOR EACH ROW BEGIN
    IF NEW.trang_thai NOT IN (0, 1) THEN
        SET NEW.trang_thai = 1;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `vai_tro` enum('admin','user') DEFAULT 'user',
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ten_dang_nhap`, `mat_khau`, `ho_ten`, `email`, `vai_tro`, `ngay_tao`, `ngay_cap_nhat`, `avatar`, `admin`) VALUES
(11, 'Hào', '$2y$10$nIuuiShgFEiIoI1ceiess.XBjkJ33170Y98wE/2ZVn9LaRj/YG.fO', 'Nhật Hào', 'Hao@gmail.com', 'user', '2024-11-10 20:58:32', '2024-12-15 10:00:22', 'uploads/avatars/675b9b2685839.jpg', 0),
(12, 'Nhật', '$2y$10$KLDP25YPnEAuLcveSEvbX.dBCTbpMiE0ykI9O.W2eeyDGrF3XNzFq', 'Nhật Hào', 'Nhat@gmail.com', 'user', '2024-11-10 22:04:08', '2024-12-02 23:06:24', 'uploads/avatars/674ddb0095ecc.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `quan_tri_vien`
--

CREATE TABLE `quan_tri_vien` (
  `id` int(11) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `trang_thai` tinyint(4) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quan_tri_vien`
--

INSERT INTO `quan_tri_vien` (`id`, `ten_dang_nhap`, `mat_khau`, `ho_ten`, `email`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(5, 'Lâm Nhật Hào', '$2y$10$6J7MJuXTRUxQoY4clwIqPOd8X.2223f.qKVU.O.4rLQzb/7EZr9Hy', 'Lâm Nhật Hào', 'admin@example.com', 1, '2024-11-06 16:09:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `su_kien`
--

CREATE TABLE `su_kien` (
  `id` int(11) NOT NULL,
  `ten_su_kien` varchar(255) NOT NULL,
  `y_nghia` text DEFAULT NULL,
  `thoi_gian_to_chuc` varchar(255) DEFAULT NULL,
  `cac_nghi_thuc` text DEFAULT NULL,
  `am_thuc_truyen_thong` text DEFAULT NULL,
  `luu_y_khach_tham_quan` text DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1 COMMENT '1: Hoạt động, 0: Ẩn',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `hinh_anh` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `su_kien`
--

INSERT INTO `su_kien` (`id`, `ten_su_kien`, `y_nghia`, `thoi_gian_to_chuc`, `cac_nghi_thuc`, `am_thuc_truyen_thong`, `luu_y_khach_tham_quan`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`, `hinh_anh`) VALUES
(52, 'Lễ hội Chol Chnam Thmay', 'Chol Chnam Thmay là Tết cổ truyền của người Khmer, mang ý nghĩa chào đón năm mới theo lịch Phật giáo, cầu mong sức khỏe, hạnh phúc, và thịnh vượng. Đây cũng là dịp để gia đình sum họp, tạ ơn tổ tiên, và thể hiện lòng kính trọng đối với các vị thần linh.', 'Lễ hội thường diễn ra trong 3 ngày từ ngày 13 đến ngày 15 tháng 4 dương lịch hàng năm.', 'Ngày đầu tiên - Maha Songkran\r\nĐón chào năm mới bằng việc dọn dẹp nhà cửa, chuẩn bị thức ăn dâng cúng.\r\nThắp nhang tại các chùa, làm lễ cầu an và tạ ơn thần linh.\r\nNgày thứ hai - Virak Wanabat\r\nTặng quà và giúp đỡ người nghèo, người già yếu.\r\nThực hiện nghi lễ dâng cơm cho sư sãi và làm lễ cầu siêu cho tổ tiên.\r\nNgày thứ ba - Tngay Leang Saka\r\nTắm tượng Phật bằng nước thơm, biểu trưng cho sự thanh tẩy tội lỗi và đón nhận phước lành.\r\nCác trò chơi dân gian và hội diễn văn nghệ được tổ chức để mừng năm mới.', 'Num ansom (bánh tét Khmer): Bánh tét gói bằng lá chuối, nhân thịt heo hoặc chuối ngọt.\r\nNum krok: Bánh nướng nhỏ từ bột gạo và nước cốt dừa.\r\nCác món cà ri Khmer và chè truyền thống như chè hạt é, chè trôi nước.', 'Mặc trang phục kín đáo, tôn trọng văn hóa chùa chiền.\r\nKhông làm ồn hoặc đùa giỡn trong khu vực hành lễ.\r\nTham gia các nghi thức một cách trang nghiêm và hỏi hướng dẫn nếu không rõ.\r\nTránh lãng phí thức ăn và đồ cúng khi tham gia lễ hội.', 0, '2024-12-15 02:31:16', '2024-12-15 02:55:03', 'uploads/events/675e41197d142.png'),
(53, 'Lễ hội Ok Om Bok', 'Ok Om Bok, còn gọi là Lễ Cúng Trăng, là một trong những lễ hội lớn của người Khmer, nhằm bày tỏ lòng biết ơn đối với thần Mặt Trăng - vị thần bảo trợ mùa màng, đem lại thời tiết thuận lợi, cây trồng bội thu. Đây cũng là dịp để người dân Khmer cầu mong một vụ mùa mới thịnh vượng.', 'Lễ hội thường được tổ chức vào rằm tháng 10 âm lịch hàng năm, khi trăng tròn và sáng nhất, tượng trưng cho sự sung túc, đủ đầy.', 'Lễ Cúng Trăng\r\nMâm lễ gồm cốm dẹp, chuối, dừa, khoai, trái cây và các món truyền thống được chuẩn bị chu đáo.\r\nKhi trăng lên cao, người dân tiến hành lễ cúng với sự trang nghiêm, dâng lễ vật và cầu nguyện cho mùa màng bội thu.\r\nThả đèn nước\r\nSau lễ cúng, người dân thả những chiếc đèn hoa đăng xuống sông, thể hiện lòng biết ơn thần sông và cầu cho nước ngọt, tôm cá dồi dào.\r\nĐua ghe ngo\r\nĐây là hoạt động đặc sắc, thu hút sự tham gia của nhiều đội thi đến từ các phum sóc. Đua ghe ngo không chỉ là môn thể thao truyền thống mà còn là biểu tượng của tinh thần đoàn kết cộng đồng.', 'Cốm dẹp: Món ăn không thể thiếu trong mâm cúng, được làm từ lúa nếp non rang và giã mịn, trộn với đường và dừa nạo.\r\nBánh gừng: Loại bánh giòn, thơm vị gừng đặc trưng.\r\nCác món chè: Như chè trôi nước, chè đậu đen, gắn liền với văn hóa ngọt ngào của người Khmer.', 'Hòa nhã, tôn trọng không gian lễ hội và các nghi thức văn hóa.\r\nKhi tham gia lễ cúng hoặc thả đèn, cần giữ sự trang nghiêm và tránh chen lấn.\r\nKhông xả rác tại khu vực tổ chức lễ hội hoặc ven sông.\r\nHỏi ý kiến người dân địa phương trước khi tham gia các hoạt động truyền thống.', 0, '2024-12-15 02:41:38', '2024-12-15 02:55:02', 'uploads/events/675e44ed83413.jpg'),
(54, 'Lễ hội Sene Dolta', 'Sene Dolta, còn gọi là lễ cúng ông bà, là dịp người Khmer tưởng nhớ tổ tiên, tạ ơn và cầu nguyện cho linh hồn người đã khuất được siêu thoát. Lễ hội cũng là thời điểm để gia đình sum họp, thể hiện lòng hiếu thảo, đồng thời cầu mong sự bình an, hạnh phúc, và mùa màng bội thu.', 'Sene Dolta diễn ra từ ngày 29 tháng 8 đến ngày 1 tháng 9 âm lịch hàng năm, kéo dài trong 3 ngày.', 'Ngày đầu tiên - Chuẩn bị lễ vật\r\nGia đình dọn dẹp nhà cửa, chuẩn bị mâm cơm và các món truyền thống để cúng ông bà.\r\nLễ cúng tổ tiên thường diễn ra tại nhà với các nghi thức trang nghiêm.\r\nNgày thứ hai - Lễ chính\r\nDâng cơm và đồ cúng tại chùa cho các sư sãi, cầu siêu cho linh hồn tổ tiên.\r\nThực hiện nghi thức \"bày cơm cho linh hồn\" ở sân chùa hoặc tại nhà.\r\nCác phum sóc thường tổ chức văn nghệ hoặc hội diễn truyền thống.\r\nNgày thứ ba - Tạ lễ và chia sẻ\r\nNgười dân tạ ơn tổ tiên và chia sẻ lương thực, vật phẩm cho những gia đình khó khăn.\r\nCác trò chơi dân gian và sinh hoạt cộng đồng được tổ chức để tăng thêm không khí đoàn', 'Bánh num ansom (bánh tét Khmer): Loại bánh đặc trưng với nhân chuối hoặc thịt mỡ, dẻo thơm.\r\nNum kanom: Bánh hấp làm từ bột gạo và nước cốt dừa, được trang trí đẹp mắt.\r\nCác món cà ri chay và mặn, phục vụ trong mâm cơm cúng và đãi khách.', 'Tham dự lễ hội với sự tôn trọng các nghi thức tín ngưỡng.\r\nKhi vào chùa, cần mặc trang phục kín đáo và giữ yên lặng.\r\nTìm hiểu văn hóa và ý nghĩa lễ hội để tham gia một cách phù hợp.\r\nHỏi ý kiến người dân địa phương trước khi tham gia các nghi lễ truyền thống.', 0, '2024-12-15 02:45:07', '2024-12-15 02:55:00', 'uploads/events/675e42b3236ad.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nguoi_dung` (`id_nguoi_dung`),
  ADD KEY `id_binh_luan_goc` (`id_binh_luan_goc`);

--
-- Indexes for table `dschua`
--
ALTER TABLE `dschua`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `su_kien`
--
ALTER TABLE `su_kien`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `binh_luan`
--
ALTER TABLE `binh_luan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=379;

--
-- AUTO_INCREMENT for table `dschua`
--
ALTER TABLE `dschua`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `su_kien`
--
ALTER TABLE `su_kien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD CONSTRAINT `binh_luan_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`),
  ADD CONSTRAINT `binh_luan_ibfk_2` FOREIGN KEY (`id_binh_luan_goc`) REFERENCES `binh_luan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
