<?php

/**
 * @file
 * Detail bodies for the news articles, keyed by article title.
 *
 * Split out of seed_news.php because the copy dwarfs the card metadata.
 * Every entry may carry: quick, sections, compare, faqs, products.
 * `products` holds bare product aliases — the API resolves them to live cards.
 */

return [

  'Cách chọn khóa theo độ dày cửa' => [
    'quick' => 'Đo độ dày cánh tại mép cửa, không đo tại khuôn bao. Dưới 40mm dùng thân khóa tiêu chuẩn, 40–55mm dùng thân dài, trên 55mm phải đặt thân chuyên dụng. Sai lệch 3mm đã đủ để mặt khóa kênh và cánh không đóng khít.',
    'sections' => [
      [
        'id' => 'do-dung-cach',
        'title' => '1. Đo đúng chỗ và đúng cách',
        'paragraphs' => [
          'Độ dày cánh là thông số đầu tiên quyết định bộ khóa có lắp được hay không. Rất nhiều trường hợp đặt nhầm size vì đo tại khuôn bao hoặc đo tại nẹp chỉ — hai vị trí này luôn dày hơn cánh thật vài milimet.',
          'Hãy đo tại mép cánh, ở phần gỗ đặc nơi sẽ khoét ổ khóa. Nếu cánh có ốp nẹp trang trí, đo phần cốt gỗ chứ không tính lớp nẹp, vì thân khóa nằm trong cốt.',
        ],
        'list' => [
          'Dùng thước kẹp hoặc thước lá, đo ở ba vị trí trên cùng một cánh',
          'Lấy số lớn nhất làm chuẩn khi đặt hàng',
          'Đo thêm chiều rộng đố cửa nếu cánh hẹp dưới 100mm',
          'Ghi rõ độ dày trong yêu cầu báo giá',
        ],
        'note' => 'Cửa gỗ tự nhiên co ngót theo mùa. Nếu đo vào mùa hanh khô, cộng thêm 1mm dung sai để cánh không bị chật khi gỗ nở lại.',
      ],
      [
        'id' => 'quy-doi-size',
        'title' => '2. Quy đổi độ dày sang thân khóa',
        'paragraphs' => [
          'Thân khóa có ba nhóm phổ biến: tiêu chuẩn cho cánh 35–45mm, thân dài cho cánh 45–55mm và thân chuyên dụng cho cánh trên 55mm hoặc cửa thép chống cháy. Chọn thân ngắn hơn cánh thì trục vuông không đủ chiều, tay gạt sẽ rơ; chọn thân dài hơn thì mặt khóa vênh khỏi bề mặt gỗ.',
          'Với khóa điện tử, phần lớn model Keybolts đi kèm trục vuông rời cho phép cắt ngắn theo độ dày thực tế — nhưng chỉ trong khoảng dung sai của model đó, không phải cắt tùy ý.',
        ],
        'list' => [],
      ],
      [
        'id' => 'backset',
        'title' => '3. Khoảng cách tâm (backset) cũng phải khớp',
        'paragraphs' => [
          'Backset là khoảng cách từ mép cánh đến tâm lỗ tay nắm, thường là 60mm hoặc 85mm với cửa dân dụng. Độ dày đúng nhưng backset sai thì vẫn phải khoét lại cánh. Khi thay khóa cũ, đo luôn backset của lỗ khoét hiện có và gửi kèm ảnh.',
        ],
        'list' => [],
        'note' => 'Lỗ khoét sai không hoàn nguyên được trên cửa gỗ. Xác nhận cả độ dày lẫn backset với kỹ thuật trước khi khoan.',
      ],
      [
        'id' => 'giua-hai-co',
        'title' => '4. Khi cánh nằm giữa hai cỡ',
        'paragraphs' => [
          'Cánh 44–46mm rơi đúng ranh giới giữa thân tiêu chuẩn và thân dài. Trong trường hợp này ưu tiên thân dài rồi bù bằng long đen hoặc đệm mặt khóa — thừa vài milimet xử lý được, thiếu thì không.',
          'Nếu cánh nằm ngoài khoảng 35–70mm, gọi hotline trước khi đặt. Keybolts có thân ngắn và thân dài đặt riêng cho các trường hợp đặc biệt như cửa kho, cửa cách âm phòng thu hay cửa kính thủy lực.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [
      ['door' => 'Gỗ tự nhiên', 'thickness' => '45–55 mm', 'lock' => 'Thân dài', 'backup' => 'Chìa cơ'],
      ['door' => 'Gỗ công nghiệp', 'thickness' => '35–45 mm', 'lock' => 'Thân tiêu chuẩn', 'backup' => 'Mã số'],
      ['door' => 'Cửa thép chống cháy', 'thickness' => '50–70 mm', 'lock' => 'Thân chuyên dụng', 'backup' => 'Thẻ từ + chìa'],
      ['door' => 'Cửa kính thủy lực', 'thickness' => '10–12 mm', 'lock' => 'Khóa kẹp kính', 'backup' => 'Chìa cơ'],
    ],
    'faqs' => [
      ['question' => 'Không có thước kẹp thì đo bằng gì?', 'answer' => 'Thước lá kim loại là đủ chính xác. Áp thước vuông góc với mép cánh và đọc số ở ngang tầm mắt để tránh sai số góc nhìn. Điện thoại có ứng dụng thước không đủ tin cậy cho việc này.'],
      ['question' => 'Cửa đã lắp khóa cũ thì đo thế nào?', 'answer' => 'Tháo mặt khóa cũ ra, đo độ dày cánh tại vị trí lỗ khoét, đo luôn chiều cao và chiều rộng lỗ. Gửi ảnh lỗ khoét kèm ba số đo để kỹ thuật xác nhận model thay được mà không phải mở rộng lỗ.'],
      ['question' => 'Đặt sai size thì đổi được không?', 'answer' => 'Đổi được nếu sản phẩm còn nguyên seal và chưa khoan lắp. Sau khi đã khoét cánh và bắt vít, sản phẩm không còn thuộc diện đổi trả — đây là lý do nên xác nhận số đo trước.'],
    ],
    'products' => ['khoa-van-tay-cua-go', 'khoa-tay-gat-cua-go', 'khoa-dong-trung'],
  ],

  'Khóa thẻ từ và khóa vân tay khác nhau thế nào?' => [
    'quick' => 'Khóa thẻ từ mạnh ở khâu quản lý nhiều người dùng luân phiên — khách sạn, văn phòng, căn hộ cho thuê. Khóa vân tay tiện hơn cho gia đình vì không phải mang theo gì. Nhà ở có người lớn tuổi nên chọn bộ có cả hai.',
    'sections' => [
      [
        'id' => 'nguyen-ly',
        'title' => '1. Hai nguyên lý nhận dạng khác nhau',
        'paragraphs' => [
          'Khóa thẻ từ đọc mã trên thẻ RFID và đối chiếu với danh sách được cấp quyền. Thẻ là vật mang, có thể phát, thu hồi, làm lại. Khóa vân tay so khớp đặc trưng vân tay đã đăng ký — không có vật mang nào để mất, nhưng cũng không phát cho người khác được.',
          'Về độ an toàn, cả hai đều đủ cho nhà ở và công trình thương mại. Khác biệt thật nằm ở cách vận hành hằng ngày chứ không phải ở khả năng chống phá.',
        ],
        'list' => [],
      ],
      [
        'id' => 'quan-ly-nguoi-dung',
        'title' => '2. Quản lý người dùng',
        'paragraphs' => [
          'Đây là điểm phân hóa rõ nhất. Khách sạn 40 phòng thay khách mỗi ngày thì phát thẻ nhanh hơn nhiều so với việc đăng ký vân tay cho từng lượt khách. Ngược lại, gia đình bốn người đăng ký vân tay một lần rồi dùng nhiều năm.',
        ],
        'list' => [
          'Thẻ từ: cấp và thu hồi trong vài giây, hợp với người dùng luân phiên',
          'Vân tay: đăng ký một lần, hợp với nhóm người dùng cố định',
          'Mã số: phương án trung gian, đổi được từ xa mà không cần gặp mặt',
        ],
      ],
      [
        'id' => 'chi-phi-van-hanh',
        'title' => '3. Chi phí vận hành về lâu dài',
        'paragraphs' => [
          'Khóa thẻ từ phát sinh chi phí thẻ thay thế và thẻ thất lạc — nhỏ nhưng đều đặn, đáng kể khi vận hành hàng chục phòng. Khóa vân tay gần như không phát sinh, nhưng cụm cảm biến là linh kiện hao mòn, sau nhiều năm sử dụng cường độ cao có thể cần thay.',
        ],
        'list' => [],
        'note' => 'Cả hai dòng đều dùng pin. Ngân sách vận hành nên tính thêm khoảng hai lần thay pin mỗi năm cho cửa chính có tần suất ra vào cao.',
      ],
      [
        'id' => 'chon-theo-bai-toan',
        'title' => '4. Chọn theo bài toán, không chọn theo công nghệ',
        'paragraphs' => [
          'Nhà ở gia đình: vân tay là chính, mã số dự phòng. Căn hộ cho thuê ngắn hạn: mã số dùng một lần là tiện nhất vì không phải gặp khách để bàn giao. Khách sạn và văn phòng: thẻ từ, tích hợp được với hệ thống quản lý phòng.',
          'Với nhà có người cao tuổi, vân tay đôi khi khó nhận do da tay mỏng và khô — nên chọn model có sẵn thẻ từ để dùng song song thay vì phải thử lại nhiều lần trước cửa.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Mất thẻ từ thì người nhặt được có mở cửa được không?', 'answer' => 'Được, cho tới khi thẻ bị xóa khỏi danh sách. Vì vậy quy trình chuẩn là xóa thẻ mất ngay trên khóa hoặc trên phần mềm quản lý, việc này mất chưa tới một phút.'],
      ['question' => 'Khóa vân tay có nhận được khi tay ướt không?', 'answer' => 'Cảm biến điện dung nhận kém khi tay ướt hoặc dính dầu mỡ. Lau khô đầu ngón trước khi đặt là đủ. Nếu cửa đặt ở vị trí hay ướt tay như cửa bếp hay cửa sân, nên ưu tiên thẻ từ hoặc mã số.'],
      ['question' => 'Một khóa dùng được cả hai không?', 'answer' => 'Có. Phần lớn khóa thông minh Keybolts hỗ trợ đồng thời vân tay, thẻ từ, mã số và chìa cơ. Bạn không phải chọn một — chỉ cần chọn model có đủ phương thức bạn thực sự sẽ dùng.'],
    ],
    'products' => ['khoa-van-tay-cua-go', 'khoa-tu-khach-san', 'khoa-thong-minh-k42'],
  ],

  'Khóa thông minh cho biệt thự cần tính năng gì?' => [
    'quick' => 'Cửa chính biệt thự cần thân khóa chốt kép, chống cạy có báo động, nguồn dự phòng qua cổng sạc và hoàn thiện chịu được nắng mưa. Cửa phụ và cửa phòng có thể dùng model nhẹ hơn — không cần đồng cấp với cửa chính.',
    'sections' => [
      [
        'id' => 'cua-chinh',
        'title' => '1. Cửa chính: ưu tiên phần cơ trước phần điện',
        'paragraphs' => [
          'Cửa chính biệt thự thường là cánh gỗ đặc nặng 40–70kg, mở ra ngoài trời. Yêu cầu đầu tiên là thân khóa chốt kép hoặc đa điểm, vì cánh nặng lâu ngày sẽ xệ và một chốt đơn sẽ bị cấn.',
          'Phần điện tử chỉ là lớp tiện dụng phía trên. Một bộ khóa có mọi tính năng thông minh nhưng thân cơ yếu vẫn là bộ khóa kém cho cửa chính.',
        ],
        'list' => [
          'Thân khóa chốt kép hoặc đa điểm cho cánh trên 40kg',
          'Mặt khóa liền khối, hạn chế khe hở đọng nước',
          'Hoàn thiện PVD hoặc DSF chịu được nắng mưa trực tiếp',
        ],
      ],
      [
        'id' => 'chong-cay',
        'title' => '2. Chống cạy và cảnh báo',
        'paragraphs' => [
          'Tính năng đáng tiền nhất trên khóa biệt thự là cảnh báo cạy phá: khóa phát chuông tại chỗ khi mặt khóa bị tác động, kèm cảnh báo nhập sai mã nhiều lần. Với nhà có sân vườn rộng, chuông tại chỗ thường hiệu quả hơn thông báo từ xa.',
        ],
        'list' => [],
        'note' => 'Nếu nhà đã có hệ thống camera hoặc báo động, hỏi kỹ thuật về model có tiếp điểm khô để đấu chung — tránh hai hệ thống chạy song song mà không liên thông.',
      ],
      [
        'id' => 'nguon-du-phong',
        'title' => '3. Nguồn và phương án dự phòng',
        'paragraphs' => [
          'Biệt thự thường có cửa chính cách xa phòng ở, nên việc hết pin giữa đêm phiền hơn nhiều so với căn hộ. Chọn model có cổng cấp nguồn ngoài và luôn giữ một chìa cơ ở nơi cố định ngoài nhà — hộp thư, nhà xe hoặc gửi hàng xóm.',
        ],
        'list' => [],
      ],
      [
        'id' => 'dong-bo',
        'title' => '4. Đồng bộ hoàn thiện toàn nhà',
        'paragraphs' => [
          'Biệt thự có nhiều cửa: cửa chính, cửa phụ, cửa phòng, cửa ban công. Chọn cùng một hệ hoàn thiện — vàng PVD hoặc rêu DSF — cho khóa, tay nắm, bản lề và chặn cửa. Đây là chi tiết dễ bị bỏ qua nhưng lộ ra rất rõ khi nhà hoàn thiện.',
          'Cửa phòng không cần khóa thông minh. Dùng khóa tay gạt cùng hoàn thiện vừa tiết kiệm vừa gọn về thị giác.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Cửa chính hai cánh thì lắp khóa ở cánh nào?', 'answer' => 'Lắp ở cánh hoạt động (cánh mở thường xuyên), cánh còn lại cố định bằng chốt âm trên dưới hoặc cremone. Khi cần mở rộng cả hai cánh thì rút chốt cánh phụ.'],
      ['question' => 'Khóa ngoài trời có bị hỏng vì mưa không?', 'answer' => 'Model dùng cho cửa ngoài trời có gioăng chống nước ở mặt khóa. Vấn đề thực tế thường là nắng chiếu trực tiếp làm nóng mặt khóa chứ không phải mưa — nếu cửa chính không có mái che, nên báo kỹ thuật để chọn model phù hợp.'],
      ['question' => 'Có cần khóa thông minh cho cửa phụ không?', 'answer' => 'Không bắt buộc. Cửa phụ ra sân hoặc ra bếp thường dùng khóa cơ tay gạt là đủ và bền hơn. Ưu tiên ngân sách cho cửa chính và cửa tầng trệt.'],
    ],
    'products' => ['khoa-biet-thu-keybolts', 'khoa-dong-dai-sanh', 'khoa-thong-minh-t28'],
  ],

  'Cách bảo dưỡng khóa đồng mạ PVD' => [
    'quick' => 'Lau khô bằng vải mềm mỗi tuần, tuyệt đối không dùng hóa chất tẩy hay giẻ nhám. Tra dầu gốc silicon vào ổ khóa và chốt sáu tháng một lần. Lớp PVD rất cứng nhưng vẫn xước nếu chà mạnh, và xước thì không phục hồi được.',
    'sections' => [
      [
        'id' => 've-sinh',
        'title' => '1. Vệ sinh hằng tuần',
        'paragraphs' => [
          'PVD là lớp phủ kim loại bám rất chắc, nhưng thứ làm mờ bề mặt lại là mồ hôi tay và bụi mịn tích tụ chứ không phải oxy hóa. Lau khô bằng vải microfiber là đủ cho 90% trường hợp.',
          'Nếu có vết bám dính, làm ẩm vải bằng nước sạch rồi lau, sau đó lau khô ngay. Không để nước đọng ở khe giữa mặt khóa và cánh cửa.',
        ],
        'list' => [
          'Vải microfiber khô, lau theo một chiều',
          'Nước sạch cho vết bám cứng đầu, lau khô ngay sau đó',
          'Không dùng cồn, nước lau kính, thuốc tẩy hay bột đánh bóng',
          'Không dùng miếng cọ nhám hay bùi nhùi kim loại',
        ],
        'note' => 'Lớp PVD dày chỉ vài micromet. Một lần chà bằng bùi nhùi là đủ để lộ nền đồng bên dưới và vết đó không đánh bóng lại được.',
      ],
      [
        'id' => 'tra-dau',
        'title' => '2. Tra dầu cho phần cơ',
        'paragraphs' => [
          'Sáu tháng một lần, xịt một lượng nhỏ dầu gốc silicon vào lỗ chìa và vào chốt, sau đó xoay chìa và đóng mở cánh vài lần cho dầu tản đều. Lau sạch phần dầu thừa chảy ra mặt khóa.',
          'Không dùng dầu nhớt đặc hay mỡ bò cho ổ khóa: chúng giữ bụi và sau vài tháng sẽ làm ổ nặng hơn trước khi tra.',
        ],
        'list' => [],
      ],
      [
        'id' => 'moi-truong',
        'title' => '3. Chú ý theo môi trường lắp đặt',
        'paragraphs' => [
          'Nhà gần biển hoặc gần khu công nghiệp có hơi muối và hơi axit trong không khí, nên rút ngắn chu kỳ lau xuống mỗi vài ngày với cửa ngoài trời. Nhà trong phố thì lịch hằng tuần là dư.',
          'Với cửa hướng tây chịu nắng gắt, tránh lau khi mặt khóa đang nóng — nước bốc hơi nhanh để lại vệt khoáng trên bề mặt.',
        ],
        'list' => [],
      ],
      [
        'id' => 'lich-bao-duong',
        'title' => '4. Lịch bảo dưỡng gợi ý',
        'paragraphs' => [
          'Hằng tuần lau khô. Sáu tháng tra dầu và siết lại vít mặt khóa. Hằng năm kiểm tra bản lề và độ khít của chốt — phần lớn sự cố khóa nặng tay bắt nguồn từ bản lề xệ chứ không phải từ ổ khóa.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Khóa PVD bị mờ một mảng thì xử lý được không?', 'answer' => 'Nếu mờ do bám bẩn thì lau sạch là hết. Nếu lớp phủ đã bị mài mòn thì không phục hồi tại chỗ được — phải thay mặt khóa. Keybolts có sẵn mặt khóa rời cho các dòng phổ biến.'],
      ['question' => 'Dùng dầu WD-40 cho ổ khóa được không?', 'answer' => 'Không nên dùng làm dầu bôi trơn lâu dài. WD-40 tiêu chuẩn là chất tẩy ẩm, bay hơi nhanh và để lại cặn. Dùng dầu xịt gốc silicon dành cho ổ khóa.'],
      ['question' => 'Bao lâu nên siết lại vít mặt khóa?', 'answer' => 'Sáu tháng một lần, cùng dịp tra dầu. Siết vừa tay — siết quá chặt trên cửa gỗ công nghiệp sẽ làm toét lỗ vít và mặt khóa lỏng nhanh hơn.'],
    ],
    'products' => ['khoa-dong-dai-sanh', 'khoa-dong-trung', 'tay-nam-cua-dong'],
  ],

  'Khóa bị kẹt chìa — xử lý thế nào?' => [
    'quick' => 'Dừng xoay ngay khi thấy nặng — cố xoay là cách nhanh nhất để gãy chìa trong ổ. Kiểm tra bản lề và độ khít cánh trước, vì phần lớn ca kẹt chìa là do cánh xệ chứ không phải do ổ khóa hỏng.',
    'sections' => [
      [
        'id' => 'nguyen-nhan',
        'title' => '1. Bốn nguyên nhân thường gặp',
        'paragraphs' => [
          'Trước khi can thiệp, xác định nguyên nhân — cách xử lý khác nhau hoàn toàn và làm sai sẽ hỏng nặng hơn.',
        ],
        'list' => [
          'Cánh xệ khiến chốt cấn vào lỗ chốt trên khuôn — chìa xoay nặng nhưng vẫn xoay được',
          'Ổ khóa khô dầu hoặc bám bụi — chìa vào ra rít, xoay giật cục',
          'Chìa mòn hoặc chìa sao chép sai — chìa vào được nhưng không xoay',
          'Có dị vật trong ổ — chìa không vào hết chiều sâu',
        ],
      ],
      [
        'id' => 'xu-ly-an-toan',
        'title' => '2. Xử lý an toàn tại chỗ',
        'paragraphs' => [
          'Nếu chìa xoay nặng: nhấc nhẹ cánh cửa lên bằng tay nắm rồi xoay chìa. Nếu cánh nhấc lên là xoay được thì nguyên nhân là bản lề xệ, cần chỉnh bản lề chứ không phải thay khóa.',
          'Nếu chìa rít: xịt dầu gốc silicon vào lỗ chìa, đưa chìa ra vào vài lần rồi thử xoay. Không dùng bút chì hay dầu ăn — cả hai đều để lại cặn làm ổ kẹt nặng hơn.',
        ],
        'list' => [],
        'note' => 'Tuyệt đối không dùng kìm để xoay chìa. Lực từ kìm vượt xa giới hạn của thân chìa và chìa sẽ gãy đúng bên trong ổ — ca khó nhất để xử lý.',
      ],
      [
        'id' => 'khi-nao-goi',
        'title' => '3. Khi nào dừng lại và gọi kỹ thuật',
        'paragraphs' => [
          'Gọi kỹ thuật ngay khi chìa đã gãy trong ổ, khi ổ khóa xoay tự do mà chốt không chạy, hoặc khi cửa đã đóng và không mở được từ cả hai phía. Trong ba tình huống này, mọi thao tác tự xử lý đều làm tăng khả năng phải phá cửa.',
        ],
        'list' => [],
      ],
      [
        'id' => 'phong-ngua',
        'title' => '4. Phòng ngừa',
        'paragraphs' => [
          'Tra dầu ổ khóa sáu tháng một lần và kiểm tra bản lề mỗi năm. Với cửa gỗ tự nhiên, kiểm tra lại độ khít sau mỗi mùa mưa vì gỗ nở làm cánh chật.',
          'Không treo vật nặng lên tay nắm cửa và không dùng chìa để mở nắp hộp hay cạy đồ — chìa cong vài độ là đủ để kẹt.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Chìa gãy trong ổ, tự lấy ra được không?', 'answer' => 'Nếu phần gãy còn nhô ra khỏi mặt ổ, có thể kẹp nhẹ bằng nhíp mũi nhỏ và rút thẳng ra, không xoay. Nếu phần gãy đã lọt hẳn vào trong, đừng thử — cần dụng cụ chuyên dụng để không làm hỏng bi ổ khóa.'],
      ['question' => 'Cửa gỗ mùa mưa bị chật, có phải thay khóa không?', 'answer' => 'Không. Đây là hiện tượng gỗ nở, thường tự hết khi hết mùa ẩm. Nếu chật kéo dài, chỉnh lại bản lề hoặc bào nhẹ mép cánh là đủ; bộ khóa không liên quan.'],
      ['question' => 'Chìa sao chép ngoài tiệm dùng được không?', 'answer' => 'Dùng được nhưng dung sai thường lớn hơn chìa gốc, dễ gây rít và mòn bi ổ theo thời gian. Với khóa cửa chính, nên đặt chìa gốc qua đại lý thay vì sao chép đại trà.'],
    ],
    'products' => ['khoa-dong-dai', 'khoa-tay-gat-cua-go', 'ban-le-la-dong'],
  ],

  'Vì sao Keybolts không niêm yết giá trên website?' => [
    'quick' => 'Cùng một dòng khóa có thể chênh nhau đáng kể theo size thân, hoàn thiện và số lượng. Niêm yết một con số duy nhất sẽ sai với hầu hết khách. Gửi số đo cửa và số lượng qua hotline hoặc Zalo, báo giá phản hồi trong 24h làm việc.',
    'sections' => [
      [
        'id' => 'gia-theo-cau-hinh',
        'title' => '1. Giá phụ thuộc cấu hình, không phụ thuộc tên model',
        'paragraphs' => [
          'Một dòng khóa như KB 1700 có bốn size thân (thông phòng, trung, đại, đại sảnh) và nhiều hoàn thiện khác nhau. Chênh lệch giữa bản nhỏ nhất và bản lớn nhất là đáng kể, nên một mức giá chung sẽ gây hiểu nhầm cho cả hai phía.',
          'Ngoài ra, cùng một model nhưng lắp cho cửa gỗ tự nhiên dày 55mm sẽ dùng thân dài và trục vuông khác so với cửa công nghiệp 38mm.',
        ],
        'list' => [],
      ],
      [
        'id' => 'theo-so-luong',
        'title' => '2. Chính sách theo số lượng và theo dự án',
        'paragraphs' => [
          'Khách lẻ mua một bộ và nhà thầu đặt 200 bộ cho một tòa chung cư không cùng một biểu giá. Việc niêm yết công khai giá lẻ sẽ khiến các đại lý và nhà thầu phải hỏi lại từ đầu, còn khách lẻ thì nhìn giá dự án lại thấy sai lệch.',
        ],
        'list' => [
          'Khách lẻ: báo giá theo bộ, gồm cả công lắp nếu ở khu vực có kỹ thuật',
          'Nhà thầu và đại lý: biểu giá riêng theo cam kết sản lượng',
          'Dự án: báo giá trọn gói theo danh mục cửa của công trình',
        ],
      ],
      [
        'id' => 'nhan-bao-gia',
        'title' => '3. Cách nhận báo giá nhanh nhất',
        'paragraphs' => [
          'Gửi ba thông tin là đủ để có báo giá chính xác: độ dày cánh cửa, số lượng theo từng loại cửa, và hoàn thiện mong muốn. Kèm ảnh cửa hiện trạng thì kỹ thuật xác nhận được luôn thân khóa phù hợp.',
        ],
        'list' => [],
        'note' => 'Báo giá phản hồi trong 24h làm việc. Với danh mục dự án trên 50 cửa, thời gian có thể lâu hơn vì cần bóc tách theo từng loại cửa.',
      ],
      [
        'id' => 'cam-ket',
        'title' => '4. Cam kết về giá',
        'paragraphs' => [
          'Báo giá đã gửi có hiệu lực trong thời hạn ghi trên phiếu và không thay đổi trong thời hạn đó. Giá đã bao gồm bảo hành chính hãng; chi phí lắp đặt và vận chuyển được tách riêng, ghi rõ trên phiếu chứ không cộng ẩn.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Tôi chỉ muốn biết khoảng giá để cân đối ngân sách thì sao?', 'answer' => 'Gọi hotline và mô tả loại cửa, kỹ thuật sẽ đưa khoảng giá ngay trong cuộc gọi. Con số chính xác vẫn cần số đo, nhưng khoảng giá là đủ để lên ngân sách.'],
      ['question' => 'Giá trên các sàn thương mại điện tử có phải giá chính hãng không?', 'answer' => 'Chỉ các gian hàng do Keybolts hoặc đại lý ủy quyền vận hành mới có giá và bảo hành chính hãng. Liên hệ hotline để xác minh một gian hàng trước khi đặt.'],
      ['question' => 'Báo giá có bao gồm lắp đặt không?', 'answer' => 'Phiếu báo giá tách riêng phần hàng hóa và phần công lắp. Tại khu vực có cơ sở của Keybolts, công lắp được báo cụ thể theo số cửa; ngoài khu vực, đại lý địa phương thực hiện.'],
    ],
    'products' => ['khoa-dong-dai-sanh', 'khoa-dong-thong-phong', 'khoa-van-tay-cua-go'],
  ],

  'Khóa đồng và khóa inox — chọn loại nào?' => [
    'quick' => 'Khóa đồng cho cửa chính và không gian cần vẻ ấm, sang; inox 304 cho cửa phụ, khu vực ẩm và công trình cần chi phí bảo trì thấp. Về độ bền cơ khí hai loại tương đương — khác biệt nằm ở bề mặt và cảm giác cầm.',
    'sections' => [
      [
        'id' => 'chat-lieu',
        'title' => '1. Khác biệt về chất liệu',
        'paragraphs' => [
          'Khóa đồng dùng hợp kim đồng thau đúc, nặng tay và có độ hoàn thiện bề mặt cao khi mạ PVD. Khóa inox dùng thép không gỉ 304 dập hoặc đúc, nhẹ hơn, bề mặt xước mờ hoặc đánh bóng.',
          'Đồng thau có tính kháng khuẩn tự nhiên trên bề mặt trần — một lý do khóa đồng vẫn phổ biến cho tay nắm cửa công cộng ở nhiều nơi.',
        ],
        'list' => [],
      ],
      [
        'id' => 'cam-giac',
        'title' => '2. Cảm giác sử dụng',
        'paragraphs' => [
          'Đây là khác biệt rõ nhất khi cầm thử. Khóa đồng nặng hơn, tay gạt trả về chậm và chắc; khóa inox nhẹ và phản hồi nhanh hơn. Với cửa chính biệt thự, độ nặng của khóa đồng góp phần vào cảm giác cửa chắc chắn.',
          'Ngược lại, cửa phòng dùng nhiều lần trong ngày thì tay gạt inox nhẹ nhàng hơn, đặc biệt với trẻ nhỏ và người lớn tuổi.',
        ],
        'list' => [],
      ],
      [
        'id' => 'moi-truong',
        'title' => '3. Chọn theo môi trường lắp đặt',
        'paragraphs' => [
          'Inox 304 chịu ẩm tốt hơn, phù hợp cửa nhà tắm, cửa bếp, cửa sân và công trình gần biển. Khóa đồng mạ PVD cũng chịu được ngoài trời nhưng đòi hỏi lau chùi đều hơn để giữ màu.',
        ],
        'list' => [
          'Cửa chính, phòng khách, phòng ngủ chính: khóa đồng',
          'Cửa bếp, nhà tắm, cửa sân, ban công: inox 304',
          'Công trình gần biển: inox 304 cho toàn bộ cửa ngoài',
          'Khách sạn và văn phòng: inox cho khối lượng lớn, đồng cho khu vực lễ tân',
        ],
      ],
      [
        'id' => 'chi-phi',
        'title' => '4. Chi phí ban đầu và chi phí giữ gìn',
        'paragraphs' => [
          'Khóa đồng có giá cao hơn ở cùng cấp size, đổi lại là bề mặt và cảm giác cao cấp hơn. Inox rẻ hơn và gần như không cần chăm sóc, chỉ lau bụi định kỳ.',
          'Với công trình nhiều cửa, cách phân bổ hợp lý là dùng khóa đồng cho các cửa nhìn thấy từ khu vực chính và inox cho phần còn lại — vừa giữ được tổng thể vừa hợp lý về ngân sách.',
        ],
        'list' => [],
        'note' => 'Nếu phối cả hai trong cùng một nhà, giữ tay nắm và bản lề cùng hệ với khóa trên mỗi cửa. Trộn hoàn thiện trên cùng một cánh là lỗi thị giác dễ thấy nhất.',
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Khóa đồng có bị xỉn màu theo thời gian không?', 'answer' => 'Đồng trần thì có, nhưng khóa Keybolts đều phủ PVD hoặc DSF nên bề mặt ổn định nhiều năm nếu không bị chà xước. Xem hướng dẫn bảo dưỡng để giữ màu lâu nhất.'],
      ['question' => 'Inox 304 và inox 201 khác nhau nhiều không?', 'answer' => 'Khác đáng kể về khả năng chống gỉ. Inox 201 rẻ hơn nhưng có thể xuất hiện điểm gỉ trong môi trường ẩm hoặc gần biển. Keybolts chỉ dùng 304 cho dòng khóa inox.'],
      ['question' => 'Cửa gỗ tân cổ điển nên chọn loại nào?', 'answer' => 'Khóa đồng hoàn thiện vàng PVD hoặc rêu DSF hợp hơn với hệ tân cổ điển. Inox xước mờ hợp với nội thất hiện đại và tối giản.'],
    ],
    'products' => ['khoa-dong-dai-sanh', 'khoa-tay-gat-inox-304', 'khoa-tay-gat-cua-go'],
  ],

  'Chọn bản lề đúng tải trọng cho cửa gỗ' => [
    'quick' => 'Ước cân nặng cánh trước, rồi chọn số lượng và cỡ bản lề theo đó. Cánh dưới 30kg dùng 3 bản lề, 30–50kg dùng 4, trên 50kg dùng bản lề lá dày có bi. Xệ cánh sau vài tháng gần như luôn là do bản lề thiếu chứ không phải do gỗ.',
    'sections' => [
      [
        'id' => 'uoc-can-nang',
        'title' => '1. Ước cân nặng cánh cửa',
        'paragraphs' => [
          'Cân nặng phụ thuộc diện tích, độ dày và loại gỗ. Một cánh gỗ tự nhiên 0,9 × 2,2m dày 45mm rơi vào khoảng 45–55kg; cùng kích thước bằng gỗ công nghiệp dày 38mm chỉ khoảng 25–30kg.',
          'Nếu không chắc, hỏi đơn vị làm cửa — họ có số liệu chính xác cho từng loại phôi. Con số này quyết định toàn bộ phần còn lại.',
        ],
        'list' => [],
      ],
      [
        'id' => 'so-luong',
        'title' => '2. Số lượng bản lề',
        'paragraphs' => [
          'Nguyên tắc là chia đều tải trọng và không để khoảng cách giữa hai bản lề liền kề quá lớn ở phần trên cánh, nơi chịu mô men lớn nhất.',
        ],
        'list' => [
          'Dưới 30kg: 3 bản lề',
          '30–50kg: 4 bản lề',
          'Trên 50kg: 4 bản lề lá dày có bi, hoặc bản lề sàn cho cửa đặc biệt nặng',
          'Cánh cao trên 2,4m: cộng thêm 1 bản lề bất kể cân nặng',
        ],
        'note' => 'Bản lề trên cùng nên đặt cách mép trên khoảng 150–200mm. Đặt quá xuống dưới là nguyên nhân phổ biến khiến cánh vênh ở góc trên.',
      ],
      [
        'id' => 'kich-thuoc-bi',
        'title' => '3. Kích thước lá và số bi',
        'paragraphs' => [
          'Chiều dài lá bản lề phải phủ đủ chiều dày cánh: cánh 45mm cần lá từ 100mm trở lên. Số bi ảnh hưởng đến độ êm và tuổi thọ — bản lề 2 bi đủ cho cửa phòng, cửa chính nặng nên dùng 4 bi trở lên.',
          'Chất liệu bản lề nên cùng hệ với khóa và tay nắm. Bản lề đồng cho bộ khóa đồng, inox cho bộ inox.',
        ],
        'list' => [],
      ],
      [
        'id' => 'lap-dat',
        'title' => '4. Lắp đặt và kiểm tra sau lắp',
        'paragraphs' => [
          'Vít bản lề phải ăn vào phần gỗ đặc, không ăn vào lớp ván mỏng hay lớp keo. Với cửa công nghiệp, vị trí bản lề phải trùng với thanh gia cường trong khung cánh — nếu không, vít sẽ tuột sau vài tháng.',
          'Sau 3 tháng sử dụng, kiểm tra lại độ khít cánh và siết vít. Đây là thời điểm cửa đã ổn định và hầu hết sai lệch sẽ lộ ra.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Cánh đã xệ rồi thì thêm bản lề có cứu được không?', 'answer' => 'Thường là có, nếu gỗ chưa biến dạng. Thêm một bản lề ở khoảng giữa hai bản lề trên và chỉnh lại vị trí lỗ chốt. Nếu cánh đã vênh cong thì cần xử lý phần cánh trước.'],
      ['question' => 'Bản lề lá và bản lề bật khác nhau thế nào?', 'answer' => 'Bản lề lá bắt vào mặt cạnh cánh, chịu tải tốt hơn, dùng cho cửa gỗ đi. Bản lề bật dùng cho cánh tủ và cửa nhẹ, không phù hợp cho cửa đi gỗ đặc.'],
      ['question' => 'Có cần bản lề tự đóng cho cửa nhà không?', 'answer' => 'Không cần cho cửa phòng. Bản lề hoặc tay co tự đóng hợp lý cho cửa thoát hiểm, cửa ngăn cháy và cửa ra sân — nơi việc quên đóng gây phiền toái thật sự.'],
    ],
    'products' => ['ban-le-la-dong', 'tay-co-hk-tron', 'khoa-dong-trung'],
  ],

  'Cài và xóa vân tay cho thành viên gia đình' => [
    'quick' => 'Đăng ký ít nhất hai ngón cho mỗi người — một ngón mỗi bàn tay — và ghi lại vị trí bộ nhớ của từng người. Khi cần xóa, xóa đúng ô nhớ đó thay vì reset toàn bộ khóa, vì reset sẽ xóa cả mã số và thẻ đã cấp.',
    'sections' => [
      [
        'id' => 'chuan-bi',
        'title' => '1. Chuẩn bị trước khi đăng ký',
        'paragraphs' => [
          'Lau khô và làm sạch đầu ngón tay cũng như mặt cảm biến. Vân tay đăng ký trong điều kiện tay ẩm hoặc bẩn sẽ tạo mẫu kém, dẫn tới việc khóa nhận chập chờn về sau.',
          'Chuẩn bị sẵn mã quản trị. Mọi thao tác thêm và xóa đều yêu cầu mã này — nếu chưa đổi mã mặc định, hãy đổi trước khi làm bất cứ việc gì khác.',
        ],
        'list' => [],
        'note' => 'Ghi lại sơ đồ ô nhớ ngay từ đầu: ô 01 bố, ô 02 mẹ, ô 03 con. Không có sổ này thì sau vài tháng không ai nhớ ô nào của ai và việc xóa một người trở thành reset cả khóa.',
      ],
      [
        'id' => 'them-van-tay',
        'title' => '2. Thêm vân tay',
        'paragraphs' => [
          'Vào chế độ quản trị bằng mã quản trị, chọn chức năng thêm vân tay, rồi đặt ngón lên cảm biến theo số lần khóa yêu cầu — thường là 4 đến 6 lần. Mỗi lần đặt nên xoay nhẹ ngón sang một góc khác nhau để mẫu bao phủ rộng hơn.',
        ],
        'list' => [
          'Đăng ký hai ngón cho mỗi người: một ngón trỏ mỗi bàn tay',
          'Đặt ngón phủ hết mặt cảm biến, không đặt lệch về mép',
          'Thử mở cửa ba lần liên tiếp ngay sau khi đăng ký để xác nhận',
        ],
      ],
      [
        'id' => 'xoa-doi',
        'title' => '3. Xóa và thay đổi',
        'paragraphs' => [
          'Khi có thay đổi nhân sự trong nhà — người giúp việc nghỉ, khách ở dài ngày ra về — hãy xóa đúng ô nhớ của người đó. Chức năng xóa theo ô nhớ có trên mọi model khóa vân tay Keybolts.',
          'Chỉ reset toàn bộ khi bạn muốn xóa sạch mọi vân tay, mã số và thẻ đã cấp, ví dụ khi nhận bàn giao nhà từ chủ cũ.',
        ],
        'list' => [],
      ],
      [
        'id' => 'quan-ly',
        'title' => '4. Quản lý về lâu dài',
        'paragraphs' => [
          'Rà lại danh sách người dùng mỗi 6 tháng. Với trẻ em, vân tay thay đổi theo tuổi nên cần đăng ký lại mỗi năm cho tới khoảng 12 tuổi.',
          'Luôn giữ một phương thức dự phòng đang hoạt động — mã số hoặc thẻ từ — và kiểm tra nó hoạt động ít nhất mỗi quý một lần.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Trẻ mấy tuổi thì đăng ký vân tay được?', 'answer' => 'Từ khoảng 6 tuổi trở lên, khi vân tay đã đủ rõ nét. Trẻ nhỏ hơn nên dùng thẻ từ hoặc mã số riêng để bạn vẫn theo dõi được ai vào nhà lúc nào.'],
      ['question' => 'Đăng ký được tối đa bao nhiêu vân tay?', 'answer' => 'Tùy model, phổ biến là 50 đến 100 mẫu. Con số này dư cho gia đình; giới hạn thực tế chỉ đáng quan tâm với văn phòng hoặc nhà xưởng.'],
      ['question' => 'Quên mã quản trị thì làm sao?', 'answer' => 'Cần reset cứng bằng nút trên mặt trong của khóa, thao tác này xóa toàn bộ người dùng đã đăng ký. Gọi kỹ thuật trước khi thực hiện để được hướng dẫn đúng cho model của bạn.'],
    ],
    'products' => ['khoa-van-tay-cua-go', 'khoa-thong-minh-k42', 'khoa-thong-minh-bd01'],
  ],

  'Khóa điện tử hết pin thì mở cửa thế nào?' => [
    'quick' => 'Ba phương án theo thứ tự ưu tiên: cấp nguồn tạm qua cổng sạc dự phòng ở mặt ngoài khóa, mở bằng chìa cơ, hoặc gọi kỹ thuật. Khóa luôn cảnh báo pin yếu nhiều ngày trước khi hết hẳn, nên tình huống này gần như luôn tránh được.',
    'sections' => [
      [
        'id' => 'canh-bao-pin',
        'title' => '1. Đọc đúng cảnh báo pin yếu',
        'paragraphs' => [
          'Khi pin xuống dưới ngưỡng, khóa phát tiếng bíp khác thường sau mỗi lần mở và đèn báo đổi màu. Từ lúc cảnh báo đầu tiên tới lúc hết hẳn thường còn vài trăm lần đóng mở — đủ thời gian để thay pin thong thả.',
          'Đừng bỏ qua cảnh báo với suy nghĩ "vẫn mở được". Đó là lý do phổ biến nhất khiến người dùng bị kẹt ngoài cửa.',
        ],
        'list' => [],
      ],
      [
        'id' => 'ba-phuong-an',
        'title' => '2. Ba phương án dự phòng',
        'paragraphs' => [
          'Mọi khóa điện tử Keybolts đều có ít nhất hai trong ba phương án dưới đây. Kiểm tra model của bạn có gì và chuẩn bị sẵn trước khi cần đến.',
        ],
        'list' => [
          'Cổng cấp nguồn ngoài: chạm pin sạc dự phòng vào cổng dưới mặt khóa, khóa hoạt động lại ngay trong vài giây',
          'Chìa cơ: giấu nắp che ổ chìa trên mặt khóa, luôn để một chìa ngoài nhà ở nơi cố định',
          'Ứng dụng và mã một lần: với model có kết nối, người trong nhà mở hộ được từ xa',
        ],
        'note' => 'Không cất chìa cơ dự phòng bên trong nhà. Đó là chỗ duy nhất bạn không tới được khi bị kẹt ngoài cửa.',
      ],
      [
        'id' => 'cap-nguon-khan',
        'title' => '3. Cấp nguồn khẩn cấp đúng cách',
        'paragraphs' => [
          'Dùng pin sạc dự phòng thông thường và cáp phù hợp với cổng trên khóa (thường là USB-C hoặc micro-USB). Giữ nguyên kết nối trong lúc mở cửa — khóa chỉ mượn nguồn để chạy, không sạc pin bên trong.',
          'Sau khi vào được nhà, thay pin ngay. Đừng dùng cách cấp nguồn ngoài như giải pháp lâu dài.',
        ],
        'list' => [],
      ],
      [
        'id' => 'thay-pin',
        'title' => '4. Thay pin đúng cách',
        'paragraphs' => [
          'Thay cả bộ cùng lúc, cùng thương hiệu và cùng loại. Trộn pin cũ với pin mới làm giảm tuổi thọ cả bộ và khiến cảnh báo pin yếu báo sai.',
          'Dùng pin kiềm chất lượng tốt, tránh pin sạc dung lượng thấp vì điện áp của chúng tụt sớm khiến khóa báo yếu khi vẫn còn dung lượng.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Pin dùng được bao lâu?', 'answer' => 'Khoảng 8 đến 12 tháng với tần suất gia đình 4 người. Cửa có lượt ra vào cao như cửa văn phòng hoặc căn hộ cho thuê nên tính khoảng 6 tháng.'],
      ['question' => 'Mất điện lưới thì khóa có mở được không?', 'answer' => 'Có. Khóa chạy hoàn toàn bằng pin riêng, không phụ thuộc điện lưới. Mất điện không ảnh hưởng gì tới việc mở cửa.'],
      ['question' => 'Khóa có nhớ vân tay khi thay pin không?', 'answer' => 'Có. Vân tay, mã số và thẻ được lưu trong bộ nhớ không mất dữ liệu khi ngắt nguồn. Chỉ thao tác reset cứng mới xóa chúng.'],
    ],
    'products' => ['khoa-thong-minh-t28', 'khoa-van-tay-cua-go', 'khoa-thong-minh-bd01'],
  ],

  'Chốt Cremone và chốt âm — dùng cho cửa nào?' => [
    'quick' => 'Cremone dùng cho cửa sổ và cửa đi hai cánh nơi cần khóa nhiều điểm bằng một thao tác, đồng thời là chi tiết trang trí. Chốt âm dùng cố định cánh phụ của cửa hai cánh, kín đáo và rẻ hơn. Nhiều công trình dùng cả hai ở các vị trí khác nhau.',
    'sections' => [
      [
        'id' => 'cremone-la-gi',
        'title' => '1. Cremone là gì',
        'paragraphs' => [
          'Cremone là bộ chốt thanh dọc điều khiển bằng một tay gạt ở giữa: xoay tay gạt là hai đầu thanh cùng ăn vào lỗ chốt trên và dưới. Ưu điểm là khóa hai điểm chỉ bằng một thao tác, và bộ tay gạt nằm lộ ra như một chi tiết trang trí.',
          'Vì lộ ra ngoài, cremone gần như luôn được chọn theo hoàn thiện của bộ khóa và tay nắm — vàng PVD cho hệ tân cổ điển, inox cho hệ hiện đại.',
        ],
        'list' => [],
      ],
      [
        'id' => 'chot-am-la-gi',
        'title' => '2. Chốt âm là gì',
        'paragraphs' => [
          'Chốt âm nằm chìm trong mép cánh, chỉ lộ ra một lẫy gạt nhỏ. Nó cố định cánh phụ của cửa hai cánh vào khuôn trên và ngưỡng dưới, để cánh chính có chỗ chốt vào.',
          'Chốt âm thao tác từng chốt một, nên với cửa cao hoặc cửa dùng thường xuyên thì bất tiện hơn cremone. Bù lại, nó kín đáo và không ảnh hưởng tới thị giác của cánh cửa.',
        ],
        'list' => [],
      ],
      [
        'id' => 'chon-theo-cua',
        'title' => '3. Chọn theo loại cửa',
        'paragraphs' => [
          'Cách chọn đơn giản nhất là hỏi: cánh này có mở thường xuyên không, và có muốn nhìn thấy bộ chốt không.',
        ],
        'list' => [
          'Cửa sổ mở quay: cremone cỡ nhỏ, vừa khóa vừa làm tay nắm',
          'Cửa đi hai cánh tân cổ điển, cánh phụ mở thường xuyên: cremone cỡ cửa đi',
          'Cửa đi hai cánh, cánh phụ hầu như luôn đóng: chốt âm',
          'Cửa đại sảnh cao trên 2,4m: cremone cỡ lớn để với tới cả hai đầu thanh',
        ],
        'note' => 'Chiều dài thanh cremone phải đặt theo chiều cao cánh thực tế. Cắt ngắn tại công trường được, nhưng nối dài thì không.',
      ],
      [
        'id' => 'luu-y-lap',
        'title' => '4. Lưu ý khi lắp',
        'paragraphs' => [
          'Lỗ chốt trên khuôn và dưới ngưỡng phải khoan sau khi cánh đã treo và chỉnh khít, không khoan trước theo bản vẽ. Sai lệch vài milimet là đủ để thanh chốt không ăn hết chiều sâu.',
          'Với ngưỡng đá hoặc sàn gỗ hoàn thiện, dùng đế chốt bằng đồng hoặc inox thay vì khoan trực tiếp — vừa bảo vệ sàn vừa dễ chỉnh lại về sau.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Cửa hai cánh có cần cả cremone lẫn khóa chính không?', 'answer' => 'Có. Cremone hoặc chốt âm cố định cánh phụ, còn bộ khóa chính lắp trên cánh hoạt động và chốt vào cánh phụ đã cố định. Hai bộ phận làm hai việc khác nhau.'],
      ['question' => 'Cremone có chống trộm được không?', 'answer' => 'Cremone tăng số điểm chốt nên khó cạy hơn một chốt đơn, nhưng nó không thay thế bộ khóa có ổ chìa. Với cửa ngoài, luôn kết hợp cremone với khóa chính.'],
      ['question' => 'Cremone inox và cremone đồng chọn theo gì?', 'answer' => 'Theo hoàn thiện của bộ khóa và tay nắm trên cùng cánh cửa đó. Cremone lộ ra rất rõ nên lệch hoàn thiện là lỗi thị giác dễ thấy nhất trong bộ phụ kiện cửa.'],
    ],
    'products' => ['cremone-cua-di-dong-ma-vang', 'cremone-cua-so-dong-ma-vang', 'cremone-inox-cua-di'],
  ],

  'Chọn hoàn thiện khóa hợp phong cách nội thất' => [
    'quick' => 'Chọn một hệ hoàn thiện duy nhất cho toàn bộ phụ kiện cửa trong nhà — khóa, tay nắm, bản lề, chặn cửa — rồi bám theo nó. Vàng PVD hợp tân cổ điển, rêu DSF hợp cổ điển trầm, inox xước hợp hiện đại tối giản.',
    'sections' => [
      [
        'id' => 'bang-hoan-thien',
        'title' => '1. Ba hệ hoàn thiện phổ biến',
        'paragraphs' => [
          'Vàng PVD là lớp phủ kim loại sáng, ánh vàng ấm, bề mặt bóng và rất cứng. Rêu DSF là hoàn thiện đồng cổ tối màu, bề mặt mờ, giấu dấu vân tay tốt. Inox 304 xước mờ là hoàn thiện trung tính, lạnh và hiện đại.',
          'Cả ba đều bền như nhau về mặt cơ khí. Việc chọn hoàn toàn là quyết định thị giác.',
        ],
        'list' => [],
      ],
      [
        'id' => 'phoi-theo-phong-cach',
        'title' => '2. Phối theo phong cách nội thất',
        'paragraphs' => [
          'Quy tắc dễ áp dụng nhất: nhìn vào màu kim loại đã có sẵn trong nhà — chân bàn, đèn, vòi nước, tay nắm tủ bếp — rồi chọn hoàn thiện khóa cùng tông với nhóm đó.',
        ],
        'list' => [
          'Tân cổ điển, cửa gỗ sơn trắng hoặc gỗ sáng: vàng PVD',
          'Cổ điển, gỗ tối màu, nội thất trầm: rêu DSF',
          'Hiện đại, tối giản, nhiều kính và bê tông: inox 304 xước mờ',
          'Indochine: rêu DSF hoặc đồng trần, tránh vàng bóng',
        ],
      ],
      [
        'id' => 'dong-bo-phu-kien',
        'title' => '3. Đồng bộ toàn bộ phụ kiện cửa',
        'paragraphs' => [
          'Một cánh cửa có ít nhất bốn chi tiết kim loại nhìn thấy: khóa, tay nắm, bản lề và chặn cửa. Lệch một trong bốn là đủ để tổng thể trông chắp vá, và đây là lỗi hay gặp nhất vì bản lề thường được mua trước theo đơn vị làm cửa.',
          'Cách tránh là chốt hoàn thiện ngay từ giai đoạn đặt cửa, trước khi đơn vị mộc mua bản lề.',
        ],
        'list' => [],
        'note' => 'Nếu buộc phải trộn hai hoàn thiện trong nhà, chia theo khu vực chứ đừng chia theo chi tiết: ví dụ toàn bộ tầng trệt dùng vàng PVD, toàn bộ tầng trên dùng inox.',
      ],
      [
        'id' => 'bao-quan',
        'title' => '4. Giữ hoàn thiện lâu dài',
        'paragraphs' => [
          'Hoàn thiện bóng như vàng PVD lộ dấu vân tay và vết xước rõ hơn hoàn thiện mờ. Nếu nhà có trẻ nhỏ hoặc cửa dùng cường độ cao, cân nhắc DSF hoặc inox xước để đỡ phải lau thường xuyên.',
          'Dù chọn hệ nào, nguyên tắc chăm sóc giống nhau: lau khô bằng vải mềm, không dùng hóa chất tẩy và không chà bằng vật nhám.',
        ],
        'list' => [],
      ],
    ],
    'compare' => [],
    'faqs' => [
      ['question' => 'Có nên chọn hoàn thiện theo màu cửa hay theo màu nội thất?', 'answer' => 'Theo nhóm kim loại trong nội thất. Màu cửa thay đổi giữa các phòng, còn tông kim loại là thứ giữ cho tổng thể liền mạch khi đi qua nhiều không gian.'],
      ['question' => 'Đặt được hoàn thiện riêng không có trong danh mục không?', 'answer' => 'Với đơn hàng dự án đủ số lượng thì có. Liên hệ hotline kèm mẫu màu mong muốn và số lượng dự kiến để kỹ thuật xác nhận khả năng đáp ứng và thời gian.'],
      ['question' => 'Đã lỡ mua lệch hoàn thiện thì xử lý sao?', 'answer' => 'Thay chi tiết lệch là cách duy nhất giữ được tổng thể — sơn hay mạ lại tại chỗ không cho ra kết quả bền. Bản lề và chặn cửa là hai món rẻ nhất để thay, nên ưu tiên thay chúng.'],
    ],
    'products' => ['khoa-dong-dai-sanh', 'khoa-tay-gat-inox-304', 'tay-nam-cua-dong'],
  ],

];
