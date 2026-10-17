(function (wp) {
    'use strict';

    if (!wp || !wp.blocks || !wp.element || !wp.components || !wp.blockEditor) {
        return;
    }

    var el = wp.element.createElement;
    var __ = wp.i18n.__;
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;
    var TextareaControl = wp.components.TextareaControl;
    var SelectControl = wp.components.SelectControl;
    var ToggleControl = wp.components.ToggleControl;
    var Button = wp.components.Button;

    var FIELD_TYPES = [
        { label: __('First Name', 'wpcalibrate-popups-lead-capture'), value: 'first_name' },
        { label: __('Last Name', 'wpcalibrate-popups-lead-capture'), value: 'last_name' },
        { label: __('Full Name', 'wpcalibrate-popups-lead-capture'), value: 'full_name' },
        { label: __('Email Address', 'wpcalibrate-popups-lead-capture'), value: 'email' },
        { label: __('Telephone', 'wpcalibrate-popups-lead-capture'), value: 'tel' },
        { label: __('Text Field', 'wpcalibrate-popups-lead-capture'), value: 'text' },
        { label: __('Textarea', 'wpcalibrate-popups-lead-capture'), value: 'textarea' },
        { label: __('Number', 'wpcalibrate-popups-lead-capture'), value: 'number' },
        { label: __('Dropdown Select', 'wpcalibrate-popups-lead-capture'), value: 'select' },
        { label: __('Radio Buttons', 'wpcalibrate-popups-lead-capture'), value: 'radio' },
        { label: __('Checkbox Group', 'wpcalibrate-popups-lead-capture'), value: 'checkbox_group' },
        { label: __('Single Checkbox', 'wpcalibrate-popups-lead-capture'), value: 'checkbox' },
        { label: __('Consent Checkbox', 'wpcalibrate-popups-lead-capture'), value: 'consent' },
        { label: __('Hidden Field', 'wpcalibrate-popups-lead-capture'), value: 'hidden' }
    ];

    registerBlockType('wpcalibrate/lead-form', {
        title: __('Popup Lead Form', 'wpcalibrate-popups-lead-capture'),
        icon: 'feedback',
        category: 'widgets',
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var fields = attributes.fields || [];
            var selectedIndexState = wp.element.useState(0);
            var selectedIndex = selectedIndexState[0];
            var setSelectedIndex = selectedIndexState[1];

            var updateField = function (index, updatedProps) {
                var newFields = fields.slice();
                newFields[index] = Object.assign({}, newFields[index], updatedProps);
                setAttributes({ fields: newFields });
            };

            var addField = function (type) {
                var newField = {
                    type: type,
                    key: type + '_' + Date.now().toString(36).substr(-4),
                    label: type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' '),
                    placeholder: '',
                    required: type === 'email',
                    helpText: '',
                    options: 'Option 1\nOption 2\nOption 3',
                    width: '100'
                };

                if (type === 'consent') {
                    newField.label = __('I consent to the terms and privacy policy.', 'wpcalibrate-popups-lead-capture');
                    newField.required = true;
                }

                var newFields = fields.concat([newField]);
                setAttributes({ fields: newFields });
                setSelectedIndex(newFields.length - 1);
            };

            var removeField = function (index) {
                var newFields = fields.filter(function (_, i) { return i !== index; });
                setAttributes({ fields: newFields });
                if (selectedIndex >= newFields.length) {
                    setSelectedIndex(Math.max(0, newFields.length - 1));
                }
            };

            var moveField = function (fromIndex, toIndex) {
                if (toIndex < 0 || toIndex >= fields.length) return;
                var newFields = fields.slice();
                var item = newFields.splice(fromIndex, 1)[0];
                newFields.splice(toIndex, 0, item);
                setAttributes({ fields: newFields });
                setSelectedIndex(toIndex);
            };

            var activeField = fields[selectedIndex];

            return el('div', { className: 'wpcal-lead-form-editor-wrapper' },
                // Sidebar Settings
                el(InspectorControls, null,
                    el(PanelBody, { title: __('Form Button & Messages', 'wpcalibrate-popups-lead-capture'), initialOpen: false },
                        el(TextControl, {
                            label: __('Submit Button Text', 'wpcalibrate-popups-lead-capture'),
                            value: attributes.submitButtonText,
                            onChange: function (val) { setAttributes({ submitButtonText: val }); }
                        }),
                        el(TextControl, {
                            label: __('Button Background Color', 'wpcalibrate-popups-lead-capture'),
                            value: attributes.submitButtonColor,
                            onChange: function (val) { setAttributes({ submitButtonColor: val }); }
                        }),
                        el(TextControl, {
                            label: __('Button Text Color', 'wpcalibrate-popups-lead-capture'),
                            value: attributes.submitButtonTextColor,
                            onChange: function (val) { setAttributes({ submitButtonTextColor: val }); }
                        }),
                        el(TextareaControl, {
                            label: __('Success Message', 'wpcalibrate-popups-lead-capture'),
                            value: attributes.successMessage,
                            onChange: function (val) { setAttributes({ successMessage: val }); }
                        })
                    ),
                    activeField && el(PanelBody, { title: __('Selected Field Settings', 'wpcalibrate-popups-lead-capture'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Field Type', 'wpcalibrate-popups-lead-capture'),
                            value: activeField.type,
                            options: FIELD_TYPES,
                            onChange: function (val) { updateField(selectedIndex, { type: val }); }
                        }),
                        el(TextControl, {
                            label: __('Field Label', 'wpcalibrate-popups-lead-capture'),
                            value: activeField.label || '',
                            onChange: function (val) { updateField(selectedIndex, { label: val }); }
                        }),
                        el(TextControl, {
                            label: __('Field Key (Identifier)', 'wpcalibrate-popups-lead-capture'),
                            value: activeField.key || '',
                            onChange: function (val) { updateField(selectedIndex, { key: val }); }
                        }),
                        activeField.type !== 'hidden' && activeField.type !== 'consent' && activeField.type !== 'checkbox' && el(TextControl, {
                            label: __('Placeholder', 'wpcalibrate-popups-lead-capture'),
                            value: activeField.placeholder || '',
                            onChange: function (val) { updateField(selectedIndex, { placeholder: val }); }
                        }),
                        activeField.type !== 'hidden' && el(TextControl, {
                            label: __('Help Text', 'wpcalibrate-popups-lead-capture'),
                            value: activeField.helpText || '',
                            onChange: function (val) { updateField(selectedIndex, { helpText: val }); }
                        }),
                        (activeField.type === 'select' || activeField.type === 'radio' || activeField.type === 'checkbox_group') && el(TextareaControl, {
                            label: __('Options (One per line)', 'wpcalibrate-popups-lead-capture'),
                            value: activeField.options || '',
                            help: __('Enter each option on a new line. You can also specify value:label (e.g., "us:United States")', 'wpcalibrate-popups-lead-capture'),
                            onChange: function (val) { updateField(selectedIndex, { options: val }); }
                        }),
                        el(SelectControl, {
                            label: __('Field Width', 'wpcalibrate-popups-lead-capture'),
                            value: activeField.width || '100',
                            options: [
                                { label: '100% (Full Width)', value: '100' },
                                { label: '50% (Half Width)', value: '50' },
                                { label: '33% (One Third)', value: '33' }
                            ],
                            onChange: function (val) { updateField(selectedIndex, { width: val }); }
                        }),
                        el(ToggleControl, {
                            label: __('Required Field', 'wpcalibrate-popups-lead-capture'),
                            checked: !!activeField.required,
                            onChange: function (val) { updateField(selectedIndex, { required: val }); }
                        }),
                        el('div', { style: { display: 'flex', gap: '8px', marginTop: '15px' } },
                            el(Button, {
                                isDestructive: true,
                                variant: 'secondary',
                                onClick: function () { removeField(selectedIndex); }
                            }, __('Remove Field', 'wpcalibrate-popups-lead-capture')),
                            el(Button, {
                                variant: 'secondary',
                                disabled: selectedIndex === 0,
                                onClick: function () { moveField(selectedIndex, selectedIndex - 1); }
                            }, '↑'),
                            el(Button, {
                                variant: 'secondary',
                                disabled: selectedIndex === fields.length - 1,
                                onClick: function () { moveField(selectedIndex, selectedIndex + 1); }
                            }, '↓')
                        )
                    )
                ),

                // Canvas Preview
                el('div', { className: 'wpcal-lead-form-preview', style: { padding: '16px', background: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '8px' } },
                    el('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '14px', borderBottom: '1px solid #cbd5e1', paddingBottom: '10px' } },
                        el('h4', { style: { margin: 0, fontSize: '15px', color: '#0f172a' } }, __('Popup Lead Form Builder', 'wpcalibrate-popups-lead-capture')),
                        el('div', { style: { display: 'flex', gap: '8px', alignItems: 'center' } },
                            el('span', { style: { fontSize: '12px', color: '#64748b' } }, __('Add Field:', 'wpcalibrate-popups-lead-capture')),
                            el(SelectControl, {
                                value: '',
                                options: [{ label: __('+ Select field to add...', 'wpcalibrate-popups-lead-capture'), value: '' }].concat(FIELD_TYPES),
                                onChange: function (val) {
                                    if (val) {
                                        addField(val);
                                    }
                                }
                            })
                        )
                    ),

                    el('div', { className: 'wpcal-form-fields-grid', style: { display: 'flex', flexWrap: 'wrap', gap: '12px' } },
                        fields.map(function (field, idx) {
                            var isSelected = idx === selectedIndex;
                            var widthPct = field.width === '50' ? 'calc(50% - 6px)' : (field.width === '33' ? 'calc(33.333% - 8px)' : '100%');

                            return el('div', {
                                key: field.key || idx,
                                onClick: function () { setSelectedIndex(idx); },
                                style: {
                                    width: widthPct,
                                    padding: '10px',
                                    border: isSelected ? '2px solid #2563eb' : '1px dashed #cbd5e1',
                                    background: isSelected ? '#eff6ff' : '#ffffff',
                                    borderRadius: '6px',
                                    cursor: 'pointer',
                                    position: 'relative'
                                }
                            },
                                el('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '6px' } },
                                    el('strong', { style: { fontSize: '13px', color: '#1e293b' } },
                                        field.label,
                                        field.required && el('span', { style: { color: '#dc2626', marginLeft: '3px' } }, '*')
                                    ),
                                    el('span', { style: { fontSize: '10px', background: '#e2e8f0', padding: '2px 6px', borderRadius: '4px', textTransform: 'uppercase' } }, field.type)
                                ),
                                field.type === 'hidden' ? el('div', { style: { fontSize: '11px', color: '#94a3b8', fontStyle: 'italic' } }, __('(Hidden Field: Value passed programmatically)', 'wpcalibrate-popups-lead-capture')) :
                                field.type === 'textarea' ? el('div', { style: { height: '36px', background: '#f1f5f9', border: '1px solid #cbd5e1', borderRadius: '4px' } }) :
                                field.type === 'consent' || field.type === 'checkbox' ? el('div', { style: { display: 'flex', alignItems: 'center', gap: '6px', fontSize: '12px' } }, el('input', { type: 'checkbox', disabled: true }), field.label) :
                                el('div', { style: { height: '30px', background: '#f1f5f9', border: '1px solid #cbd5e1', borderRadius: '4px', display: 'flex', alignItems: 'center', padding: '0 8px', color: '#94a3b8', fontSize: '12px' } },
                                    field.placeholder || __('Text placeholder...', 'wpcalibrate-popups-lead-capture')
                                )
                            );
                        })
                    ),

                    el('div', { style: { marginTop: '16px', textAlign: 'center' } },
                        el('button', {
                            type: 'button',
                            style: {
                                background: attributes.submitButtonColor || '#2563eb',
                                color: attributes.submitButtonTextColor || '#ffffff',
                                border: 'none',
                                borderRadius: '6px',
                                padding: '10px 24px',
                                fontWeight: '600',
                                fontSize: '14px',
                                cursor: 'pointer'
                            }
                        }, attributes.submitButtonText || __('Subscribe', 'wpcalibrate-popups-lead-capture'))
                    )
                )
            );
        },
        save: function () {
            // Rendered dynamically server-side via render_callback for maximum security and flexibility
            return null;
        }
    });
})(window.wp);
