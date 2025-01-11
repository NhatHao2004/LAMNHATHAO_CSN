-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2025 at 02:28 AM
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
-- Table structure for table `chitiet_chua`
--

CREATE TABLE `chitiet_chua` (
  `id` int(11) NOT NULL,
  `chua_id` int(11) DEFAULT NULL,
  `gioi_thieu` text DEFAULT NULL,
  `hinh_anh_gioi_thieu` varchar(255) DEFAULT NULL,
  `lich_su` text DEFAULT NULL,
  `hinh_anh_lich_su` varchar(255) DEFAULT NULL,
  `kien_truc` text DEFAULT NULL,
  `hinh_anh_kien_truc` varchar(255) DEFAULT NULL,
  `di_tich` text DEFAULT NULL,
  `hinh_anh_di_tich` varchar(255) DEFAULT NULL,
  `le_hoi` text DEFAULT NULL,
  `video_gioi_thieu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitiet_chua`
--

INSERT INTO `chitiet_chua` (`id`, `chua_id`, `gioi_thieu`, `hinh_anh_gioi_thieu`, `lich_su`, `hinh_anh_lich_su`, `kien_truc`, `hinh_anh_kien_truc`, `di_tich`, `hinh_anh_di_tich`, `le_hoi`, `video_gioi_thieu`) VALUES
(1, 1, 'Trà Vinh, mảnh đất gắn liền với những ngôi chùa Khmer cổ kính, là nơi giữ gìn những giá trị văn hóa, tâm linh đặc sắc của cộng đồng người Khmer. Trong số đó, Chùa Âng (hay Wat Angkor Raig Borei theo tên gọi trong ngôn ngữ Pali) là một trong những ngôi chùa nổi bật nhất của tỉnh Trà Vinh. Chùa nằm tại Phường 8, thành phố Trà Vinh, trong khu vực danh thắng Ao Bà Om – một trong những địa danh du lịch nổi tiếng, cùng với Bảo tàng Văn hóa Dân tộc Khmer, tạo nên một điểm đến lý tưởng cho du khách muốn khám phá vẻ đẹp văn hóa và lịch sử của người Khmer. Đây là một trong những ngôi chùa có giá trị lịch sử, văn hóa và nghệ thuật tiêu biểu của khu vực đồng bằng sông Cửu Long.', 'uploads/677d38aa2c4f1.jpg', 'Chùa Âng được xây dựng từ rất lâu, vào thế kỷ thứ 10 (khoảng năm 990) và được cải tạo, xây dựng lại quy mô như hiện nay vào năm Thiệu Trị thứ 3 (1842), dưới triều đại nhà Nguyễn. Mặc dù trải qua nhiều lần trùng tu, tu sửa, nhưng ngôi chánh điện của chùa vẫn giữ nguyên vẹn nét cổ kính ban đầu. Chùa Âng, như nhiều ngôi chùa Khmer khác, không chỉ là nơi thờ Phật mà còn là nơi bảo tồn văn hóa, giáo dục cộng đồng, nơi tổ chức các lễ hội, sinh hoạt tôn giáo, truyền dạy chữ Pali và chữ Khmer cho các thế hệ sau.\r\nChùa Âng có một lịch sử dài và đầy ấn tượng, chứng kiến sự phát triển mạnh mẽ của cộng đồng Khmer tại Trà Vinh qua các giai đoạn lịch sử. Trải qua bao thăng trầm của thời gian, ngôi chùa vẫn vững vàng tồn tại, trở thành niềm tự hào không chỉ của cộng đồng Khmer, mà còn của tất cả các dân tộc ở Trà Vinh. Chùa Âng đã được công nhận là di tích lịch sử – văn hóa cấp quốc gia vào năm 1994.', 'uploads/677d38aa2cc90.jpg', 'Kiến trúc của Chùa Âng là một tác phẩm nghệ thuật đậm đà bản sắc văn hóa Khmer, với sự kết hợp tuyệt vời giữa các yếu tố tôn giáo và nghệ thuật kiến trúc, điêu khắc, hội họa. Chùa được xây dựng theo mô hình một quần thể kiến trúc bao gồm chánh điện, tăng xá, giảng đường dạy chữ Pali và chữ Khmer, tất cả đều bao quanh một không gian trang nghiêm và thanh tịnh.\r\nChùa Âng quay mặt về phía đông, theo quan niệm Phật giáo, tượng trưng cho hướng đi của Đức Phật Thích Ca từ phía tây sang để cứu độ chúng sinh. Các công trình trong chùa, đặc biệt là chánh điện, đều được thiết kế tinh xảo, phản ánh trình độ cao của các nghệ nhân Khmer. Chánh điện là trung tâm tôn giáo của chùa, với 18 cột gỗ quý nâng đỡ mái chùa. Các cột này được chạm khắc hình rồng, sơn son thếp vàng, tạo nên không gian uy nghiêm và linh thiêng. Mái chùa có ba cấp, hai mái trên cùng rất cao và dốc, tạo cảm giác trang trọng và linh thiêng mỗi khi du khách chiêm bái.\r\nMột điểm đặc biệt trong kiến trúc của chùa Âng là ngôi tháp năm ngọn đặt phía trước chánh điện. Tháp này là nơi lưu giữ di cốt của các vị sư trụ trì qua các thời kỳ. Đây là một công trình rất đặc trưng của các ngôi chùa Khmer, mang đậm ảnh hưởng của Ấn Độ giáo, với tượng trưng vũ trụ và sự kết nối giữa trời và đất, giữa con người và thần linh.\r\nNgoài ra, các công trình phụ khác như cổng chùa và các khu vực xung quanh cũng được trang trí rất công phu, với các tượng điêu khắc hình chằn, tiên nữ, chim thần, mang đậm dấu ấn nghệ thuật Khmer. Cổng chùa được xây dựng bằng đá, với những hình tượng thần linh bảo vệ, tạo nên vẻ uy nghiêm và thần bí ngay từ lần đầu tiên đặt chân đến chùa.', 'uploads/677d36fbb075a.jpg', 'Chùa Âng không chỉ là một nơi thờ Phật, mà còn là một di tích lịch sử - văn hóa cấp quốc gia, là điểm hội tụ các giá trị văn hóa, nghệ thuật Khmer. Các bức bích họa trong chánh điện được vẽ rất công phu, thể hiện các giai đoạn trong cuộc đời Đức Phật Thích Ca, từ Phật đản sanh, Phật xuất gia, Phật thành đạo, cho đến Phật nhập Niết Bàn. Đây là những tác phẩm nghệ thuật đậm nét, không chỉ là hình ảnh tôn thờ Đức Phật, mà còn là những minh họa sinh động về con đường tu hành của Ngài.\r\nKhuôn viên chùa rộng đến 4 ha, được bao quanh bởi một hệ sinh thái phong phú, với nhiều cây xanh cổ thụ như sao, dầu, tre, trúc. Các hàng cây này tạo nên một không gian mát mẻ, trong lành, rất thích hợp cho việc chiêm bái và thư giãn tâm hồn. Khuôn viên chùa còn là nơi tổ chức các hoạt động văn hóa, tôn giáo của cộng đồng Khmer địa phương, là nơi kết nối giữa các thế hệ, giữa quá khứ và hiện tại.', 'uploads/677d38aa2cf05.jpg', 'Các lễ hội của đồng bào Khmer không chỉ là dịp tôn vinh truyền thống tín ngưỡng mà còn thể hiện sự gắn bó sâu sắc với đạo Phật và cộng đồng. Ba lễ hội tiêu biểu là Tết Chol Chnam Thmay, Lễ hội Sen Đôn Ta, và Lễ hội Dâng Y Kathinat. Tết Chol Chnam Thmay, tổ chức vào giữa tháng 4 dương lịch, là dịp đón năm mới với các hoạt động cúng dường, tụng kinh và vui chơi dân gian, mang ý nghĩa cầu mong một năm an lành. Lễ hội Sen Đôn Ta, kéo dài 15 ngày vào tháng 10 âm lịch, là dịp tưởng nhớ tổ tiên và thể hiện lòng hiếu thảo, với các nghi thức cúng dường và các hoạt động cộng đồng. Lễ hội Dâng Y Kathinat, diễn ra sau ba tháng an cư của các sư, là thời gian để người dân tặng phẩm vật và áo cà sa cho các sư, thể hiện lòng kính trọng và công đức. Các lễ hội này không chỉ giữ gìn bản sắc văn hóa mà còn thắt chặt tình đoàn kết trong cộng đồng Khmer.', 'https://www.youtube.com/watch?v=885UNlZcUCA'),
(2, 2, 'Chùa Hang, hay còn gọi là Chùa Kompông Chrây, là một ngôi chùa Phật giáo Nam Tông Khmer nổi tiếng tại Trà Vinh, Việt Nam. Ngôi chùa này không chỉ là nơi thờ tự linh thiêng mà còn là biểu tượng văn hóa, tâm linh quan trọng của cộng đồng người Khmer ở khu vực Đồng bằng sông Cửu Long.\r\nChùa Hang, tên đầy đủ là Chùa Kompông Chrây (có nghĩa là \"bến cây đa\"), tọa lạc tại khóm 4, thị trấn Châu Thành, huyện Châu Thành, tỉnh Trà Vinh, cách trung tâm thành phố Trà Vinh khoảng 6 km. Đây là một trong những ngôi chùa có lịch sử lâu đời và kiến trúc độc đáo nhất ở vùng Đồng bằng sông Cửu Long. Ngôi chùa không chỉ thu hút du khách bởi cảnh sắc thiên nhiên tuyệt đẹp, mà còn bởi vai trò quan trọng trong đời sống văn hóa và tinh thần của người dân Khmer.', 'uploads/677d3a9973736.jpg', 'Chùa Kompông Chrây được xây dựng vào năm 1637, là một trong những ngôi chùa cổ nhất của người Khmer tại Trà Vinh. Ngôi chùa có tên gọi Kompông Chrây, nghĩa là \"bến cây đa\", bởi ngày xưa, nơi đây từng là bến đò với một cây đa cổ thụ to lớn, gắn liền với sinh hoạt cộng đồng của người dân. Người dân địa phương thường xuyên qua lại bến này để giao thương và đi lại bằng thuyền, vì vậy khi chùa được xây dựng, người ta đã đặt tên theo tên bến sông này.\r\nTrong lịch sử, chùa Kompông Chrây đã trải qua nhiều biến cố. Đặc biệt, vào năm 1968, trong cuộc Tổng tấn công Tết Mậu Thân, chùa bị bom đạn tàn phá nặng nề, gây thiệt hại lớn về cơ sở vật chất và làm hư hại nhiều di sản văn hóa của chùa. Tuy nhiên, nhờ sự nỗ lực của sư Thạch Suông, người đã trở về chùa làm trụ trì vào năm 1977, ngôi chùa đã được phục dựng và tu sửa lại, dần trở lại với diện mạo khang trang như ngày nay.', 'uploads/677d3a9973a0d.jpg', 'Chùa Kompông Chrây có một kiến trúc đặc trưng của Phật giáo Nam Tông Khmer, kết hợp giữa những yếu tố văn hóa dân gian và tôn giáo, tạo nên sự hòa quyện độc đáo và ấn tượng.\r\nCổng Chính: Cổng chính của chùa nhìn ra sông, được xây dựng kiên cố với hai bức tượng chằn Yak to như người thật đứng ở hai bên. Tượng Yak trong truyền thuyết Phật giáo là những sinh vật hung ác nhưng sau khi được Phật giáo hóa, chúng trở thành các bảo vệ cho đền chùa, mang lại sự bình an. Hai tượng chằn Yak ở đây không chỉ có giá trị về mặt thẩm mỹ mà còn mang tính biểu tượng mạnh mẽ.\r\nCổng Phụ: Cổng phụ của chùa nằm ven tỉnh lộ 36 và có một cấu trúc rất đặc biệt. Nó được xây dựng theo dạng vòm cuốn và có tường rất dày. Đặc biệt, cổng phụ này có hình dáng giống như một cái hang, nên người dân trong vùng quen gọi ngôi chùa là \"Chùa Hang\". Đây cũng chính là điểm nhấn tạo nên sự khác biệt cho chùa Kompông Chrây, khiến nó trở thành một trong những địa điểm thu hút du khách.\r\nChánh Điện: Chánh điện của chùa nằm trên một nền cao 3 mét, với nhiều bậc thang dẫn lên. Mái của chánh điện được thiết kế theo kiểu chồng nhiều lớp, tạo nên một không gian rộng rãi và cao vút. Đỉnh mái của chánh điện nhọn như một chóp tháp, là biểu tượng của sự giác ngộ và con đường tu hành của các vị sư. Trong chánh điện, có nhiều bức tượng Phật Thích Ca với kích thước khác nhau, từ tượng lớn ở trung tâm đến những tượng nhỏ hơn ở các vị trí xung quanh. Tất cả các tượng Phật này đều được chạm khắc tinh xảo và đầy tính nghệ thuật.\r\nCột Cờ: Cột cờ trước chánh điện của chùa có hình dạng rất độc đáo, được chạm khắc thành hình một con rắn thần Nara với 7 đầu. Theo truyền thuyết, rắn Nara tượng trưng cho sự bảo vệ Phật Thích Ca trong 7 ngày đêm thiền định, đồng thời cũng biểu thị cho sự vững vàng và kiên định trong con đường tu hành. Cột cờ này là một tác phẩm nghệ thuật độc đáo, kết hợp giữa tinh thần tôn giáo và nghệ thuật điêu khắc Khmer.', 'uploads/677d3a9973c07.jpg', 'Chùa Kompông Chrây nổi bật không chỉ vì lịch sử lâu dài mà còn nhờ vào những di sản nghệ thuật đặc sắc. Một trong những điểm độc đáo của chùa là xưởng điêu khắc gỗ, nơi những nghệ nhân tạo ra những tác phẩm điêu khắc tinh xảo từ các gốc cây cổ thụ trong vườn chùa. Những cây cổ thụ bị tàn phá trong chiến tranh để lại nhiều bộ gốc rễ còn nguyên vẹn, với những hình thù kỳ thú. Chính sư Thạch Suông đã nhận ra giá trị của những gốc cây này và quyết định mời nghệ nhân điêu khắc nổi tiếng Thạch Buôl từ Vĩnh Long về mở lớp dạy nghề cho các sư sãi và thanh niên có năng khiếu.\r\nCác tác phẩm điêu khắc từ chùa rất đa dạng, bao gồm tượng Phật, tượng động vật, các cảnh sinh hoạt đời thường của người Khmer. Những tác phẩm này không chỉ thể hiện tài năng điêu luyện của các nghệ nhân mà còn phản ánh văn hóa, tín ngưỡng của người dân Khmer. Một trong những tác phẩm nổi bật là tượng \"Cửu Long\", mô tả hình ảnh của 9 con rồng trong thần thoại, và tượng \"Trâu kéo cộ\", miêu tả cảnh sinh hoạt thường nhật của người dân Khmer trong nông thôn.\r\nNhững tác phẩm này đã được du khách trong và ngoài nước đánh giá cao và là một phần không thể thiếu trong di sản văn hóa của chùa. Chùa Kompông Chrây không chỉ là nơi tu hành mà còn là trung tâm giáo dục đạo đức và văn hóa của người Khmer. Tại đây, các sư sãi và thanh niên Khmer được học các giá trị đạo đức, các bài học về Phật pháp và những kỹ năng thủ công như điêu khắc gỗ. Chùa còn tổ chức các hoạt động văn hóa như múa, hát dân ca Khmer, tạo cơ hội cho thế hệ trẻ hiểu và tiếp nối các truyền thống văn hóa đặc sắc của dân tộc.\r\nNgoài ra, chùa cũng là nơi lưu giữ nhiều giá trị lịch sử và văn hóa của cộng đồng Khmer, góp phần bảo tồn và phát huy những đặc trưng văn hóa Khmer giữa thời kỳ phát triển hiện đại.', 'uploads/677d3a9973de4.jpg', 'Các lễ hội của đồng bào Khmer không chỉ là dịp tôn vinh truyền thống tín ngưỡng mà còn thể hiện sự gắn bó sâu sắc với đạo Phật và cộng đồng. Ba lễ hội tiêu biểu là Tết Chol Chnam Thmay, Lễ hội Sen Đôn Ta, và Lễ hội Dâng Y Kathinat. Tết Chol Chnam Thmay, tổ chức vào giữa tháng 4 dương lịch, là dịp đón năm mới với các hoạt động cúng dường, tụng kinh và vui chơi dân gian, mang ý nghĩa cầu mong một năm an lành. Lễ hội Sen Đôn Ta, kéo dài 15 ngày vào tháng 10 âm lịch, là dịp tưởng nhớ tổ tiên và thể hiện lòng hiếu thảo, với các nghi thức cúng dường và các hoạt động cộng đồng. Lễ hội Dâng Y Kathinat, diễn ra sau ba tháng an cư của các sư, là thời gian để người dân tặng phẩm vật và áo cà sa cho các sư, thể hiện lòng kính trọng và công đức. Các lễ hội này không chỉ giữ gìn bản sắc văn hóa mà còn thắt chặt tình đoàn kết trong cộng đồng Khmer.', 'https://www.youtube.com/watch?v=NHQ0bZg7J_U'),
(3, 3, 'Chùa Ông Mẹt là một trong những ngôi chùa cổ và có giá trị lịch sử, văn hóa sâu sắc tại tỉnh Trà Vinh. Đây không chỉ là một công trình tôn giáo của cộng đồng Khmer mà còn là trung tâm văn hóa, nơi gìn giữ và phát huy các giá trị truyền thống của người Khmer Nam Bộ.\r\nChùa Ông Mẹt tọa lạc tại số 50/1 đường Lê Lợi, phường 1, thành phố Trà Vinh, tỉnh Trà Vinh. Là một trong những ngôi chùa cổ nhất của người Khmer trong tỉnh, Chùa Ông Mẹt đã được Bộ Văn hóa, Thể thao và Du lịch công nhận là di tích cấp Quốc gia vào ngày 3 tháng 3 năm 2009. Ngôi chùa này còn được biết đến là nơi đặt Văn phòng Trị sự của Phật giáo Khmer hệ phái Mahanikay, đóng góp nhiều vào việc bảo tồn và phát huy những giá trị văn hóa truyền thống của Phật giáo Khmer.', 'uploads/677d3c7d858b8.jpg', 'Chùa Ông Mẹt được hình thành từ năm 642, với tên gọi ban đầu là Wat Kompong, nghĩa là \"Chùa Bến\", vì trước đây chùa nằm gần bến đò, nơi có các con rạch và dòng sông, thuận tiện cho việc neo đậu thuyền bè. Sau này, chùa được đặt tên là Bodhisàlaraja, tức là cây bồ đề – biểu tượng của sự giác ngộ và trí tuệ, với niềm tin rằng cây bồ đề là vua của các loài cây.\r\nTheo truyền thuyết, có một tượng Phật nổi lên trên mặt nước trong một con rạch gần chùa. Người dân đã không thể kéo tượng lên bằng sức thường, cho đến khi một vị thánh tăng báo mộng về cách làm lễ cầu an và lễ đăng quang. Sau đó, tượng Phật được đưa lên chùa và đặt tại chính điện, nơi thờ Phật ngày nay.\r\nChùa Ông Mẹt cũng là nơi đào tạo nhiều thế hệ sư sãi cho các ngôi chùa Khmer trong khu vực, đóng góp vào việc duy trì và phát triển đạo Phật tại tỉnh Trà Vinh.', 'uploads/677d3c7d85b9c.jpg', 'Chùa Ông Mẹt có một hệ thống kiến trúc rất đặc trưng của văn hóa Khmer Nam Bộ, bao gồm nhiều công trình kiến trúc lớn nhỏ như cổng chùa, chính điện, sala (nhà hội), cột cờ, tăng xá, tháp tưởng niệm… Mặc dù các công trình được xây dựng vào các thời kỳ khác nhau và bằng những chất liệu khác nhau, nhưng tất cả đều hòa quyện tạo thành một tổng thể kiến trúc đậm đà bản sắc Khmer Nam Bộ.\r\nCổng Chùa: Cổng chùa được xây dựng vững chãi và oai nghiêm, với 8 cột trụ lớn nâng đỡ mái cổng. Trên đầu mỗi cột đều được chạm khắc hình chim thần Keyno, biểu trưng cho sự mời gọi, đón tiếp du khách vào tham quan. Các bờ tường bên cạnh cổng chùa được trang trí bằng hình ảnh các rắn bảy đầu, một biểu tượng linh thiêng của người Khmer.\r\nChánh Điện: Ngôi chánh điện được xây dựng với 32 cột trụ gỗ quý, chia thành 4 hàng, với các hoa văn chạm trổ tinh xảo, sơn son thếp vàng. Trên bệ thờ, có tượng Đức Phật Thích Ca uy nghiêm, cao 4,4m, dài 5m và rộng 4,3m. Đây là một trong những tượng Phật lớn nhất trong các chùa Khmer ở Trà Vinh. Mái của chính điện được thiết kế theo hình dạng của một đàn rồng, tượng trưng cho sự bảo vệ và che chở.\r\nThư Viện: Phía sau chánh điện là Thư viện có kiến trúc nhà sàn gỗ truyền thống của người Khmer Nam Bộ. Toàn bộ 24 đầu cột, xiên tâm, xiên dọc đều được chạm khắc tinh xảo và sơn son thếp vàng. Thư viện này không chỉ chứa đựng các thư tịch cổ mà còn là nơi học tập của các vị sư sãi và cộng đồng phật tử trong vùng.', 'uploads/677d3c7d85d4a.jpg', 'Chùa Ông Mẹt không chỉ nổi bật với kiến trúc độc đáo mà còn lưu giữ nhiều di tích có giá trị lịch sử và văn hóa:\r\nTượng Phật Thích Ca: Đây là tượng Phật uy nghiêm, đặt trên tòa sen cao 4,4m, dài 5m và rộng 4,3m. Tượng Phật này là một trong những tượng Phật lớn nhất trong các ngôi chùa Khmer ở tỉnh Trà Vinh, được tạc với những chi tiết tinh xảo, thể hiện sự tôn kính và đức hạnh của Phật Thích Ca.\r\nThư Viện Chùa Ông Mẹt: Thư viện của chùa không chỉ là nơi lưu giữ nhiều sách vở, thư tịch quý giá mà còn là nơi đào tạo học hỏi cho các thế hệ sư sãi và phật tử. Kiến trúc của thư viện mang đậm dấu ấn văn hóa Khmer, với những chi tiết trang trí tỉ mỉ và sơn son thếp vàng.', 'uploads/677d3c7d85ebd.jpg', 'Các lễ hội của đồng bào Khmer không chỉ là dịp tôn vinh truyền thống tín ngưỡng mà còn thể hiện sự gắn bó sâu sắc với đạo Phật và cộng đồng. Ba lễ hội tiêu biểu là Tết Chol Chnam Thmay, Lễ hội Sen Đôn Ta, và Lễ hội Dâng Y Kathinat. Tết Chol Chnam Thmay, tổ chức vào giữa tháng 4 dương lịch, là dịp đón năm mới với các hoạt động cúng dường, tụng kinh và vui chơi dân gian, mang ý nghĩa cầu mong một năm an lành. Lễ hội Sen Đôn Ta, kéo dài 15 ngày vào tháng 10 âm lịch, là dịp tưởng nhớ tổ tiên và thể hiện lòng hiếu thảo, với các nghi thức cúng dường và các hoạt động cộng đồng. Lễ hội Dâng Y Kathinat, diễn ra sau ba tháng an cư của các sư, là thời gian để người dân tặng phẩm vật và áo cà sa cho các sư, thể hiện lòng kính trọng và công đức. Các lễ hội này không chỉ giữ gìn bản sắc văn hóa mà còn thắt chặt tình đoàn kết trong cộng đồng Khmer.', 'https://www.youtube.com/watch?v=VIVQeAr39pI'),
(4, 4, 'Chùa Samrong Ek, một ngôi chùa mang đậm dấu ấn văn hóa và truyền thống của người Khmer, là một điểm đến không thể thiếu khi du khách có dịp đến Trà Vinh. Với không gian thanh tịnh, vẻ đẹp kiến trúc độc đáo, cùng những giá trị tâm linh sâu sắc, chùa đã trở thành nơi sinh hoạt tôn giáo và trung tâm văn hóa quan trọng của cộng đồng người Khmer tại đây.\r\nChùa Samrong Ek, theo tiếng Khmer có nghĩa là \"cây sam rông già lẻ loi\", được đặt tên như vậy vì trước đây khu vực quanh chùa có rất nhiều cây sam rông, trong đó có một cây to lớn, đứng cô đơn giữa không gian bao la. Chùa tọa lạc trên diện tích 4 hecta, thuộc phường 8, thị xã Trà Vinh, tỉnh Trà Vinh. Nơi đây không chỉ là một địa điểm tôn nghiêm cho các Phật tử mà còn là một không gian sinh hoạt văn hóa của người Khmer, thu hút du khách thập phương đến tham quan, vãn cảnh và tìm hiểu văn hóa Khmer.', 'uploads/677d3d305ca3c.jpg', 'Chùa Samrong Ek có một lịch sử lâu dài và gắn bó với sự phát triển của cộng đồng Khmer tại Trà Vinh. Theo truyền thuyết và lời kể của các bô lão trong vùng, chùa được xây dựng vào năm 1642. Tuy nhiên, một số tài liệu khác lại cho rằng chùa có tuổi đời lâu hơn, khoảng từ năm 1373. Chùa đã trải qua nhiều biến cố, chịu ảnh hưởng của thiên tai và chiến tranh, khiến công trình bị hư hại nặng nề.\r\nVào năm 1850, chùa được xây dựng lại trên nền đất cũ, mang lại một diện mạo mới cho ngôi chùa. Năm 1944, chính điện của chùa được trùng tu và sửa chữa lại, trở nên trang nghiêm và uy nghi hơn. Đến năm 1964, trường học Pali dành cho chư tăng được xây dựng, tạo điều kiện để các vị sư có nơi học tập, tu dưỡng. Năm 1993, giảng đường của chùa được hoàn thiện. Những năm gần đây, chùa tiếp tục được tu bổ và là nơi tổ chức các hoạt động văn hóa, tâm linh, phục vụ nhu cầu sinh hoạt tín ngưỡng của cộng đồng Khmer địa phương.', 'uploads/677d4046be2c1.jpg', 'Chùa Samrong Ek là một trong những ngôi chùa đặc trưng của Phật giáo Khmer Nam Bộ, với kiến trúc mang đậm ảnh hưởng của văn hóa dân tộc Khmer. Ngôi chùa được xây dựng theo mô hình truyền thống, với các công trình kiến trúc đặc trưng như cổng chùa, chính điện, tăng xá, trường học Pali và giảng đường, tất cả tạo thành một tổng thể kiến trúc hài hòa và đẹp mắt.\r\nCổng Chùa và Hàng Rào: Cổng chùa Samrong Ek mang đậm nét văn hóa Khmer, với hình dáng kiến trúc chóp vươn cao, thể hiện sự uy nghi, trang trọng. Hàng rào xung quanh chùa được xây dựng kiên cố và đẹp mắt, với các họa tiết chạm khắc đặc trưng của nghệ thuật Khmer.\r\nChánh Điện: Chánh điện là công trình quan trọng nhất của chùa, được xây dựng ở vị trí trung tâm và có nền cao hơn các công trình khác. Đây là nơi thờ Phật Thích Ca, với tượng Phật cao 3m tạc trong tư thế thiền định. Chánh điện được trang trí công phu, với các chi tiết chạm khắc tinh xảo, thể hiện đậm nét nghệ thuật điêu khắc truyền thống của người Khmer.\r\nTăng Xá và Giảng Đường: Tăng xá là nơi các chư tăng sinh hoạt, học tập và tu hành, được xây dựng gần chánh điện. Giảng đường là nơi tổ chức các buổi giảng pháp, hội thảo, và các hoạt động sinh hoạt văn hóa, tâm linh của cộng đồng.\r\nChùa Samrong Ek là nơi lý tưởng để tìm hiểu về kiến trúc truyền thống và nghệ thuật chạm khắc của người Khmer, với những công trình mang đậm tính linh thiêng và nghệ thuật cao.', 'uploads/677d3d305d0e5.jpg', 'Chùa Samrong Ek không chỉ nổi bật bởi kiến trúc mà còn là nơi lưu giữ những di vật quý giá:\r\nTượng Phật Thích Ca: Trong chánh điện của chùa, có một pho tượng Phật Thích Ca cao 3m, tạc trong tư thế thiền định. Đây là một tác phẩm nghệ thuật điêu khắc tinh xảo, mang đậm giá trị tôn giáo và văn hóa.\r\nTượng Vishnu: Ngoài ra, chùa còn có một pho tượng quý được tạc bằng đá, chạm hình thần Vishnu đứng, cao 1,2m. Pho tượng này có niên đại từ thế kỷ VI, là một trong những di vật quý giá và có giá trị lịch sử lớn.\r\n18 ngọn tháp: Rải rác trong khuôn viên chùa là 18 ngọn tháp cao, được chạm khắc tỉ mỉ, tinh tế, mang đậm ảnh hưởng của văn hóa Khmer. Những ngọn tháp này không chỉ có giá trị về mặt thẩm mỹ mà còn là những di tích văn hóa, tôn giáo quan trọng của cộng đồng người Khmer.', 'uploads/677d3d305d3aa.jpg', 'Các lễ hội của đồng bào Khmer không chỉ là dịp tôn vinh truyền thống tín ngưỡng mà còn thể hiện sự gắn bó sâu sắc với đạo Phật và cộng đồng. Ba lễ hội tiêu biểu là Tết Chol Chnam Thmay, Lễ hội Sen Đôn Ta, và Lễ hội Dâng Y Kathinat. Tết Chol Chnam Thmay, tổ chức vào giữa tháng 4 dương lịch, là dịp đón năm mới với các hoạt động cúng dường, tụng kinh và vui chơi dân gian, mang ý nghĩa cầu mong một năm an lành. Lễ hội Sen Đôn Ta, kéo dài 15 ngày vào tháng 10 âm lịch, là dịp tưởng nhớ tổ tiên và thể hiện lòng hiếu thảo, với các nghi thức cúng dường và các hoạt động cộng đồng. Lễ hội Dâng Y Kathinat, diễn ra sau ba tháng an cư của các sư, là thời gian để người dân tặng phẩm vật và áo cà sa cho các sư, thể hiện lòng kính trọng và công đức. Các lễ hội này không chỉ giữ gìn bản sắc văn hóa mà còn thắt chặt tình đoàn kết trong cộng đồng Khmer.', 'https://www.youtube.com/watch?v=o6tJBMHGHeI&t=2s'),
(5, 5, 'Chùa Vàm Ray tọa lạc tại ấp Vàm Ray, xã Hàm Tân (trước kia là xã Hàm Giang), huyện Trà Cú, tỉnh Trà Vinh, cách thành phố Trà Vinh khoảng 35km. Đây là một ngôi chùa nổi bật không chỉ vì quy mô lớn mà còn vì vẻ đẹp huyền bí, cổ kính mang đậm nét văn hóa Khmer. Với diện tích rộng lớn và không gian thanh tịnh, chùa Vàm Ray luôn là điểm đến không thể bỏ qua của những ai yêu thích tìm hiểu về lịch sử và văn hóa Phật giáo Nam Tông.\r\nKhông chỉ là một ngôi chùa dành cho tín đồ Phật giáo, chùa Vàm Ray còn là nơi diễn ra các hoạt động văn hóa, lễ hội truyền thống đặc sắc của người Khmer, bao gồm những lễ hội lớn như Chôl Chnăm Thmây (Tết Nguyên Đán của người Khmer), Lễ Sêndôlta (Lễ Vu Lan), Lễ Okombok (Lễ Tạ Ơn Mùa Màng) và Lễ Dâng Y. Đây cũng là nơi sinh hoạt cộng đồng, giáo dục tôn giáo và giảng dạy về văn hóa Khmer cho thế hệ trẻ.', 'uploads/677d3de939c64.jpg', 'Chùa Vàm Ray, tên đầy đủ là Chùa Compongpdhipruhs Bonraichas, đã có hơn 600 năm lịch sử, là một trong những ngôi chùa lâu đời và quan trọng của cộng đồng Khmer Nam Bộ. Ngôi chùa này không chỉ có giá trị tôn giáo mà còn đóng vai trò quan trọng trong việc gìn giữ và phát huy những nét đẹp văn hóa, truyền thống của dân tộc Khmer.\r\nChùa Vàm Ray đã trải qua nhiều lần tu sửa và phục dựng. Trong giai đoạn đầu, ngôi chùa có thể chỉ là một ngôi chùa nhỏ của cộng đồng Khmer địa phương, nhưng trải qua thời gian, chùa đã được mở rộng và xây dựng thành một công trình lớn, có ảnh hưởng mạnh mẽ trong đời sống tinh thần của cộng đồng Khmer.\r\n\r\nVào năm 2004, ngôi chùa đã bắt đầu được phục dựng và cải tạo lại hoàn toàn dưới sự tài trợ của ông Trầm Bê, một người con của Trà Vinh, với mục đích bảo tồn và gìn giữ các giá trị văn hóa, lịch sử quý báu của chùa. Quá trình thi công kéo dài trong 3 năm, từ tháng 5 năm 2004 đến tháng 3 năm 2008. Sau khi hoàn tất, vào ngày 22 tháng 5 năm 2010, chùa Vàm Ray chính thức được khánh thành và mở cửa đón du khách.\r\nVới tổng kinh phí lên đến hơn 1 triệu USD, chùa không chỉ được xây dựng lại mà còn được trang trí và trùng tu lại những công trình nghệ thuật, tượng Phật, các bức tranh tường, mái vòm và các chi tiết chạm khắc, giúp ngôi chùa trở thành một công trình tôn giáo và nghệ thuật độc đáo của người Khmer tại Việt Nam.', 'uploads/677d3de939f35.jpg', 'Chùa Vàm Ray mang đậm phong cách kiến trúc Angkor, một biểu tượng văn hóa đặc trưng của người Khmer Campuchia. Ngôi chùa không chỉ nổi bật với sự bề thế mà còn gây ấn tượng mạnh mẽ nhờ vẻ đẹp rực rỡ của màu vàng, một gam màu được sử dụng xuyên suốt trong các công trình, tượng trưng cho sự uy nghi, tôn kính và thiêng liêng của Phật giáo.\r\nCổng Chùa: Ngay khi bước vào khuôn viên chùa, du khách sẽ không khỏi choáng ngợp trước chiếc cổng chùa hoành tráng được sơn màu mạ vàng. Đỉnh của cổng được thiết kế hình những ngọn tháp nhọn chồng nhiều tầng, ẩn mình trong không gian cây xanh, tạo nên một không gian huyền bí, đậm chất Khmer. Màu vàng rực rỡ của cổng chùa càng làm nổi bật lên sự uy nghi và linh thiêng của công trình.\r\nChánh Điện: Bên trong chính điện của chùa Vàm Ray là không gian thanh thoát, rộng lớn, được trang hoàng lộng lẫy với các bức tranh tường mô tả cuộc đời và giáo lý của Đức Phật. Những bức tranh này không chỉ phản ánh các sự kiện trọng đại trong cuộc đời Phật mà còn thể hiện những giá trị tâm linh, trí tuệ của người Khmer. Không gian chính điện cao, thoáng đãng, mang đến cảm giác mát mẻ và dễ chịu cho các phật tử khi hành lễ cũng như cho du khách tham quan.\r\nMột trong những điểm đặc sắc của chính điện là tượng Đức Phật Thích Ca nhập Niết Bàn, với tổng chiều dài lên đến 54m. Tượng Phật này được đặt trên một bệ cao và được sơn phủ thiếp vàng lộng lẫy. Đây là một trong những tượng Phật lớn nhất trong các ngôi chùa Khmer ở Việt Nam. Với kích thước khổng lồ, tượng Phật mang đến sự tôn nghiêm, thanh tịnh, giúp phật tử và du khách cảm nhận được sự an lạc và niềm tin vào giáo lý Phật giáo.\r\nCột Trụ Naga: Một điểm đặc biệt khác trong khuôn viên chùa Vàm Ray là cột trụ cao vút được nâng đỡ bởi những cột hình rắn thần Naga có 5 đầu. Đây là một hình ảnh quen thuộc trong văn hóa Khmer, tượng trưng cho sự bảo vệ và sự soi sáng của Phật pháp. Cột trụ này được sử dụng trong các ngày lễ hội, nơi các phật tử có thể thắp nến cầu nguyện, với ý nghĩa là Phật pháp sẽ soi sáng và giúp mọi người sống theo con đường chân thiện mỹ, hướng đến những điều tốt đẹp trong cuộc sống.\r\nCác Họa Tiết Nghệ Thuật: Không chỉ nổi bật về mặt cấu trúc, chùa Vàm Ray còn thu hút du khách bởi các chi tiết chạm khắc tinh xảo và nghệ thuật. Các hình tượng như Maraprum (vị thánh bốn mặt), Kayno (nữ thần nửa người nửa chim) hay Marakrit (chim thần) được chạm khắc tinh tế trên các cột trụ, mái vòm và cầu thang, tạo nên một không gian đậm chất văn hóa Khmer. Những họa tiết này không chỉ mang tính nghệ thuật cao mà còn có ý nghĩa tôn giáo sâu sắc, thể hiện sự kết nối giữa con người và các lực lượng siêu nhiên.', 'uploads/677d3de93a186.jpg', 'Chùa Vàm Ray là một di tích văn hóa vô cùng quan trọng của cộng đồng Khmer tại Việt Nam. Chùa không chỉ là nơi sinh hoạt tôn giáo, mà còn là trung tâm văn hóa, giáo dục. Các thế hệ người Khmer đã học hỏi và truyền lại những giá trị văn hóa, tôn giáo qua những bài giảng, những lớp học Phật pháp, và các nghi lễ truyền thống.\r\nNgôi chùa cũng là nơi tổ chức các lễ hội lớn của cộng đồng Khmer, nơi diễn ra các hoạt động văn hóa như múa, hát, trò chơi dân gian, giúp bảo tồn và phát huy những giá trị văn hóa dân tộc. Các lễ hội này thu hút rất đông phật tử và du khách, không chỉ trong nước mà còn từ các quốc gia khác, đặc biệt là Campuchia.', 'uploads/677d3de93a3fa.jpg', 'Các lễ hội của đồng bào Khmer không chỉ là dịp tôn vinh truyền thống tín ngưỡng mà còn thể hiện sự gắn bó sâu sắc với đạo Phật và cộng đồng. Ba lễ hội tiêu biểu là Tết Chol Chnam Thmay, Lễ hội Sen Đôn Ta, và Lễ hội Dâng Y Kathinat. Tết Chol Chnam Thmay, tổ chức vào giữa tháng 4 dương lịch, là dịp đón năm mới với các hoạt động cúng dường, tụng kinh và vui chơi dân gian, mang ý nghĩa cầu mong một năm an lành. Lễ hội Sen Đôn Ta, kéo dài 15 ngày vào tháng 10 âm lịch, là dịp tưởng nhớ tổ tiên và thể hiện lòng hiếu thảo, với các nghi thức cúng dường và các hoạt động cộng đồng. Lễ hội Dâng Y Kathinat, diễn ra sau ba tháng an cư của các sư, là thời gian để người dân tặng phẩm vật và áo cà sa cho các sư, thể hiện lòng kính trọng và công đức. Các lễ hội này không chỉ giữ gìn bản sắc văn hóa mà còn thắt chặt tình đoàn kết trong cộng đồng Khmer.', 'https://www.youtube.com/watch?v=x1X9tZHq4No'),
(6, 6, 'Chùa Pisesaram là một trong những ngôi chùa cổ kính và nổi bật nhất tại tỉnh Trà Vinh, miền Tây Nam Bộ. Với hơn 500 năm lịch sử, ngôi chùa này không chỉ là nơi thờ Phật mà còn là một biểu tượng văn hóa của cộng đồng người Khmer ở khu vực này. Nằm tại xã Bình Phú, huyện Càng Long, chùa Pisesaram được biết đến với vẻ đẹp nguy nga, tráng lệ mang đậm phong cách kiến trúc Khmer truyền thống. Chùa không chỉ thu hút những tín đồ Phật giáo mà còn là điểm đến du lịch tâm linh nổi bật, nơi du khách có thể khám phá sự huyền bí và thiêng liêng của đất Phật. Ngôi chùa cổ này là minh chứng cho sự trường tồn của văn hóa Khmer ở Trà Vinh, đồng thời phản ánh sự hòa nhập văn hóa đặc sắc của khu vực Đông Nam Á.', 'uploads/677d3f34c467c.jpg', 'Chùa Pisesaram được xây dựng vào năm 1500, tức là đã tồn tại hơn 500 năm. Đây là một trong những ngôi chùa cổ nhất ở Trà Vinh, cũng là nơi sinh hoạt tín ngưỡng và văn hóa của cộng đồng người Khmer tại địa phương. Lịch sử của chùa Pisesaram gắn liền với sự phát triển của đạo Phật tại miền Tây Nam Bộ, đặc biệt là Phật giáo Nam Tông.\r\nTrong suốt lịch sử tồn tại, chùa đã trải qua nhiều thăng trầm. Chùa từng bị hư hỏng nghiêm trọng do thiên tai và các yếu tố tự nhiên, nhưng qua các lần trùng tu, đặc biệt là vào năm 2009, chùa đã được phục dựng lại với vẻ đẹp mới, lộng lẫy và vẫn giữ được các giá trị truyền thống. Việc trùng tu đã giúp bảo tồn không chỉ vẻ đẹp kiến trúc mà còn là những giá trị văn hóa, tín ngưỡng của người Khmer tại Trà Vinh, đưa ngôi chùa này trở thành điểm đến quan trọng cho các tín đồ Phật giáo và du khách.', 'uploads/677d3f52770cb.jpg', 'Chùa Pisesaram mang đậm phong cách kiến trúc Khmer truyền thống nhưng cũng có sự giao thoa với ảnh hưởng của các nền Phật giáo trong khu vực Đông Nam Á như Thái Lan, Myanmar. Kiến trúc của chùa được thiết kế theo hình thức tháp vươn cao với nhiều tầng, cùng mái vòm cong vút đặc trưng. Chùa có một không gian rộng lớn, được chia thành các khu vực chính như chánh điện, các khu thờ cúng và tu viện.\r\nMái Chùa và Các Hoa Văn: Mái chùa được phủ bằng ngói vàng rực rỡ, là biểu tượng của sự linh thiêng trong văn hóa Khmer. Các hoa văn và họa tiết chạm khắc trên mái, cột trụ và tường được thực hiện rất tỉ mỉ, mang đậm dấu ấn của nghệ thuật Khmer. Những hình ảnh như rắn thần Naga hay các hình tượng thần thoại Khmer được khắc họa sinh động, phản ánh sự kết hợp giữa tín ngưỡng Phật giáo và các yếu tố thần thoại của văn hóa dân gian.\r\nChánh Điện và Tượng Phật: Chánh điện của chùa Pisesaram là nơi thờ Phật và các vị thần linh, được xây dựng theo hình tháp cao, vươn lên trời. Bên trong chánh điện là những tượng Phật lớn, được chế tác từ đá quý và phủ lớp vàng, mang đến sự linh thiêng và uy nghiêm cho không gian thờ cúng.\r\nCổng Chùa: Cổng chùa Pisesaram là một điểm nhấn ấn tượng, được xây dựng theo kiểu tháp nhọn, với những chi tiết trang trí công phu. Cổng không chỉ là cửa vào chùa mà còn là một tác phẩm nghệ thuật, tạo nên một không gian linh thiêng, trang nghiêm từ khi du khách bước vào.\r\nHành Lang và Các Khu Vực Phụ: Chùa có nhiều hành lang bao quanh khu chính điện, với những chi tiết trang trí tinh xảo. Các khu vực phụ của chùa như khu thờ cúng, tu viện và các tượng đài được bố trí hợp lý, tạo nên một không gian yên bình và thanh tịnh cho khách hành hương.', 'uploads/677d3f34c4ae6.jpg', 'Chùa Pisesaram không chỉ là một di tích tôn giáo mà còn là một di sản văn hóa của cộng đồng người Khmer ở Trà Vinh. Với hơn 500 năm tồn tại, chùa đã chứng kiến sự phát triển của văn hóa, tín ngưỡng Khmer tại miền Tây Nam Bộ. Chùa cũng là một trong những biểu tượng đặc sắc của Phật giáo Nam Tông trong khu vực, phản ánh sự giao thoa văn hóa giữa các quốc gia Đông Nam Á.\r\nChùa Pisesaram được coi là một di tích lịch sử quan trọng, không chỉ với người dân địa phương mà còn với các thế hệ sau. Cùng với những ngôi chùa khác ở Trà Vinh, chùa Pisesaram là nơi bảo tồn, phát huy các giá trị văn hóa, tín ngưỡng Khmer. Các hoạt động lễ hội Phật giáo, đặc biệt là các nghi lễ truyền thống như Lễ dâng y, Chôl Chnăm Thmây, hay các nghi thức lễ Phật, đều diễn ra tại đây, thu hút đông đảo các tín đồ đến tham gia.\r\nNgoài giá trị tôn giáo, chùa Pisesaram còn là một điểm đến du lịch nổi tiếng, nơi khách tham quan có thể tìm hiểu về lịch sử, văn hóa và kiến trúc Khmer. Những du khách đến thăm chùa không chỉ được chiêm bái, cầu an mà còn có thể tham quan, khám phá những giá trị nghệ thuật độc đáo của công trình, cũng như tìm hiểu sâu về đời sống tâm linh của người Khmer tại Trà Vinh.', 'uploads/677d3f34c4d28.jpg', 'Các lễ hội của đồng bào Khmer không chỉ là dịp tôn vinh truyền thống tín ngưỡng mà còn thể hiện sự gắn bó sâu sắc với đạo Phật và cộng đồng. Ba lễ hội tiêu biểu là Tết Chol Chnam Thmay, Lễ hội Sen Đôn Ta, và Lễ hội Dâng Y Kathinat. Tết Chol Chnam Thmay, tổ chức vào giữa tháng 4 dương lịch, là dịp đón năm mới với các hoạt động cúng dường, tụng kinh và vui chơi dân gian, mang ý nghĩa cầu mong một năm an lành. Lễ hội Sen Đôn Ta, kéo dài 15 ngày vào tháng 10 âm lịch, là dịp tưởng nhớ tổ tiên và thể hiện lòng hiếu thảo, với các nghi thức cúng dường và các hoạt động cộng đồng. Lễ hội Dâng Y Kathinat, diễn ra sau ba tháng an cư của các sư, là thời gian để người dân tặng phẩm vật và áo cà sa cho các sư, thể hiện lòng kính trọng và công đức. Các lễ hội này không chỉ giữ gìn bản sắc văn hóa mà còn thắt chặt tình đoàn kết trong cộng đồng Khmer.', 'https://www.youtube.com/watch?v=huXFF3i9cTs');

-- --------------------------------------------------------

--
-- Table structure for table `dschua`
--

CREATE TABLE `dschua` (
  `id` int(11) NOT NULL,
  `ten_chua` varchar(255) NOT NULL,
  `dia_chi` text DEFAULT NULL,
  `dien_thoai` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT 1,
  `ngay_them` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dschua`
--

INSERT INTO `dschua` (`id`, `ten_chua`, `dia_chi`, `dien_thoai`, `email`, `hinh_anh`, `trang_thai`, `ngay_them`) VALUES
(1, 'Chùa Âng', 'xã Lương Hoà, huyện Châu Thành, TP. Trà Vinh', '02943851123', 'chuaang@gmail.com', 'uploads/677d37f7bad10.jpg', 1, '2024-12-25 14:15:50'),
(2, 'Chùa Hang', 'TT. Châu Thành, huyện Châu Thành, tỉnh Trà Vinh', '02943855456', 'chuahang@gmail.com', 'uploads/677d3af2aad90.jpg', 1, '2024-12-25 14:27:04'),
(3, 'Chùa Ông Mẹt', '50/1 Lê Lợi, Phường 4, TP. Trà Vinh', '02943853789', 'chuaongmet@gmail.com', 'uploads/677d3bcf6dc9d.jpg', 1, '2024-12-25 14:30:11'),
(4, 'Chùa Samrong Ek', 'Phường 8, TP. Trà Vinh', '02943854321', 'chuasamrongek@gmail.com', 'uploads/677d4020c898e.jpg', 1, '2024-12-25 14:32:48'),
(5, 'Chùa Vàm Ray', 'ấp Vàm Ray, huyện Trà Cú, tỉnh Trà Vinh', '02943855654', 'chuavamray@gmail.com', 'uploads/677d3da213970.jpg', 1, '2024-12-25 14:36:22'),
(6, 'Chùa Pisesaram', 'xã Bình Phú, huyện Càng Long, tỉnh Trà Vinh', '02943856987', 'chuapisesaram@gmail.com', 'uploads/677d3ea574cf8.jpg', 1, '2024-12-25 14:37:38');

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
(11, 'Hào', '$2y$10$D4V/sg.8RDNWg6BGQTfvhumdbcAwsVKxlpOjfZY9ruNTX7/zDqlxi', 'Lâm Nhật Hào', 'Hao@gmail.com', 'user', '2024-11-10 20:58:32', '2025-01-07 10:10:30', 'uploads/avatars/676ebf6ccc86d.jpg', 0),
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
(64, 'Lễ hội Ok Om Bok', 'Lễ hội Ok Om Bok có ý nghĩa tôn vinh Mặt Trăng và cầu nguyện cho mùa màng bội thu, cũng như sự thịnh vượng của cộng đồng. Trong văn hóa người Khmer, Mặt Trăng được coi là hiện thân của thần linh, có khả năng mang lại những mùa vụ bội thu và giúp con người gặp nhiều may mắn. Lễ hội này không chỉ thể hiện lòng biết ơn đối với thiên nhiên mà còn là dịp để người Khmer thể hiện sự gắn kết cộng đồng, đồng thời là một dịp vui chơi, giải trí sau những tháng ngày lao động vất vả.', 'Lễ hội Ok Om Bok được tổ chức vào ngày rằm tháng 10 âm lịch hàng năm. Đây là thời điểm mà người Khmer tin rằng Mặt Trăng sẽ sáng nhất trong năm, đánh dấu sự kết thúc của mùa gặt lúa và chuẩn bị bước vào mùa trồng trọt mới. Thời gian này cũng trùng với lễ ', 'Lễ cúng Mặt Trăng: Vào ngày chính của lễ hội, người dân Khmer sẽ thực hiện nghi thức cúng Mặt Trăng, cầu mong sự phú quý, mùa màng tốt tươi và an lành. Họ sẽ chuẩn bị các lễ vật gồm trái cây, hoa và nến để dâng lên Mặt Trăng, thể hiện lòng biết ơn và cầu xin sự phù hộ.\r\nLễ dâng đèn trời: Đây là một nghi thức đặc trưng trong lễ hội Ok Om Bok, trong đó người dân sẽ thả đèn trời (hay còn gọi là đèn lồng) lên bầu trời đêm. Đèn lồng được làm từ giấy hoặc tre, có thể chứa đèn cầy hoặc ngọn lửa nhỏ. Khi đèn lồng bay lên, người dân tin rằng những ước nguyện và mong muốn của họ sẽ được gửi đến Mặt Trăng và các vị thần, giúp cho cuộc sống của họ được bình an và mùa màng bội thu.\r\nCác trò chơi dân gian: Trong suốt lễ hội, người dân sẽ tham gia các trò chơi truyền thống, như đua thuyền, kéo co, đánh đu, đánh cồng chiêng và múa lân. Những trò chơi này không chỉ tạo không khí vui tươi, sôi động mà còn là cách thể hiện tinh thần đoàn kết và sự gắn bó giữa các thành viên trong cộng đồng.\r\nLễ hội đường phố: Các cuộc diễu hành, múa lân và các hoạt động văn hóa nghệ thuật đường phố cũng thường xuyên được tổ chức trong khuôn khổ lễ hội. Mọi người sẽ mặc trang phục truyền thống Khmer, tham gia vào các hoạt động như múa sạp, nhảy múa theo nhạc dân tộc, tạo nên không khí lễ hội rất sôi động.', 'Lễ hội Ok Om Bok cũng không thể thiếu các món ăn đặc trưng của người Khmer trong dịp lễ. Một số món ăn truyền thống thường xuất hiện trong dịp này bao gồm:\r\nCơm dừa (Cơm lam): Đây là món ăn đặc trưng trong lễ hội, được làm từ gạo nếp, dừa tươi, và thường được nấu trong ống tre hoặc ống lá chuối. Món ăn này biểu trưng cho sự no đủ và đoàn kết.\r\nBánh Pía (Bánh dẻo nhân chuối): Bánh Pía là loại bánh truyền thống của người Khmer, được làm từ bột gạo nếp, đường, dừa, chuối chín và đậu xanh. Bánh này tượng trưng cho sự ngọt ngào và ấm cúng của mùa lễ hội.\r\nXôi nếp (Xôi ngọt): Món xôi nếp được chế biến từ gạo nếp, nước cốt dừa và đậu phộng, ăn kèm với chuối hoặc các loại trái cây tươi. Đây là món ăn phổ biến trong dịp lễ Ok Om Bok.\r\nBánh trôi (Bánh trôi nước): Bánh trôi có nhân đậu xanh hoặc đường thốt nốt, được nấu trong nước đường, tạo thành một món tráng miệng ngọt ngào và thơm ngon.\r\nBánh xèo Khmer: Bánh xèo Khmer có phần vỏ giòn, nhân thường là tôm, thịt và giá đỗ, được chiên vàng. Đây là món ăn thể hiện sự cầu kỳ và giàu có của người Khmer trong dịp lễ hội.', 'Trang phục phù hợp: Khi tham gia lễ hội Ok Om Bok, du khách nên mặc trang phục nhẹ nhàng, thoải mái và nên mang những trang phục có tính tôn trọng văn hóa địa phương, chẳng hạn như áo dài hoặc trang phục truyền thống của người Khmer.\r\nCần tôn trọng tín ngưỡng và nghi thức:\r\nLễ hội Ok Om Bok mang ý nghĩa tâm linh sâu sắc, vì vậy du khách cần tôn trọng các nghi thức cúng bái và không làm phiền đến người dân khi họ đang thực hiện các nghi lễ.\r\nHạn chế ồn ào: Dù lễ hội có phần vui nhộn và sôi động, nhưng du khách cũng cần lưu ý không làm ồn ào trong khu vực chánh điện hoặc nơi tổ chức các nghi lễ tôn giáo.\r\nChú ý đến an toàn trong các trò chơi: Các trò chơi dân gian trong lễ hội như đua thuyền, kéo co... có thể rất thú vị nhưng cũng tiềm ẩn một số nguy cơ. Du khách nên tham gia trong tinh thần vui tươi và chú ý đến an toàn cá nhân.', 1, '2025-01-07 09:20:53', '2025-01-07 15:01:17', 'uploads/events/677d41bdb01d5.jpg'),
(65, 'Lễ hội Chôl Chnăm Thmây', 'Lễ hội Chôl Chnăm Thmây đánh dấu sự kết thúc của một năm cũ và sự bắt đầu của một năm mới, với hy vọng một năm mới đầy hạnh phúc, thịnh vượng, mùa màng bội thu và an lành cho cộng đồng. Tết Chôl Chnăm Thmây thể hiện lòng biết ơn đối với tổ tiên, trời đất và các thần linh đã che chở cho người dân trong suốt một năm qua. Đây là dịp để mọi người sum vầy bên gia đình, bè bạn và tham gia các hoạt động vui chơi, giải trí.', 'Lễ hội Chôl Chnăm Thmây được tổ chức vào ngày 1 tháng 4 âm lịch, theo lịch của người Khmer. Thời gian này trùng với dịp giao thừa của năm cũ và bắt đầu năm mới, khi mùa gặt đã kết thúc và mùa vụ mới chuẩn bị bắt đầu. Tết Chôl Chnăm Thmây kéo dài từ 3 đến ', 'Lễ Tắm Phật:\r\nVào ngày đầu năm, người Khmer thực hiện nghi thức tắm Phật. Họ mang tượng Phật ra ngoài trời và dùng nước thơm để tắm, tượng trưng cho việc gột rửa mọi xui xẻo của năm cũ, cầu mong sự bình an và may mắn trong năm mới. Lễ tắm Phật là một nghi thức vô cùng trang trọng, thể hiện lòng tôn kính đối với Đức Phật.\r\nLễ Cúng Gia Tiên:\r\nVào dịp Tết, mỗi gia đình Khmer tổ chức lễ cúng gia tiên. Người dân chuẩn bị các lễ vật như hoa quả, bánh, trái cây và thức ăn dâng lên bàn thờ tổ tiên để bày tỏ lòng thành kính, cầu mong các vị tổ tiên phù hộ cho gia đình có sức khỏe, an khang thịnh vượng trong năm mới.\r\nTết Mừng Lúa Mới:\r\nLễ hội Chôl Chnăm Thmây cũng gắn liền với việc mừng lúa mới. Đây là dịp để tạ ơn trời đất, cầu cho mùa màng bội thu trong năm mới. Người dân Khmer tin rằng mùa lúa mới sẽ mang đến may mắn và thịnh vượng, giúp cộng đồng vượt qua khó khăn trong năm mới.\r\nCác trò chơi dân gian:\r\nTrong dịp Tết, người Khmer tổ chức nhiều trò chơi dân gian vui nhộn, như đua thuyền, đánh đu, đá cầu, kéo co, múa lân, và các trò chơi khác. Những trò chơi này không chỉ tạo không khí vui tươi mà còn thể hiện tinh thần đoàn kết, hợp tác giữa các cộng đồng.\r\nLễ hội đường phố:\r\nTại các ngôi chùa Khmer, lễ hội thường có các cuộc diễu hành, múa lân và các hoạt động văn hóa nghệ thuật. Người dân mặc trang phục truyền thống, tham gia diễu hành quanh chùa và trên các con phố. Đây là dịp để thể hiện sự đoàn kết và vui vẻ trong cộng đồng.', 'Lễ hội Chôl Chnăm Thmây cũng là dịp để thưởng thức những món ăn đặc trưng của người Khmer. Một số món ăn không thể thiếu trong dịp này bao gồm:\r\nBánh Tét (Bánh Chưng):\r\nNgười Khmer ăn bánh tét (hoặc bánh chưng) trong dịp Tết để thể hiện sự biết ơn với tổ tiên và mong muốn một năm mới no đủ, hạnh phúc. Bánh tét được làm từ gạo nếp, đậu xanh, thịt heo hoặc cá, bọc trong lá chuối và luộc lên.\r\nBánh Pía:\r\nBánh Pía là món ăn nổi tiếng của người Khmer trong dịp lễ, làm từ bột nếp, đậu xanh, dừa và chuối, có vị ngọt thanh. Bánh này được chế biến công phu, mang đậm hương vị quê hương, và thể hiện sự phồn vinh, thịnh vượng trong năm mới.\r\nXôi nếp:\r\nMón xôi nếp là món ăn không thể thiếu trong ngày Tết. Xôi được chế biến từ gạo nếp, nước cốt dừa, ăn kèm với chuối hoặc các loại trái cây khác. Đây là món ăn tượng trưng cho sự đoàn kết và sự giàu có.\r\nGà Luộc:\r\nNgười Khmer thường luộc gà trong dịp Tết, coi đây là món ăn mang lại may mắn và tài lộc cho gia đình. Gà luộc được chế biến đơn giản nhưng mang lại ý nghĩa sâu sắc về sự bình an và thịnh vượng.\r\nMón ăn ngọt:\r\nCác món ăn ngọt như bánh trôi, bánh ngọt nhân đậu xanh cũng thường xuất hiện trong dịp Tết. Đây là món ăn thể hiện sự ngọt ngào, ấm cúng của gia đình trong dịp lễ hội.', 'Tôn trọng văn hóa và nghi thức tôn giáo:\r\nChôl Chnăm Thmây là lễ hội tôn vinh tôn giáo và tổ tiên, vì vậy khi tham gia lễ hội, du khách cần tôn trọng các nghi thức tôn giáo và văn hóa của người Khmer. Tránh làm ồn ào hay có những hành động thiếu tôn trọng trong các khu vực thờ tự.\r\nTrang phục lịch sự:\r\nDu khách nên mặc trang phục lịch sự, đặc biệt là khi tham gia vào các hoạt động tôn giáo như lễ tắm Phật hoặc lễ cúng gia tiên. Trang phục nên kín đáo và không gây sự chú ý.\r\nTham gia các trò chơi dân gian:\r\nCác trò chơi dân gian trong dịp lễ hội thường rất vui nhộn và thú vị, nhưng du khách cần chú ý đến an toàn khi tham gia, đặc biệt là các trò chơi thể thao ngoài trời.\r\nChú ý an toàn thực phẩm:\r\nĐặc biệt trong các hoạt động ăn uống và thưởng thức các món ăn truyền thống, du khách nên lựa chọn các cơ sở đảm bảo vệ sinh an toàn thực phẩm để tránh những vấn đề về sức khỏe.', 1, '2025-01-07 09:24:04', '2025-01-07 15:02:16', 'uploads/events/677d41f852e41.jpg'),
(66, 'Lễ hội Sen Đôn Ta', 'Lễ hội Sen Đôn Ta là dịp để người Khmer thể hiện lòng hiếu thảo và tưởng nhớ đến tổ tiên, ông bà đã khuất. Đây là một lễ hội tâm linh gắn liền với tín ngưỡng thờ cúng tổ tiên, giúp các thế hệ trẻ thể hiện lòng kính trọng đối với các bậc tiền nhân, đồng thời cầu nguyện cho họ được yên nghỉ nơi cõi vĩnh hằng và cho gia đình luôn khỏe mạnh, hạnh phúc. Lễ hội cũng phản ánh truyền thống tôn trọng sự sống và đạo lý \"uống nước nhớ nguồn\" của cộng đồng Khmer.', 'Lễ hội Sen Đôn Ta diễn ra vào tháng 9 âm lịch, kéo dài từ ngày 29 đến ngày 1 tháng 10 âm lịch. Đây là thời điểm cuối mùa mưa, khi mà người Khmer tin rằng linh hồn của tổ tiên sẽ trở về đoàn tụ cùng gia đình. Ngày chính của lễ hội thường là ngày 29 và 30 t', 'Lễ Cúng Ông Bà:\r\nTrong lễ hội Sen Đôn Ta, người Khmer thường cúng dường cho ông bà tổ tiên bằng những lễ vật như cơm, trái cây, bánh, rượu, và các món ăn đặc trưng của người Khmer. Nghi thức cúng tổ tiên được tổ chức tại bàn thờ gia đình hoặc tại các ngôi chùa, với mong muốn các linh hồn tổ tiên được thanh thản, hưởng phúc lộc.\r\n\r\nLễ Cúng Chư Tăng:\r\nNgười Khmer cũng tổ chức lễ cúng chư tăng vào dịp này để cầu cho mọi người trong gia đình được an lành, phúc đức. Chư tăng sẽ được mời đến các gia đình hoặc các ngôi chùa để tụng kinh cầu nguyện, giúp người dân được bảo vệ khỏi những tai ương, bệnh tật.\r\n\r\nThả Đèn Sen:\r\nMột trong những nghi thức đặc biệt trong lễ hội Sen Đôn Ta là thả đèn sen. Người dân sẽ làm những chiếc đèn sen nhỏ từ lá sen, rồi thắp sáng và thả xuống sông hoặc hồ. Đèn sen không chỉ là biểu tượng của sự thắp sáng niềm hy vọng mà còn thể hiện ước nguyện về một cuộc sống bình an, hạnh phúc và may mắn trong năm mới.\r\n\r\nLễ Thả Cỏ và Thức Ăn Cho Linh Hồn:\r\nTrong suốt lễ hội, người Khmer thường thả cỏ, thức ăn và các lễ vật xuống các dòng sông, ao hồ như một cách để \"mời\" linh hồn tổ tiên, ông bà về ăn uống và hưởng thụ những món ăn mà con cháu đã dâng cúng. Điều này thể hiện sự tưởng nhớ và lòng thành kính đối với các vị đã khuất.\r\n\r\nThăm Mộ Tổ Tiên:\r\nMột phong tục quan trọng trong dịp Sen Đôn Ta là thăm mộ tổ tiên. Các gia đình sẽ cùng nhau đến nghĩa trang, dọn dẹp mộ phần và thực hiện lễ cúng tổ tiên tại đó. Đây là cách để gia đình thể hiện lòng hiếu thảo và tưởng nhớ đến những người đã khuất.', 'Lễ hội Sen Đôn Ta không thể thiếu những món ăn đặc trưng của người Khmer, mỗi món ăn đều mang trong mình ý nghĩa tâm linh, thể hiện lòng thành kính và hiếu thảo đối với tổ tiên.\r\n\r\nBánh Pía (Bánh Trái):\r\nĐây là món ăn nổi tiếng trong dịp lễ hội, bánh làm từ bột nếp, nhân đậu xanh và dừa. Người Khmer tin rằng bánh Pía sẽ đem lại sự bình an và may mắn, và là một trong những món cúng dâng tổ tiên trong lễ hội Sen Đôn Ta.\r\n\r\nXôi nếp:\r\nXôi nếp là món ăn phổ biến trong dịp lễ Tết của người Khmer, được chế biến từ gạo nếp, nước cốt dừa và ăn kèm với các loại trái cây. Món xôi này tượng trưng cho sự đầy đủ, thịnh vượng và đoàn kết trong gia đình.\r\n\r\nMón Cà Ri Khmer:\r\nMón cà ri với vị đậm đà, mùi thơm đặc trưng là món ăn không thể thiếu trong lễ hội Sen Đôn Ta. Cà ri Khmer được chế biến từ thịt gà, thịt heo hoặc bò, cùng với các loại gia vị như sả, ớt, và dừa, tạo nên hương vị đặc biệt.\r\n\r\nTrái Cây Tươi:\r\nTrái cây tươi như chuối, dưa hấu, xoài, mãng cầu, nhãn… là những món ăn được dâng cúng lên tổ tiên. Món ăn này thể hiện sự thanh đạm và mộc mạc của người Khmer, với mong muốn cuộc sống gia đình luôn tươi mới, ngọt ngào như trái cây.\r\n\r\nBánh Hấp:\r\nBánh hấp cũng là món ăn truyền thống trong lễ hội. Món này được làm từ bột gạo và các nguyên liệu tự nhiên, mang vị ngọt thanh, ăn kèm với nước cốt dừa.', 'Tôn trọng nghi lễ tôn giáo:\r\nLễ hội Sen Đôn Ta gắn liền với tín ngưỡng và tôn giáo của người Khmer. Du khách tham gia lễ hội nên tôn trọng các nghi thức, nhất là trong các hoạt động cúng bái tại chùa hoặc nhà thờ tổ tiên.\r\n\r\nTrang phục lịch sự:\r\nKhi tham gia lễ hội, du khách nên ăn mặc trang nghiêm, kín đáo và lịch sự, đặc biệt là khi tham gia vào các nghi thức cúng lễ tại các ngôi chùa hoặc nghĩa trang.\r\n\r\nTham gia vào các hoạt động cộng đồng:\r\nSen Đôn Ta là dịp để mọi người sum vầy bên gia đình và cộng đồng, vì vậy du khách có thể tham gia vào các hoạt động như thả đèn sen, lễ cúng tại chùa và tham gia các trò chơi dân gian.\r\n\r\nChú ý bảo vệ môi trường:\r\nVì có nhiều nghi thức như thả đèn sen, thả hoa cỏ, du khách cần lưu ý bảo vệ môi trường, tránh vứt rác bừa bãi, giữ gìn sự sạch sẽ trong và ngoài khu vực lễ hội.', 1, '2025-01-07 09:25:47', '2025-01-07 15:03:23', 'uploads/events/677d423bcdad8.png');

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
-- Indexes for table `chitiet_chua`
--
ALTER TABLE `chitiet_chua`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chua_id` (`chua_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=420;

--
-- AUTO_INCREMENT for table `chitiet_chua`
--
ALTER TABLE `chitiet_chua`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `dschua`
--
ALTER TABLE `dschua`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD CONSTRAINT `binh_luan_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`),
  ADD CONSTRAINT `binh_luan_ibfk_2` FOREIGN KEY (`id_binh_luan_goc`) REFERENCES `binh_luan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chitiet_chua`
--
ALTER TABLE `chitiet_chua`
  ADD CONSTRAINT `chitiet_chua_ibfk_1` FOREIGN KEY (`chua_id`) REFERENCES `dschua` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
