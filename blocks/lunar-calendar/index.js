import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './style.css';

export default function Edit() {
    const props = useBlockProps({ className: 'lunar-calendar-container' });
    return (
        <div {...props}>
            <div style={{ padding: '20px' }}>{__('Lịch Âm Dương sẽ hiển thị ở frontend', 'buocchandisan')}</div>
        </div>
    );
}

export function save() {
    return null;
}

