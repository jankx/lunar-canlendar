import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
    InspectorControls,
    useBlockProps,
} from '@wordpress/block-editor';
import {
    PanelBody,
    ToggleControl,
} from '@wordpress/components';
import './style.scss';

/**
 * Event Details Block
 * Hiển thị thông tin sự kiện từ Events Manager
 */
registerBlockType('jankx/event-details', {
    icon: 'calendar-alt',
    edit: ({ attributes, setAttributes }) => {
        const {
            showDate,
            showTime,
            showLocation,
            showOrganizer,
            showCategories,
            showBookingInfo,
            showBookingButton,
            bookingButtonText,
        } = attributes;

        const blockProps = useBlockProps({
            className: 'jankx-event-details',
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Display Settings', 'lunar-calendar')} initialOpen={true}>
                        <ToggleControl
                            label={__('Show Date', 'lunar-calendar')}
                            checked={showDate}
                            onChange={(value) => setAttributes({ showDate: value })}
                        />
                        <ToggleControl
                            label={__('Show Time', 'lunar-calendar')}
                            checked={showTime}
                            onChange={(value) => setAttributes({ showTime: value })}
                        />
                        <ToggleControl
                            label={__('Show Location', 'lunar-calendar')}
                            checked={showLocation}
                            onChange={(value) => setAttributes({ showLocation: value })}
                        />
                        <ToggleControl
                            label={__('Show Organizer', 'lunar-calendar')}
                            checked={showOrganizer}
                            onChange={(value) => setAttributes({ showOrganizer: value })}
                        />
                        <ToggleControl
                            label={__('Show Categories', 'lunar-calendar')}
                            checked={showCategories}
                            onChange={(value) => setAttributes({ showCategories: value })}
                        />
                    </PanelBody>

                    <PanelBody title={__('Booking Settings', 'lunar-calendar')} initialOpen={false}>
                        <ToggleControl
                            label={__('Show Booking Info', 'lunar-calendar')}
                            checked={showBookingInfo}
                            onChange={(value) => setAttributes({ showBookingInfo: value })}
                            help={__('Show available spaces, max per booking, deadline', 'lunar-calendar')}
                        />
                        <ToggleControl
                            label={__('Show Booking Button', 'lunar-calendar')}
                            checked={showBookingButton}
                            onChange={(value) => setAttributes({ showBookingButton: value })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div className="event-details-preview">
                        <h3 className="event-details-title">
                            {__('Thông tin Sự kiện', 'lunar-calendar')}
                        </h3>

                        {showDate && (
                            <div className="event-detail-item">
                                <span className="detail-icon">📅</span>
                                <div className="detail-content">
                                    <strong>{__('Thời gian:', 'lunar-calendar')}</strong>
                                    <p>{__('Sẽ hiển thị ngày và giờ sự kiện', 'lunar-calendar')}</p>
                                </div>
                            </div>
                        )}

                        {showTime && showDate && (
                            <div className="event-detail-item event-time-only">
                                <span className="detail-icon">🕐</span>
                                <div className="detail-content">
                                    <strong>{__('Giờ:', 'lunar-calendar')}</strong>
                                    <p>{__('Sẽ hiển thị giờ bắt đầu - kết thúc', 'lunar-calendar')}</p>
                                </div>
                            </div>
                        )}

                        {showLocation && (
                            <div className="event-detail-item">
                                <span className="detail-icon">📍</span>
                                <div className="detail-content">
                                    <strong>{__('Địa điểm:', 'lunar-calendar')}</strong>
                                    <p>{__('Sẽ hiển thị tên và địa chỉ địa điểm', 'lunar-calendar')}</p>
                                </div>
                            </div>
                        )}

                        {showOrganizer && (
                            <div className="event-detail-item">
                                <span className="detail-icon">👥</span>
                                <div className="detail-content">
                                    <strong>{__('Tổ chức:', 'lunar-calendar')}</strong>
                                    <p>{__('Sẽ hiển thị người/tổ chức tổ chức sự kiện', 'lunar-calendar')}</p>
                                </div>
                            </div>
                        )}

                        {showCategories && (
                            <div className="event-detail-item">
                                <span className="detail-icon">🏷️</span>
                                <div className="detail-content">
                                    <strong>{__('Danh mục:', 'lunar-calendar')}</strong>
                                    <p>{__('Sẽ hiển thị các danh mục sự kiện', 'lunar-calendar')}</p>
                                </div>
                            </div>
                        )}

                        {showBookingInfo && (
                            <div className="event-detail-item">
                                <span className="detail-icon">🎫</span>
                                <div className="detail-content">
                                    <strong>{__('Thông tin đăng ký:', 'lunar-calendar')}</strong>
                                    <p>{__('Sẽ hiển thị số chỗ còn trống, giới hạn đăng ký, hạn đăng ký', 'lunar-calendar')}</p>
                                </div>
                            </div>
                        )}

                        {showBookingButton && (
                            <div className="event-booking-button-preview">
                                <button type="button" className="event-booking-button">
                                    {bookingButtonText || __('Đăng ký tham gia', 'lunar-calendar')}
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </>
        );
    },
    save: () => {
        // Dynamic block - rendered via PHP
        return null;
    },
});

