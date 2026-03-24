/**
 * TGS Login Customizer - Admin JavaScript
 * 
 * Handles settings form, color pickers, image upload, import/export, preview
 * 
 * @package TGS_Login_Customizer
 * @since 1.0.0
 */

(function($) {
    'use strict';

    var TGSLoginAdmin = {
        settings: {},
        defaults: {},
        isDirty: false,

        /**
         * Initialize
         */
        init: function() {
            if (typeof tgsLoginAdmin === 'undefined') return;

            this.settings = JSON.parse(JSON.stringify(tgsLoginAdmin.settings));
            this.defaults = tgsLoginAdmin.defaults;

            this.initTabs();
            this.initColorPickers();
            this.initImageUploads();
            this.initToggles();
            this.initFormBindings();
            this.initButtons();
            this.initConditionalFields();
            this.initImportExport();

            console.log('TGS Login Customizer Admin initialized');
        },

        // ========================================
        // TABS
        // ========================================
        initTabs: function() {
            var self = this;
            $(document).on('click', '.tgs-login-tab', function() {
                var tabId = $(this).data('tab');
                
                $('.tgs-login-tab').removeClass('active');
                $(this).addClass('active');

                $('.tgs-login-tab-content').removeClass('active');
                $('#tab-' + tabId).addClass('active');

                // Save active tab to localStorage
                localStorage.setItem('tgs_login_active_tab', tabId);
            });

            // Restore active tab
            var savedTab = localStorage.getItem('tgs_login_active_tab');
            if (savedTab && $('.tgs-login-tab[data-tab="' + savedTab + '"]').length) {
                $('.tgs-login-tab[data-tab="' + savedTab + '"]').click();
            }
        },

        // ========================================
        // COLOR PICKERS
        // ========================================
        initColorPickers: function() {
            var self = this;
            $('.tgs-color-picker').each(function() {
                $(this).wpColorPicker({
                    change: function(event, ui) {
                        var key = $(event.target).data('key');
                        var color = ui.color.toString();
                        self.updateSetting(key, color);
                    },
                    clear: function(event) {
                        var input = $(event.target).closest('.wp-picker-container').find('.tgs-color-picker');
                        var key = input.data('key');
                        self.updateSetting(key, '');
                    }
                });
            });
        },

        // ========================================
        // IMAGE UPLOADS
        // ========================================
        initImageUploads: function() {
            var self = this;

            $(document).on('click', '.tgs-upload-btn', function(e) {
                e.preventDefault();
                var button = $(this);
                var target = button.data('target');
                var key = button.data('key');
                var previewEl = button.closest('.tgs-image-upload').find('.tgs-image-preview');

                var frame = wp.media({
                    title: tgsLoginAdmin.i18n.choose_image,
                    multiple: false,
                    library: { type: 'image' },
                    button: { text: tgsLoginAdmin.i18n.use_image }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#' + target).val(attachment.url).trigger('change');
                    previewEl.html('<img src="' + attachment.url + '" alt="Preview" />');
                    self.updateSetting(key, attachment.url);
                });

                frame.open();
            });

            $(document).on('click', '.tgs-remove-image-btn', function(e) {
                e.preventDefault();
                var button = $(this);
                var target = button.data('target');
                var key = button.data('key');
                var previewEl = button.closest('.tgs-image-upload').find('.tgs-image-preview');

                $('#' + target).val('').trigger('change');
                previewEl.html('<span class="no-image">Chưa có hình ảnh</span>');
                self.updateSetting(key, '');
            });
        },

        // ========================================
        // TOGGLES
        // ========================================
        initToggles: function() {
            var self = this;
            $(document).on('change', '.tgs-toggle input', function() {
                var key = $(this).data('key');
                var value = $(this).is(':checked');
                self.updateSetting(key, value);
                self.updateConditionalFields();
            });
        },

        // ========================================
        // FORM BINDINGS
        // ========================================
        initFormBindings: function() {
            var self = this;

            // Text, number, select inputs
            $(document).on('change input', '.tgs-setting-field', function() {
                var key = $(this).data('key');
                var value = $(this).val();
                var type = $(this).attr('type');

                if (type === 'number') {
                    value = parseFloat(value) || 0;
                }

                self.updateSetting(key, value);
            });

            // Textarea
            $(document).on('change', '.tgs-setting-textarea', function() {
                var key = $(this).data('key');
                self.updateSetting(key, $(this).val());
            });
        },

        // ========================================
        // CONDITIONAL FIELDS
        // ========================================
        initConditionalFields: function() {
            this.updateConditionalFields();
        },

        updateConditionalFields: function() {
            var self = this;

            // Background type conditions
            var bgType = this.getSetting('background.type');
            $('.tgs-bg-color-fields').toggleClass('hidden', bgType !== 'color');
            $('.tgs-bg-image-fields').toggleClass('hidden', bgType !== 'image');
            $('.tgs-bg-gradient-fields').toggleClass('hidden', bgType !== 'gradient');
            $('.tgs-bg-overlay-fields').toggleClass('hidden', bgType !== 'image');

            // Auto login conditions
            var autoEnabled = this.getSetting('auto_login.enabled');
            $('.tgs-auto-login-fields').toggleClass('hidden', !autoEnabled);

            // Prefill only condition
            var prefillOnly = this.getSetting('auto_login.prefill_only');
            $('.tgs-auto-submit-fields').toggleClass('hidden', prefillOnly || !autoEnabled);
        },

        // ========================================
        // BUTTONS
        // ========================================
        initButtons: function() {
            var self = this;

            // Save
            $(document).on('click', '.tgs-btn-save', function(e) {
                e.preventDefault();
                self.saveSettings();
            });

            // Reset
            $(document).on('click', '.tgs-btn-reset', function(e) {
                e.preventDefault();
                if (confirm(tgsLoginAdmin.i18n.reset_confirm)) {
                    self.resetSettings();
                }
            });

            // Preview
            $(document).on('click', '.tgs-btn-preview', function(e) {
                e.preventDefault();
                self.openPreview();
            });

            // Keyboard shortcut: Ctrl+S to save
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    self.saveSettings();
                }
            });
        },

        // ========================================
        // IMPORT/EXPORT
        // ========================================
        initImportExport: function() {
            var self = this;

            // Export
            $(document).on('click', '#tgs-export-btn', function(e) {
                e.preventDefault();
                self.exportSettings();
            });

            // Copy export
            $(document).on('click', '#tgs-copy-export', function(e) {
                e.preventDefault();
                var textarea = $('#tgs-export-data');
                textarea.select();
                document.execCommand('copy');
                self.showToast('Đã copy vào clipboard!', 'success');
            });

            // Download export
            $(document).on('click', '#tgs-download-export', function(e) {
                e.preventDefault();
                var data = $('#tgs-export-data').val();
                if (!data) {
                    self.showToast('Chưa có dữ liệu để tải.', 'error');
                    return;
                }
                var blob = new Blob([data], { type: 'application/json' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'tgs-login-settings-' + new Date().toISOString().slice(0,10) + '.json';
                a.click();
                URL.revokeObjectURL(url);
            });

            // Import from textarea
            $(document).on('click', '#tgs-import-btn', function(e) {
                e.preventDefault();
                var json = $('#tgs-import-data').val();
                if (!json.trim()) {
                    self.showToast('Vui lòng dán JSON vào ô bên trên.', 'error');
                    return;
                }
                if (confirm(tgsLoginAdmin.i18n.import_confirm)) {
                    self.importSettings(json);
                }
            });

            // Import from file
            $(document).on('click', '#tgs-import-file-btn', function(e) {
                e.preventDefault();
                $('#tgs-import-file-input').click();
            });

            $(document).on('change', '#tgs-import-file-input', function(e) {
                var file = e.target.files[0];
                if (!file) return;

                var reader = new FileReader();
                reader.onload = function(ev) {
                    var json = ev.target.result;
                    $('#tgs-import-data').val(json);
                    if (confirm(tgsLoginAdmin.i18n.import_confirm)) {
                        self.importSettings(json);
                    }
                };
                reader.readAsText(file);
            });
        },

        // ========================================
        // SETTINGS HELPERS
        // ========================================

        /**
         * Update a setting value using dot notation
         */
        updateSetting: function(key, value) {
            var keys = key.split('.');
            var ref = this.settings;

            for (var i = 0; i < keys.length - 1; i++) {
                if (!ref[keys[i]] || typeof ref[keys[i]] !== 'object') {
                    ref[keys[i]] = {};
                }
                ref = ref[keys[i]];
            }

            ref[keys[keys.length - 1]] = value;
            this.isDirty = true;
            this.updateSaveStatus();

            // Update conditional fields when certain keys change
            if (key === 'background.type' || key === 'auto_login.enabled' || key === 'auto_login.prefill_only') {
                this.updateConditionalFields();
            }
        },

        /**
         * Get a setting value using dot notation
         */
        getSetting: function(key) {
            var keys = key.split('.');
            var ref = this.settings;

            for (var i = 0; i < keys.length; i++) {
                if (ref && typeof ref === 'object' && keys[i] in ref) {
                    ref = ref[keys[i]];
                } else {
                    return undefined;
                }
            }

            return ref;
        },

        updateSaveStatus: function() {
            if (this.isDirty) {
                $('.tgs-save-status').html('<span style="color:#dba617;">⚠ Có thay đổi chưa lưu</span>');
            } else {
                $('.tgs-save-status').html('<span style="color:#00a32a;">✓ Đã lưu</span>');
            }
        },

        // ========================================
        // AJAX OPERATIONS
        // ========================================

        saveSettings: function() {
            var self = this;
            var $btn = $('.tgs-btn-save');

            $btn.prop('disabled', true).text(tgsLoginAdmin.i18n.saving);

            $.ajax({
                url: tgsLoginAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tgs_login_save_settings',
                    nonce: tgsLoginAdmin.nonce,
                    settings: JSON.stringify(self.settings)
                },
                success: function(res) {
                    if (res.success) {
                        self.settings = res.data.settings;
                        self.isDirty = false;
                        self.updateSaveStatus();
                        self.showToast(res.data.message, 'success');
                    } else {
                        self.showToast(res.data.message || tgsLoginAdmin.i18n.save_error, 'error');
                    }
                },
                error: function() {
                    self.showToast(tgsLoginAdmin.i18n.save_error, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('💾 Lưu cài đặt');
                }
            });
        },

        resetSettings: function() {
            var self = this;

            $.ajax({
                url: tgsLoginAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tgs_login_reset_settings',
                    nonce: tgsLoginAdmin.nonce
                },
                success: function(res) {
                    if (res.success) {
                        self.settings = res.data.settings;
                        self.isDirty = false;
                        self.showToast(res.data.message, 'success');
                        // Reload page to update form
                        location.reload();
                    } else {
                        self.showToast(res.data.message, 'error');
                    }
                }
            });
        },

        exportSettings: function() {
            var self = this;

            $.ajax({
                url: tgsLoginAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tgs_login_export_settings',
                    nonce: tgsLoginAdmin.nonce
                },
                success: function(res) {
                    if (res.success) {
                        $('#tgs-export-data').val(res.data.json);
                        self.showToast(tgsLoginAdmin.i18n.export_success, 'success');
                    }
                }
            });
        },

        importSettings: function(json) {
            var self = this;

            $.ajax({
                url: tgsLoginAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tgs_login_import_settings',
                    nonce: tgsLoginAdmin.nonce,
                    import_data: json
                },
                success: function(res) {
                    if (res.success) {
                        self.settings = res.data.settings;
                        self.isDirty = false;
                        self.showToast(res.data.message, 'success');
                        location.reload();
                    } else {
                        self.showToast(res.data.message || tgsLoginAdmin.i18n.import_error, 'error');
                    }
                },
                error: function() {
                    self.showToast(tgsLoginAdmin.i18n.import_error, 'error');
                }
            });
        },

        openPreview: function() {
            var url = tgsLoginAdmin.loginUrl;
            url += (url.indexOf('?') > -1 ? '&' : '?') + 'tgs_preview=1&t=' + Date.now();
            window.open(url, '_blank', 'width=1200,height=800');
        },

        // ========================================
        // TOAST NOTIFICATIONS
        // ========================================
        showToast: function(message, type) {
            type = type || 'info';
            var $toast = $('<div class="tgs-toast ' + type + '">' + message + '</div>');
            $('body').append($toast);

            setTimeout(function() {
                $toast.addClass('show');
            }, 10);

            setTimeout(function() {
                $toast.removeClass('show');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }, 3500);
        }
    };

    // Initialize when ready
    $(document).ready(function() {
        TGSLoginAdmin.init();
    });

})(jQuery);
