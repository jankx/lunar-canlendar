<?php
// Server-side render for jankx/lunar-calendar
?>
<div class="lunar-calendar-container">
    <div class="lunar-calendar-header">
        <h1>Lịch Âm Dương</h1>
        <p>Tra cứu lịch âm dương Việt Nam</p>
    </div>

    <div class="lunar-current-date-section">
        <div class="lunar-date-column">
            <div class="lunar-date-label">Dương lịch</div>
            <div class="lunar-date-number" id="current-gregorian-day">08</div>
            <div class="lunar-date-month-year" id="current-gregorian-month-year">Tháng 08 năm 2025</div>
            <div class="lunar-date-day" id="current-gregorian-day-name">Thứ 6</div>
        </div>
        <div class="lunar-date-column">
            <div class="lunar-date-label">Âm lịch</div>
            <div class="lunar-date-number" id="current-lunar-day">15</div>
            <div class="lunar-date-month-year" id="current-lunar-month-year">Tháng 06 năm Ất Tỵ</div>
            <div class="lunar-info" id="current-lunar-details">Ngày Kỷ Dậu - Tháng Quý Mùi</div>
        </div>
    </div>

    <div class="lunar-holiday-info">
        <div class="lunar-holiday-title">Thông tin ngày lễ hôm nay</div>
        <div class="lunar-holiday-content" id="holiday-info">Không có</div>
    </div>

    <div class="lunar-calendar-nav">
        <button class="lunar-nav-arrow" id="prev-month"><span class="screen-reader-text">Trước</span></button>
        <div class="lunar-nav-center">
            <div class="lunar-current-month-year" id="current-month-year">Tháng 8 - 2025</div>
            <div class="lunar-month-year-selectors">
                <select id="month-selector">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>"<?php echo $m === (int) date('n') ? ' selected' : ''; ?>>Tháng <?php echo $m; ?></option>
                    <?php endfor; ?>
                </select>
                <select id="year-selector">
                    <?php for ($y = (int) date('Y') - 2; $y <= (int) date('Y') + 2; $y++): ?>
                        <option value="<?php echo $y; ?>"<?php echo $y === (int) date('Y') ? ' selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <button class="lunar-view-btn" id="view-btn">Xem</button>
                <button class="lunar-today-btn" id="today-btn">Hôm nay</button>
            </div>
        </div>
        <button class="lunar-nav-arrow" id="next-month"><span class="screen-reader-text">Sau</span></button>
    </div>

    <div id="loading-indicator" class="lunar-loading-indicator" style="display: none;">
        <div class="lunar-loading-spinner"></div>
        <span class="lunar-loading-text">Đang tải dữ liệu...</span>
    </div>

    <div id="page-loading-overlay" class="lunar-page-loading-overlay" style="display: none;">
        <div class="lunar-page-loading-content">
            <div class="lunar-page-loading-spinner"></div>
        </div>
    </div>

    <div class="lunar-calendar-grid">
        <div class="lunar-weekdays">
            <div class="lunar-weekday">Thứ hai</div>
            <div class="lunar-weekday">Thứ ba</div>
            <div class="lunar-weekday">Thứ tư</div>
            <div class="lunar-weekday">Thứ năm</div>
            <div class="lunar-weekday">Thứ sáu</div>
            <div class="lunar-weekday">Thứ bảy</div>
            <div class="lunar-weekday">Chủ nhật</div>
        </div>
        <div class="lunar-calendar-days" id="calendar-days"></div>
    </div>
</div>

