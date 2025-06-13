<?php
/**
 * Modal template for download box
 */
?>
<div class="custom-modal">
    <div class="custom-modal-overlay"></div>
    <div class="custom-modal-content">
        <button class="custom-modal-close">&times;</button>
        <div class="custom-modal-inner">
            <div class="custom-modal-content-wrapper">
                <h3 class="custom-modal-title"><?php echo esc_html($title); ?></h3>
                <p class="custom-modal-size"><?php echo esc_html($size); ?></p>
                <a href="<?php echo esc_url($url); ?>" class="custom-modal-button" download>
                    <svg class="custom-download-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="none" d="M0 0h24v24H0z"/>
                        <path d="M13 16.171l5.364-5.364 1.414 1.414L12 20l-7.778-7.778 1.414-1.414L11 16.171V2h2v14.171z"/>
                    </svg>
                    ダウンロード
                </a>
            </div>
        </div>
    </div>
</div> 