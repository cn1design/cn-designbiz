<?php
/**
 * Download box template
 */
?>
<div class="custom-download-box">
    <div class="custom-download-box-inner">
        <div class="custom-download-box-content">
            <h3 class="custom-download-box-title"><?php echo esc_html($title); ?></h3>
            <p class="custom-download-box-size"><?php echo esc_html($size); ?></p>
        </div>
        <a href="<?php echo esc_url($url); ?>" class="custom-download-box-button" download>
            <svg class="custom-download-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path fill="none" d="M0 0h24v24H0z"/>
                <path d="M13 16.171l5.364-5.364 1.414 1.414L12 20l-7.778-7.778 1.414-1.414L11 16.171V2h2v14.171z"/>
            </svg>
            ダウンロード
        </a>
    </div>
</div>
