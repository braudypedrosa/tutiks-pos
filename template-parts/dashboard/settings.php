<?php
/**
 * Template part for displaying the settings page
 *
 * @package TutiksPOS
 */

// Include WordPress file handling functions
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

// Handle form submission
if (isset($_POST['tutiks_store_settings_nonce']) && wp_verify_nonce($_POST['tutiks_store_settings_nonce'], 'tutiks_store_settings')) {
    // Handle QR code image upload
    if (!empty($_FILES['qr_code_image']['tmp_name'])) {
        $uploaded_file = $_FILES['qr_code_image'];
        
        // Check if image (and handle error)
        $wp_filetype = wp_check_filetype_and_ext($uploaded_file['tmp_name'], $uploaded_file['name']);
        if (!$wp_filetype['type'] || !preg_match('!^image/!', $wp_filetype['type'])) {
            $upload_error = 'Please upload a valid image file.';
        } else {
            // Process the upload
            $upload = wp_handle_upload($uploaded_file, array('test_form' => false));
            if (!isset($upload['error'])) {
                update_option('tutiks_pos_qr_code_url', $upload['url']);
                $upload_success = 'QR code image updated successfully.';
            } else {
                $upload_error = $upload['error'];
            }
        }
    }

    // Save other settings
    if (isset($_POST['store_name'])) {
        update_option('tutiks_pos_store_name', sanitize_text_field($_POST['store_name']));
    }
    if (isset($_POST['store_address'])) {
        update_option('tutiks_pos_store_address', sanitize_textarea_field($_POST['store_address']));
    }
    if (isset($_POST['store_phone'])) {
        update_option('tutiks_pos_store_phone', sanitize_text_field($_POST['store_phone']));
    }
    
    $settings_updated = true;
}

// Get current settings
$qr_code_url = get_option('tutiks_pos_qr_code_url');
$store_name = get_option('tutiks_pos_store_name');
$store_address = get_option('tutiks_pos_store_address');
$store_phone = get_option('tutiks_pos_store_phone');
?>

<div class="settings-container">
    <?php if (isset($upload_error)): ?>
        <div class="alert alert-danger">
            <?php echo esc_html($upload_error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($upload_success)): ?>
        <div class="alert alert-success">
            <?php echo esc_html($upload_success); ?>
        </div>
    <?php endif; ?>

    <div class="settings-container">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-qrcode me-2"></i>
                <h3 class="card-title mb-0">QR Code Payment Settings</h3>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" class="settings-form">
                    <?php wp_nonce_field('tutiks_store_settings', 'tutiks_store_settings_nonce'); ?>
                    
                    <?php if ($qr_code_url): ?>
                        <div class="current-qr">
                            <h4 class="section-title">Current QR Code</h4>
                            <div class="qr-preview">
                                <img src="<?php echo esc_url($qr_code_url); ?>" 
                                     alt="QR Code">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="upload-section">
                        <h4 class="section-title">Upload New QR Code</h4>
                        <div class="upload-area">
                            <div class="upload-instructions">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag and drop</p>
                                <span>Supported formats: PNG, JPG, JPEG</span>
                            </div>
                            <input type="file" id="qr_code_image" name="qr_code_image" 
                                   class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save QR Code
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    .settings-container {
        max-width: 600px;
        margin: 0 auto;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e9ecef;
    }

    .card-header i {
        font-size: 1.25rem;
        color: #007bff;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 500;
        color: #495057;
        margin-bottom: 1rem;
    }

    .current-qr {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e9ecef;
    }

    .qr-preview {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        text-align: center;
    }

    .qr-preview img {
        max-width: 200px;
        height: auto;
    }

    .upload-area {
        position: relative;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        background: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .upload-area:hover {
        border-color: #007bff;
        background: #fff;
    }

    .upload-instructions {
        pointer-events: none;
    }

    .upload-instructions i {
        font-size: 2.5rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .upload-instructions p {
        margin: 0 0 0.5rem;
        font-size: 1rem;
        color: #495057;
    }

    .upload-instructions span {
        font-size: 0.875rem;
        color: #6c757d;
    }

    input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .form-actions {
        margin-top: 2rem;
        text-align: right;
    }

    .btn-primary {
        padding: 0.5rem 1.5rem;
        font-weight: 500;
    }

    .alert {
        margin-bottom: 1.5rem;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Handle drag and drop styling
        const $uploadArea = $('.upload-area');
        const $fileInput = $('#qr_code_image');

        $uploadArea.on('dragenter dragover', function(e) {
            e.preventDefault();
            $(this).css({
                'border-color': '#007bff',
                'background': '#fff'
            });
        });

        $uploadArea.on('dragleave drop', function(e) {
            e.preventDefault();
            $(this).css({
                'border-color': '#dee2e6',
                'background': '#f8f9fa'
            });
        });

        // Show file name when selected
        $fileInput.on('change', function() {
            const fileName = this.files[0]?.name;
            if (fileName) {
                $('.upload-instructions p').text(fileName);
            }
        });
    });
    </script>
</div>

<style>
.settings-container {
    max-width: 800px;
    margin: 0 auto;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.card {
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card-header {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    background-color: #f8f9fa;
}

.card-title {
    margin: 0;
    font-size: 1.1rem;
}

.card-body {
    padding: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out;
}

.form-control:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
}

.form-actions {
    margin-top: 2rem;
    text-align: right;
}

.btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    line-height: 1.5;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    cursor: pointer;
    border: 1px solid transparent;
    border-radius: 0.25rem;
    transition: all 0.15s ease-in-out;
}

.btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}

.me-2 {
    margin-right: 0.5rem;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}
</style>
