<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_assets() {
	$timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/style.css' ) );

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), $timestamp, 'all' );


}
add_action( 'wp_enqueue_scripts', 'child_enqueue_assets', 15 );


/**
 * Next Chapter Navigation for Course Pages
 */

// チャプターNextナビ用：チャプター順リスト定義
function get_chapter_order_list() {
    // 例：ライトコース用チャプターID順
    return array(
        316, // Photoshop基礎・応用 (投稿ID)
        9, // Figma基礎・応用 (投稿ID)
        326  // 案件獲得サポート (投稿ID)
    );
}

// チャプター画面に「次のチャプターへ進む」ボタン追加
function add_next_chapter_button() {
    if (is_singular('sfwd-courses')) {
        $chapter_order = get_chapter_order_list();
        $current_id = get_the_ID();
        $current_index = array_search($current_id, $chapter_order);
        if ($current_index !== false && isset($chapter_order[$current_index + 1])) {
            $next_id = $chapter_order[$current_index + 1];
            $next_url = get_permalink($next_id);
            echo '<div class="next-chapter-nav" style="margin: 2em 0; text-align: center;">';
            echo '<a href="' . esc_url($next_url) . '" class="button">次のチャプターへ進む</a>';
            echo '</div>';
        }
    }
}
add_action('astra_primary_content_bottom', 'add_next_chapter_button');

// セクション（レッスン）最終にも「次のチャプターへ進む」ボタン追加
function add_next_chapter_from_last_lesson() {
    global $post;
    $course_id = learndash_get_course_id($post);
    $lesson_list = learndash_get_course_lessons_list($course_id);
    $last_lesson_id = end($lesson_list)['post']->ID ?? null;
    if (get_the_ID() == $last_lesson_id) {
        $chapter_order = get_chapter_order_list();
        $current_index = array_search($course_id, $chapter_order);
        if ($current_index !== false && isset($chapter_order[$current_index + 1])) {
            $next_id = $chapter_order[$current_index + 1];
            $next_url = get_permalink($next_id);
            echo '<div class="next-chapter-nav" style="margin: 2em 0; text-align: center;">';
            echo '<a href="' . esc_url($next_url) . '" class="button">次のチャプターへ進む</a>';
            echo '</div>';
        }
    }
}
add_action('learndash-lesson-content-bottom', 'add_next_chapter_from_last_lesson');


/*--------------------------------------------------------
 固定ページのスラッグ（ディレクトリ名）指定でショートコードを作成
--------------------------------------------------------*/

function show_page_content_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'id' => 0,
        'slug' => '',
    ), $atts, 'page_content' );

    $page = null;

    if ( ! empty( $atts['slug'] ) ) {
        $page = get_page_by_path( sanitize_title( $atts['slug'] ) );
    } elseif ( intval( $atts['id'] ) > 0 ) {
        $page = get_post( intval( $atts['id'] ) );
    }

    if ( ! $page || $page->post_status !== 'publish' ) {
        return '<!-- Page not found or not published -->';
    }

    $content = apply_filters( 'the_content', $page->post_content );

    return $content;
}

add_shortcode( 'page_content', 'show_page_content_shortcode' );

/*--------------------------------------------------------
 ダウンロードボックス
--------------------------------------------------------*/
function my_download_box_shortcode( $atts ) {
    // 引数（url, title, size など）を設定
    $atts = shortcode_atts( array(
        'url'   => '',
        'title' => '',
        'size'  => ''
    ), $atts, 'my-download' );

    // URLから添付ファイルIDを取得
    $attachment_id = attachment_url_to_postid($atts['url']);
    
    if ($attachment_id) {
        // メディア情報を取得
        $attachment = get_post($attachment_id);
        
        // タイトルが指定されていない場合は代替テキストを使用
        if (empty($atts['title'])) {
            $atts['title'] = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            // 代替テキストも空の場合はファイル名を使用
            if (empty($atts['title'])) {
                $atts['title'] = $attachment->post_title;
            }
        }
        
        // サイズが指定されていない場合はファイルサイズを自動計算
        if (empty($atts['size'])) {
            $file_size = filesize(get_attached_file($attachment_id));
            if ($file_size) {
                $atts['size'] = size_format($file_size, 2);
            }
        }
    }

    // ファイルの拡張子を取得
    $file_extension = strtolower(pathinfo($atts['url'], PATHINFO_EXTENSION));
    
    // ファイルタイプに応じたアイコンを設定
    $icon_path = get_stylesheet_directory_uri() . '/images/';
    $icon_file = 'svg.svg'; // デフォルトアイコン
    
    if ($file_extension === 'pdf') {
        $icon_file = 'pdf.svg';
    } elseif (in_array($file_extension, ['jpg', 'jpeg'])) {
        $icon_file = 'jpg.svg';
    } elseif ($file_extension === 'png') {
        $icon_file = 'png.svg';
    } elseif ($file_extension === 'svg') {
        $icon_file = 'svg.svg';
    } elseif (in_array($file_extension, ['doc', 'docx'])) {
        $icon_file = 'doc.svg';
    } elseif (in_array($file_extension, ['xls', 'xlsx'])) {
        $icon_file = 'xls.svg';
    } elseif (in_array($file_extension, ['ppt', 'pptx'])) {
        $icon_file = 'pptx.svg';
    } elseif ($file_extension === 'txt') {
        $icon_file = 'txt.svg';
    } elseif ($file_extension === 'psd') {
        $icon_file = 'psd.svg';
    } elseif ($file_extension === 'zip') {
        $icon_file = 'zip.svg';
    } elseif ($file_extension === 'csv') {
        $icon_file = 'csv.svg';
    }

    // HTMLを返す
    return '
    <div class="my-download-box">
        <div class="my-download-box__content">
            <img src="' . esc_url($icon_path . $icon_file) . '" alt="ファイルアイコン" class="my-download-box__icon">
            <div>
                <div class="my-download-box__title">' . esc_html( $atts['title'] ) . '</div>
                <div class="my-download-box__size">' . esc_html( $atts['size'] ) . '</div>
            </div>
        </div>
        <div>
            <a href="' . esc_url( $atts['url'] ) . '" download class="my-download-btn">ダウンロード</a>
        </div>
    </div>';
}
add_shortcode( 'my-download', 'my_download_box_shortcode' );


// Swiperアセット読み込み
function enqueue_swiper_assets() {
    // Swiperの基本アセットを常に読み込む
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), null, true);
    
    // カスタムCSSとJSを読み込む
    wp_enqueue_style('swiper-custom-css', get_stylesheet_directory_uri() . '/assets/css/swiper-custom.css');
    wp_enqueue_script('swiper-custom-js', get_stylesheet_directory_uri() . '/assets/js/swiper-custom.js', array('swiper-js'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_swiper_assets');

// Swiperスライダー用ショートコード
function swiper_slider_shortcode($atts, $content = null) {
    global $swiper_used;
    $swiper_used = true;

    $atts = shortcode_atts(array(
        'class' => '',
    ), $atts);

    $output  = '<div class="swiper-container ' . esc_attr($atts['class']) . '">';
    $output .= '<div class="swiper-wrapper">' . do_shortcode($content) . '</div>';
    $output .= '<div class="swiper-pagination"></div>';
    $output .= '<div class="swiper-button-next"></div>';
    $output .= '<div class="swiper-button-prev"></div>';
    $output .= '</div>';

    return $output;
}
add_shortcode('swiper_slider', 'swiper_slider_shortcode');

// Swiperスライド用ショートコード
function swiper_slide_shortcode($atts, $content = null) {
    return '<div class="swiper-slide">' . do_shortcode($content) . '</div>';
}
add_shortcode('swiper_slide', 'swiper_slide_shortcode');