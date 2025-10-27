# Lunar Calendar Events Database

Hệ thống quản lý events cho calendar với nhiều nguồn dữ liệu và trọng số ưu tiên.

## Cấu trúc thư mục

```
db/
├── lunar-events/     # Các ngày lễ theo lịch âm
│   ├── 01.json      # Tháng 1 âm lịch
│   ├── 02.json      # Tháng 2 âm lịch
│   └── ...
└── solar-events/     # Các ngày lễ theo lịch dương
    ├── 01.json      # Tháng 1 dương lịch
    ├── 02.json      # Tháng 2 dương lịch
    └── ...
```

## Hệ thống trọng số (Weight System)

Events được merge từ nhiều nguồn với trọng số ưu tiên:

1. **MySQL Database (wp-event-solution plugin)** - Weight: `1.0` (ưu tiên cao nhất)
   - Events do admin tạo từ WordPress
   - Có thể chỉnh sửa và quản lý qua WP Admin

2. **Lunar Events JSON** - Weight: `0.8`
   - Các ngày lễ truyền thống theo lịch âm Việt Nam
   - Tự động convert sang lịch dương theo năm

3. **Solar Events JSON** - Weight: `0.4` (ưu tiên thấp nhất)
   - Các ngày lễ quốc tế và Việt Nam theo lịch dương
   - Hỗ trợ cả ngày cố định và ngày đặc thù (theo thứ)

**Quy tắc merge:**
- Khi nhiều events cùng ngày, event có weight cao hơn sẽ được hiển thị
- MySQL events luôn được ưu tiên (admin có thể override default events)

## Lunar Events Format

File: `lunar-events/{month}.json`

```json
{
  "month": 1,
  "calendar_type": "lunar",
  "weight": 0.8,
  "events": [
    {
      "day": 1,
      "title": "Tết Nguyên Đán",
      "description": "Tết Nguyên Đán - Ngày đầu tiên của năm mới âm lịch",
      "type": 1,
      "type_name": "national",
      "is_holiday": true,
      "recurrence": "yearly"
    }
  ]
}
```

### Fields

- `day`: Ngày trong tháng âm lịch (1-30)
- `title`: Tên sự kiện
- `description`: Mô tả chi tiết
- `type`: Mã loại sự kiện (số 0-9)
- `type_name`: Tên loại sự kiện
- `is_holiday`: Có phải ngày lễ không
- `recurrence`: Tần suất lặp lại (yearly, monthly, ...)
- `year` (optional): Năm sự kiện xảy ra (cho sự kiện lịch sử)

## Solar Events Format

File: `solar-events/{month}.json`

### Ngày cố định (Fixed Day)

```json
{
  "day": 1,
  "day_rule": "fixed",
  "title": "Tết Dương lịch",
  "description": "Năm mới Dương lịch",
  "type": 3,
  "type_name": "international",
  "is_holiday": true,
  "recurrence": "yearly"
}
```

### Ngày đặc thù theo thứ (Weekday-based)

```json
{
  "day_rule": "second_sunday",
  "weekday": 0,
  "week_number": 2,
  "title": "Ngày của Mẹ",
  "description": "Mother's Day - Chủ nhật thứ 2 của tháng 5",
  "type": 3,
  "type_name": "international",
  "is_holiday": false,
  "recurrence": "yearly"
}
```

### Weekday Rules

- `day_rule`: Quy tắc tính ngày
  - `"fixed"`: Ngày cố định
  - `"second_sunday"`: Chủ nhật thứ 2
  - `"third_sunday"`: Chủ nhật thứ 3
  - `"fourth_thursday"`: Thứ 5 thứ 4
  - etc.

- `weekday`: Thứ trong tuần (0 = Chủ nhật, 1 = Thứ 2, ..., 6 = Thứ 7)
- `week_number`: Tuần thứ mấy trong tháng (1-5)

## Event Types

| Type | Type Name     | Màu sắc | Mô tả |
|------|---------------|---------|-------|
| 0    | default       | Xám     | Mặc định |
| 1    | national      | Đỏ      | Ngày lễ quốc gia |
| 2    | historical    | Xanh dương | Sự kiện lịch sử |
| 3    | international | Xanh lá | Ngày lễ quốc tế |
| 4    | professional  | Tím     | Ngày nghề nghiệp |
| 5    | social        | Cam     | Ngày xã hội |
| 6    | memorial      | Nâu     | Ngày tưởng niệm |
| 7    | celebration   | Hồng    | Lễ hội |
| 8    | cultural      | Cyan    | Văn hóa |
| 9    | religious     | Vàng    | Tôn giáo |

## Cách thêm Events mới

### 1. Thêm vào JSON (cho default events)

**Lunar Events:**
```bash
# Chỉnh sửa file tháng tương ứng
vim db/lunar-events/01.json
```

**Solar Events:**
```bash
# Chỉnh sửa file tháng tương ứng
vim db/solar-events/05.json
```

### 2. Thêm qua WordPress Admin (cho custom events)

1. Cài đặt plugin `wp-event-solution`
2. Vào **Events** > **Add New**
3. Tạo event mới với các thông tin cần thiết
4. Event này sẽ có weight 1.0 (cao nhất)

## API Usage

```php
use Jankx\LunarCanlendar\EventsManager;

// Get all events for a specific month
$events = EventsManager::getEvents($month, $year);

// Clear cache
EventsManager::clearCache();
```

## Filters

### Customize Category Type Map

```php
add_filter('lunar-calendar/category-type-map', function($map) {
    $map['custom-category'] = 'custom_type';
    return $map;
});
```

### Customize Type Number Map

```php
add_filter('lunar-calendar/type-number-map', function($map) {
    $map['custom_type'] = 10;
    return $map;
});
```

## Notes

- Lunar events được tự động convert sang solar date theo năm
- Cache được sử dụng để tối ưu performance
- Events có thể được override bằng cách tạo event trong WordPress với cùng ngày

