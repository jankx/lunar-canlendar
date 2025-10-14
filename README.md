# Lunar Calendar Block - Tích hợp Events Manager

## Tổng quan

Block Lịch Âm Dương này đã được tích hợp với plugin **Events Manager** để hiển thị các sự kiện thực từ WordPress thay vì mock data.

## Tính năng mới

### ✅ Tích hợp Events Manager
- Lấy sự kiện từ Events Manager plugin
- Hiển thị thông tin thời gian chính xác
- Hiển thị địa điểm sự kiện
- Link đến trang chi tiết sự kiện

### ✅ Thông tin chi tiết sự kiện
- **Thời gian**: Giờ bắt đầu - Giờ kết thúc hoặc "Cả ngày"
- **Địa điểm**: Tên và địa chỉ địa điểm
- **Năm lịch sử**: Năm xảy ra sự kiện (nếu có custom field)
- **Link**: Liên kết đến trang chi tiết sự kiện

### ✅ Phân loại sự kiện theo Categories
- Tự động map category slug sang event type
- Hỗ trợ nhiều loại sự kiện: lịch sử, quốc gia, quốc tế, nghề nghiệp, xã hội, tưởng niệm

## Cách sử dụng

### 1. Cài đặt Events Manager
Đảm bảo plugin Events Manager đã được cài đặt và kích hoạt.

### 2. Tạo sự kiện
1. Vào **Events** > **Add New Event**
2. Nhập thông tin sự kiện:
   - **Tên sự kiện**: Tiêu đề sự kiện
   - **Mô tả**: Nội dung chi tiết
   - **Ngày bắt đầu**: Ngày diễn ra sự kiện
   - **Giờ bắt đầu**: Thời gian (hoặc chọn "All Day")
   - **Ngày kết thúc**: Ngày kết thúc sự kiện
   - **Địa điểm**: Chọn hoặc tạo địa điểm mới
   - **Categories**: Chọn category phù hợp

### 3. Tùy chỉnh Categories
Tạo các categories với slug phù hợp để tự động phân loại:

```
- lich-su → Historical events
- quoc-gia → National events
- quoc-te → International events
- nghe-nghiep → Professional events
- xa-hoi → Social events
- tuong-niem → Memorial events
- le-hoi → Celebration events
- van-hoa → Cultural events
- ton-giao → Religious events
```

### 4. Thêm năm lịch sử (Tùy chọn)
Để hiển thị thông tin "X năm trước", thêm custom field:
- **Meta key**: `_event_year`
- **Value**: Năm xảy ra sự kiện (VD: 1945)

## Tùy chỉnh nâng cao

### Thay đổi mapping categories
```php
add_filter('lunar_calendar_category_type_map', function($map) {
    $map['my-custom-category'] = 'historical';
    return $map;
});
```

### Thay đổi mapping type numbers
```php
add_filter('lunar_calendar_type_number_map', function($map) {
    $map['my-custom-type'] = 1;
    return $map;
});
```

## API Endpoint

Block sử dụng AJAX endpoint: `/wp-admin/admin-ajax.php?action=jankx_lunar_calendar_events`

### Parameters:
- `month`: Tháng (1-12)
- `year`: Năm (VD: 2025)

### Response format:
```json
{
    "success": true,
    "data": {
        "month": "08",
        "year": 2025,
        "events": [
            {
                "day": 15,
                "title": "Tên sự kiện",
                "description": "Mô tả sự kiện",
                "start_date": "2025-08-15",
                "end_date": "2025-08-22",
                "start_time": "09:00",
                "end_time": "17:00",
                "time_display": "09:00 - 17:00",
                "is_all_day": false,
                "location": "Địa điểm sự kiện",
                "event_url": "https://example.com/event/",
                "type": 1,
                "typeName": "National"
            }
        ],
        "total": 1
    }
}
```

## Xử lý lỗi

### Events Manager không được cài đặt
- Block sẽ hiển thị thông báo lỗi
- Fallback về message "Không có sự kiện"

### Không có sự kiện trong tháng
- Trả về mảng rỗng `[]`
- Calendar hiển thị "Không có sự kiện nào" khi chọn ngày không có event
- Calendar vẫn hoạt động bình thường

## Lưu ý kỹ thuật

- Block chỉ lấy events có status = 1 (Published)
- Sắp xếp theo ngày và giờ bắt đầu
- Hỗ trợ events kéo dài nhiều ngày
- Tự động cache events trong 3 tháng (prev, current, next)
- **Backup mechanism**: Nếu EM_Events class không hoạt động, block sẽ truy vấn trực tiếp từ database

## Cấu trúc Database

Events Manager lưu trữ dữ liệu trong các bảng:

### Bảng chính: `{prefix}_em_events`
```sql
- event_id (Primary Key, BIGINT UNSIGNED AUTO_INCREMENT)
- post_id (Foreign Key to wp_posts, BIGINT UNSIGNED)
- event_name (TEXT) - Tên sự kiện
- event_start_date (DATE) - Ngày bắt đầu
- event_end_date (DATE) - Ngày kết thúc
- event_start_time (TIME) - Giờ bắt đầu
- event_end_time (TIME) - Giờ kết thúc
- event_all_day (TINYINT UNSIGNED) - Sự kiện cả ngày
- event_active_status (TINYINT) - Trạng thái active (1 = active)
- location_id (Foreign Key to wp_em_locations, BIGINT UNSIGNED)
- recurrence_id (BIGINT UNSIGNED) - ID sự kiện lặp lại
- event_archetype (VARCHAR(20)) - Loại event (event, recurring)
- event_type (VARCHAR(20)) - Loại (single, recurring)
```

### Bảng địa điểm: `{prefix}_em_locations`
```sql
- location_id (Primary Key)
- post_id (Foreign Key to wp_posts)
- location_name
- location_address
```

### Bảng categories: `{prefix}_term_taxonomy`
```sql
- taxonomy = 'event-categories'
- term_id (Foreign Key to wp_terms)
```

### Custom Fields: `{prefix}_postmeta`
```sql
- post_id (Foreign Key)
- meta_key = '_event_year' (tùy chọn)
- meta_value = năm lịch sử
```

### Truy vấn SQL mẫu:
```sql
SELECT
    e.event_id,
    e.post_id,
    e.event_name,
    e.event_start,
    e.event_end,
    e.event_all_day,
    e.location_id,
    p.post_excerpt,
    p.post_content
FROM bcs_em_events e
LEFT JOIN bcs_posts p ON e.post_id = p.ID
WHERE p.post_status = 'publish'
AND e.event_start >= '2025-08-01 00:00:00'
AND e.event_start <= '2025-08-31 23:59:59'
ORDER BY e.event_start ASC
```

## Troubleshooting

### Events không hiển thị
1. Kiểm tra Events Manager đã active
2. Kiểm tra event đã publish
3. Kiểm tra ngày event trong khoảng tháng được chọn
4. Xem console browser để debug AJAX calls

### Categories không được map đúng
1. Kiểm tra slug của category
2. Thêm filter để tùy chỉnh mapping
3. Kiểm tra category đã được assign cho event

### Performance issues
- Block sử dụng lazy loading cho 3 tháng
- Chỉ load thêm tháng khi cần thiết
- Events được cache trong session

## Debug & Development

### Debug Database Structure
Để kiểm tra cấu trúc database Events Manager:

```javascript
// Trong browser console (chỉ cho admin)
fetch('/wp-admin/admin-ajax.php?action=jankx_lunar_calendar_debug')
    .then(response => response.json())
    .then(data => console.log(data));
```

Response sẽ trả về:
```json
{
    "success": true,
    "data": {
        "table_prefix": "bcs_",
        "events_table": "bcs_em_events",
        "locations_table": "bcs_em_locations",
        "events_table_exists": true,
        "locations_table_exists": true,
        "events_count": "5",
        "sample_event": {
            "event_id": "1",
            "post_id": "123",
            "event_name": "Sample Event",
            "event_start": "2025-08-15 09:00:00",
            "event_end": "2025-08-15 17:00:00",
            "event_all_day": "0",
            "location_id": "1"
        }
    }
}
```

### Testing API Endpoint
```bash
# Test main endpoint (tháng 10 năm 2025)
curl "https://yourdomain.com/wp-admin/admin-ajax.php?action=jankx_lunar_calendar_events&month=10&year=2025"

# Test debug endpoint (chỉ cho admin)
curl "https://yourdomain.com/wp-admin/admin-ajax.php?action=jankx_lunar_calendar_debug"
```

### Debug Events không hiển thị

1. **Kiểm tra WordPress Debug Log**:
```php
// Thêm vào wp-config.php để bật debug
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

2. **Xem log trong**: `/wp-content/debug.log`

3. **Tìm các dòng log**:
```
Lunar Calendar Debug - Month: 10, Year: 2025
Lunar Calendar Debug - Date Range: 2025-10-01 to 2025-10-31
Lunar Calendar SQL: SELECT e.event_id, e.post_id, e.event_name...
Lunar Calendar Results Count: 1
```

4. **Kiểm tra query trực tiếp**:
```sql
SELECT
    e.event_id,
    e.post_id,
    e.event_name,
    e.event_start_date,
    e.event_end_date
FROM bcs_em_events e
INNER JOIN bcs_posts p ON e.post_id = p.ID
WHERE p.post_status = 'publish'
AND e.event_start_date >= '2025-10-01'
AND e.event_start_date <= '2025-10-31'
ORDER BY e.event_start_date ASC;
```
