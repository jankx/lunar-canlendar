// Version: 2025-10-25 15:23 - iOS optimized
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, SelectControl, TextareaControl } from '@wordpress/components';
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
            showIcons,
            showTodayButton,
            gregorianIconHtml,
            lunarIconHtml
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
                    <PanelBody title={__('Gregorian Calendar', 'lunar-calendar')} initialOpen={true}>
                        <RangeControl
                            label={__('Current day font size', 'lunar-calendar')}
                            value={gregorianDayFontSize}
                            onChange={(value) => setAttributes({ gregorianDayFontSize: value })}
                            min={1}
                            max={5}
                            step={0.1}
                        />
                        {showIcons && (
                            <>
                                <SelectControl
                                    label={__('Icon', 'lunar-calendar')}
                                    value={gregorianIcon}
                                    options={iconOptions}
                                    onChange={(value) => setAttributes({ gregorianIcon: value })}
                                />
                                <TextareaControl
                                    label={__('Custom Icon HTML/SVG (priority)', 'lunar-calendar')}
                                    help={__('Paste HTML of font icon or SVG. If present, will override the dropdown icon above.', 'lunar-calendar')}
                                    value={gregorianIconHtml}
                                    onChange={(value) => setAttributes({ gregorianIconHtml: value })}
                                    rows={4}
                                />
                            </>
                        )}
                    </PanelBody>
                    <PanelBody title={__('Lunar Calendar', 'lunar-calendar')} initialOpen={true}>
                        <RangeControl
                            label={__('Current day font size', 'lunar-calendar')}
                            value={lunarDayFontSize}
                            onChange={(value) => setAttributes({ lunarDayFontSize: value })}
                            min={1}
                            max={5}
                            step={0.1}
                        />
                        {showIcons && (
                            <>
                                <SelectControl
                                    label={__('Icon', 'lunar-calendar')}
                                    value={lunarIcon}
                                    options={iconOptions}
                                    onChange={(value) => setAttributes({ lunarIcon: value })}
                                />
                                <TextareaControl
                                    label={__('Custom Icon HTML/SVG (priority)', 'lunar-calendar')}
                                    help={__('Paste HTML of font icon or SVG. If present, will override the dropdown icon above.', 'lunar-calendar')}
                                    value={lunarIconHtml}
                                    onChange={(value) => setAttributes({ lunarIconHtml: value })}
                                    rows={4}
                                />
                            </>
                        )}
                    </PanelBody>
                    <PanelBody title={__('Display Options', 'lunar-calendar')} initialOpen={false}>
                        <ToggleControl
                            label={__('Show icons', 'lunar-calendar')}
                            checked={showIcons}
                            onChange={(value) => setAttributes({ showIcons: value })}
                        />
                        <ToggleControl
                            label={__('Show "Today" button', 'lunar-calendar')}
                            checked={showTodayButton}
                            onChange={(value) => setAttributes({ showTodayButton: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...props} style={customStyles}>
                    {/* HTML preview lịch âm dương (có thể rút gọn, hoặc lấy từ calendar-html.php nếu cần) */}
                    <div ref={calendarRef}>
                    <div className="lunar-calendar-header">
                        <h1>{__('Lunar Calendar', 'lunar-calendar')}</h1>
                        <p>{__('Vietnamese Lunar Calendar Lookup', 'lunar-calendar')}</p>
                    </div>
                    <div className="lunar-current-date-section">
                        <div className="lunar-date-nav-buttons-wrapper lunar-date-nav-buttons-top">
                            <button className="lunar-date-nav-btn" id="prev-day-btn" title={__('Previous Day', 'lunar-calendar')}>
                                <i className="fas fa-chevron-left"></i>
                                <span className="lunar-date-nav-text">{__('Previous', 'lunar-calendar')}</span>
                            </button>
                        </div>
                        <div className="lunar-date-columns-wrapper">
                        <div className="lunar-date-column lunar-date-column-gregorian">
                            <div className="lunar-date-label">
                                {showIcons && (
                                    gregorianIconHtml ? (
                                        <span dangerouslySetInnerHTML={{ __html: gregorianIconHtml }}></span>
                                    ) : (
                                        <i className={`fas fa-${gregorianIcon}`}></i>
                                    )
                                )}
                                {__('Gregorian Calendar', 'lunar-calendar')}
                            </div>
                            <div className="lunar-date-number" id="current-gregorian-day">08</div>
                            <div className="lunar-date-month-year" id="current-gregorian-month-year">{__('Month 08 2025', 'lunar-calendar')}</div>
                            <div className="lunar-date-day" id="current-gregorian-day-name">{__('Friday', 'lunar-calendar')}</div>
                        </div>
                        <div className="lunar-date-column lunar-date-column-lunar">
                            <div className="lunar-date-label">
                                {showIcons && (
                                    lunarIconHtml ? (
                                        <span dangerouslySetInnerHTML={{ __html: lunarIconHtml }}></span>
                                    ) : (
                                        <i className={`fas fa-${lunarIcon}`}></i>
                                    )
                                )}
                                {__('Lunar Calendar', 'lunar-calendar')}
                            </div>
                            <div className="lunar-date-number" id="current-lunar-day">15</div>
                            <div className="lunar-date-month-year" id="current-lunar-month-year">{__('Month 06 Year At Ty', 'lunar-calendar')}</div>
                            <div className="lunar-info" id="current-lunar-details">{__('Day Ky Dau - Month Quy Mui', 'lunar-calendar')}</div>
                        </div>
                        </div>
                        <div className="lunar-date-nav-buttons-wrapper lunar-date-nav-buttons-bottom">
                            <button className="lunar-date-nav-btn" id="next-day-btn" title={__('Next Day', 'lunar-calendar')}>
                                <span className="lunar-date-nav-text">{__('Next', 'lunar-calendar')}</span>
                                <i className="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div className="lunar-holiday-info">
                        <div className="lunar-holiday-title">{__('Today\'s Holiday Information', 'lunar-calendar')}</div>
                        <div className="lunar-holiday-content" id="holiday-info">{__('None', 'lunar-calendar')}</div>
                    </div>
                    <div className="lunar-calendar-nav">
                        <div>
                            <button className="lunar-nav-arrow" id="prev-month">
                                <i className="fas fa-chevron-left"></i>
                            </button>
                            <div className="lunar-current-month-year" id="current-month-year">{__('Month 8 - 2025', 'lunar-calendar')}</div>
                            <button className="lunar-nav-arrow" id="next-month">
                                <i className="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div className="lunar-month-year-selectors">
                            <select id="month-selector">
                                <option value="1">{__('Month 1', 'lunar-calendar')}</option>
                                <option value="2">{__('Month 2', 'lunar-calendar')}</option>
                                <option value="3">{__('Month 3', 'lunar-calendar')}</option>
                                <option value="4">{__('Month 4', 'lunar-calendar')}</option>
                                <option value="5">{__('Month 5', 'lunar-calendar')}</option>
                                <option value="6">{__('Month 6', 'lunar-calendar')}</option>
                                <option value="7">{__('Month 7', 'lunar-calendar')}</option>
                                <option value="8" selected>{__('Month 8', 'lunar-calendar')}</option>
                                <option value="9">{__('Month 9', 'lunar-calendar')}</option>
                                <option value="10">{__('Month 10', 'lunar-calendar')}</option>
                                <option value="11">{__('Month 11', 'lunar-calendar')}</option>
                                <option value="12">{__('Month 12', 'lunar-calendar')}</option>
                            </select>
                            <select id="year-selector">
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025" selected>2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                            <button className="lunar-view-btn" id="view-btn">{__('View', 'lunar-calendar')}</button>
                            {showTodayButton && <button className="lunar-today-btn" id="today-btn">{__('Today', 'lunar-calendar')}</button>}
                        </div>
                    </div>
                    <div id="loading-indicator" className="lunar-loading-indicator" style={{ display: 'none' }}>
                        <div className="lunar-loading-spinner"></div>
                        <span className="lunar-loading-text">{__('Loading data...', 'lunar-calendar')}</span>
                    </div>
                    <div id="page-loading-overlay" className="lunar-page-loading-overlay" style={{ display: 'none' }}>
                        <div className="lunar-page-loading-content">
                            <div className="lunar-page-loading-spinner"></div>
                        </div>
                    </div>
                    <div className="lunar-calendar-grid">
                        <div className="lunar-weekdays">
                            <div className="lunar-weekday">{__('Monday', 'lunar-calendar')}</div>
                            <div className="lunar-weekday">{__('Tuesday', 'lunar-calendar')}</div>
                            <div className="lunar-weekday">{__('Wednesday', 'lunar-calendar')}</div>
                            <div className="lunar-weekday">{__('Thursday', 'lunar-calendar')}</div>
                            <div className="lunar-weekday">{__('Friday', 'lunar-calendar')}</div>
                            <div className="lunar-weekday">{__('Saturday', 'lunar-calendar')}</div>
                            <div className="lunar-weekday">{__('Sunday', 'lunar-calendar')}</div>
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

