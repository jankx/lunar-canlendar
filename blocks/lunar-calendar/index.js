
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, SelectControl } from '@wordpress/components';
import { useEffect, useRef } from '@wordpress/element';
import './style.css';

// Import các thư viện cần thiết cho preview (moment, fontawesome, ...)
// Nếu dùng webpack, cần import các thư viện JS calendar vào đây hoặc nhúng qua enqueue script.

registerBlockType('jankx/lunar-calendar', {
    edit: function Edit({ attributes, setAttributes }) {
        const {
            gregorianDayFontSize,
            lunarDayFontSize,
            gregorianIcon,
            lunarIcon,
            showIcons
        } = attributes;

        const props = useBlockProps({ className: 'lunar-calendar-container lunar-calendar-editor-preview' });
        const calendarRef = useRef();

        useEffect(() => {
            // Nếu đã có window.LunarCalendar thì khởi tạo lại preview
            if (window.LunarCalendar && typeof window.LunarCalendar === 'function') {
                new window.LunarCalendar(calendarRef.current);
            } else {
                // Nếu chưa có, có thể load script động hoặc báo lỗi
                // (Tùy vào cách bạn bundle JS calendar)
            }
        }, []);

        // Dynamic styles based on attributes
        const customStyles = {
            '--gregorian-day-font-size': `${gregorianDayFontSize}rem`,
            '--lunar-day-font-size': `${lunarDayFontSize}rem`,
        };

        const iconOptions = [
            { label: 'Calendar', value: 'calendar-alt' },
            { label: 'Calendar Check', value: 'calendar-check' },
            { label: 'Calendar Day', value: 'calendar-day' },
            { label: 'Sun', value: 'sun' },
            { label: 'Moon', value: 'moon' },
            { label: 'Star', value: 'star' },
            { label: 'Clock', value: 'clock' },
            { label: 'Globe', value: 'globe' },
        ];

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Dương lịch', 'lunar-calendar')} initialOpen={true}>
                        <RangeControl
                            label={__('Font size ngày hiện tại', 'lunar-calendar')}
                            value={gregorianDayFontSize}
                            onChange={(value) => setAttributes({ gregorianDayFontSize: value })}
                            min={1}
                            max={5}
                            step={0.1}
                        />
                        {showIcons && (
                            <SelectControl
                                label={__('Icon', 'lunar-calendar')}
                                value={gregorianIcon}
                                options={iconOptions}
                                onChange={(value) => setAttributes({ gregorianIcon: value })}
                            />
                        )}
                    </PanelBody>
                    <PanelBody title={__('Âm lịch', 'lunar-calendar')} initialOpen={true}>
                        <RangeControl
                            label={__('Font size ngày hiện tại', 'lunar-calendar')}
                            value={lunarDayFontSize}
                            onChange={(value) => setAttributes({ lunarDayFontSize: value })}
                            min={1}
                            max={5}
                            step={0.1}
                        />
                        {showIcons && (
                            <SelectControl
                                label={__('Icon', 'lunar-calendar')}
                                value={lunarIcon}
                                options={iconOptions}
                                onChange={(value) => setAttributes({ lunarIcon: value })}
                            />
                        )}
                    </PanelBody>
                    <PanelBody title={__('Tùy chọn hiển thị', 'lunar-calendar')} initialOpen={false}>
                        <ToggleControl
                            label={__('Hiển thị icons', 'lunar-calendar')}
                            checked={showIcons}
                            onChange={(value) => setAttributes({ showIcons: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...props} style={customStyles}>
                    {/* HTML preview lịch âm dương (có thể rút gọn, hoặc lấy từ calendar-html.php nếu cần) */}
                    <div ref={calendarRef}>
                    <div className="lunar-calendar-header">
                        <h1>Lịch Âm Dương</h1>
                        <p>Tra cứu lịch âm dương Việt Nam</p>
                    </div>
                    <div className="lunar-current-date-section">
                        <div className="lunar-date-nav-buttons-wrapper lunar-date-nav-buttons-top">
                            <button className="lunar-date-nav-btn" id="prev-day-btn" title="Ngày trước">
                                <i className="fas fa-chevron-left"></i>
                                <span className="lunar-date-nav-text">Trước</span>
                            </button>
                        </div>
                        <div className="lunar-date-columns-wrapper">
                        <div className="lunar-date-column lunar-date-column-gregorian">
                            <div className="lunar-date-label">
                                {showIcons && <i className={`fas fa-${gregorianIcon}`}></i>}
                                Dương lịch
                            </div>
                            <div className="lunar-date-number" id="current-gregorian-day">08</div>
                            <div className="lunar-date-month-year" id="current-gregorian-month-year">Tháng 08 năm 2025</div>
                            <div className="lunar-date-day" id="current-gregorian-day-name">Thứ 6</div>
                        </div>
                        <div className="lunar-date-column lunar-date-column-lunar">
                            <div className="lunar-date-label">
                                {showIcons && <i className={`fas fa-${lunarIcon}`}></i>}
                                Âm lịch
                            </div>
                            <div className="lunar-date-number" id="current-lunar-day">15</div>
                            <div className="lunar-date-month-year" id="current-lunar-month-year">Tháng 06 năm Ất Tỵ</div>
                            <div className="lunar-info" id="current-lunar-details">Ngày Kỷ Dậu - Tháng Quý Mùi</div>
                        </div>
                        </div>
                        <div className="lunar-date-nav-buttons-wrapper lunar-date-nav-buttons-bottom">
                            <button className="lunar-date-nav-btn" id="next-day-btn" title="Ngày tiếp theo">
                                <span className="lunar-date-nav-text">Sau</span>
                                <i className="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div className="lunar-holiday-info">
                        <div className="lunar-holiday-title">Thông tin ngày lễ hôm nay</div>
                        <div className="lunar-holiday-content" id="holiday-info">Không có</div>
                    </div>
                    <div className="lunar-calendar-nav">
                        <div>
                            <button className="lunar-nav-arrow" id="prev-month">
                                <i className="fas fa-chevron-left"></i>
                            </button>
                            <div className="lunar-current-month-year" id="current-month-year">Tháng 8 - 2025</div>
                            <button className="lunar-nav-arrow" id="next-month">
                                <i className="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div className="lunar-month-year-selectors">
                            <select id="month-selector">
                                <option value="1">Tháng 1</option>
                                <option value="2">Tháng 2</option>
                                <option value="3">Tháng 3</option>
                                <option value="4">Tháng 4</option>
                                <option value="5">Tháng 5</option>
                                <option value="6">Tháng 6</option>
                                <option value="7">Tháng 7</option>
                                <option value="8" selected>Tháng 8</option>
                                <option value="9">Tháng 9</option>
                                <option value="10">Tháng 10</option>
                                <option value="11">Tháng 11</option>
                                <option value="12">Tháng 12</option>
                            </select>
                            <select id="year-selector">
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025" selected>2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                            <button className="lunar-view-btn" id="view-btn">Xem</button>
                            <button className="lunar-today-btn" id="today-btn">Hôm nay</button>
                        </div>
                    </div>
                    <div id="loading-indicator" className="lunar-loading-indicator" style={{ display: 'none' }}>
                        <div className="lunar-loading-spinner"></div>
                        <span className="lunar-loading-text">Đang tải dữ liệu...</span>
                    </div>
                    <div id="page-loading-overlay" className="lunar-page-loading-overlay" style={{ display: 'none' }}>
                        <div className="lunar-page-loading-content">
                            <div className="lunar-page-loading-spinner"></div>
                        </div>
                    </div>
                    <div className="lunar-calendar-grid">
                        <div className="lunar-weekdays">
                            <div className="lunar-weekday">Thứ hai</div>
                            <div className="lunar-weekday">Thứ ba</div>
                            <div className="lunar-weekday">Thứ tư</div>
                            <div className="lunar-weekday">Thứ năm</div>
                            <div className="lunar-weekday">Thứ sáu</div>
                            <div className="lunar-weekday">Thứ bảy</div>
                            <div className="lunar-weekday">Chủ nhật</div>
                        </div>
                        <div className="lunar-calendar-days" id="calendar-days">
                            {/* Calendar days will be generated by JS */}
                        </div>
                    </div>
                </div>
                </div>
            </>
        );
    },
    save: function save() {
        return null;
    }
});

