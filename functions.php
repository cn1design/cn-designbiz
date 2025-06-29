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

/*
|--------------------------------------------------------------------------
| アセットファイルの読み込み
|--------------------------------------------------------------------------
|
| テーマで利用するCSSやJSファイルを読み込みます。
|
*/
function child_enqueue_assets() {
	$timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/style.css' ) );
	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), $timestamp, 'all' );
}
add_action( 'wp_enqueue_scripts', 'child_enqueue_assets', 15 );


/*
|--------------------------------------------------------------------------
| LearnDash：次のチャプターへのナビゲーション
|--------------------------------------------------------------------------
|
| コースページと最終レッスンページに「次のチャプターへ進む」ボタンを
| 表示するための機能です。
|
*/
function get_chapter_order_list() {
    // 例：ライトコース用チャプターID順
    return array(
        316, // Photoshop基礎・応用 (投稿ID)
        9, // Figma基礎・応用 (投稿ID)
        326  // 案件獲得サポート (投稿ID)
    );
}

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


/*
|--------------------------------------------------------------------------
| ショートコード：固定ページコンテンツの表示
|--------------------------------------------------------------------------
|
| 固定ページのスラッグを指定して、その内容を他の場所に埋め込むための
| ショートコード [page_content] を定義します。
|
*/
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


/*
|--------------------------------------------------------------------------
| ショートコード：ダウンロードボックス
|--------------------------------------------------------------------------
|
| ファイルへのリンクを装飾的なボックスで表示するための
| ショートコード [my-download] を定義します。
|
*/
function my_download_box_shortcode( $atts ) {
    // 引数（url, title, size など）を設定
    $atts = shortcode_atts( array(
        'url'   => '',
        'title' => '',
        'size'  => ''
    ), $atts, 'my-download' );

    // URLをhttpsに強制（ローカル環境は除外）
    if (
        strpos($atts['url'], 'http://localhost') === 0 ||
        strpos($atts['url'], 'http://127.0.0.1') === 0
    ) {
        $url = $atts['url'];
    } else {
        $url = str_replace('http://', 'https://', $atts['url']);
    }

    // URLから添付ファイルIDを取得
    $attachment_id = attachment_url_to_postid($url);
    
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
    $file_extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    
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
    } elseif ($file_extension === 'fig') {
        $icon_file = 'fig.svg';
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
            <a href="' . esc_url( $url ) . '" download class="my-download-btn">ダウンロード</a>
        </div>
    </div>';
}
add_shortcode( 'my-download', 'my_download_box_shortcode' );


/*
|--------------------------------------------------------------------------
| メディア：PSDファイルのアップロード許可
|--------------------------------------------------------------------------
|
| WordPressのメディアライブラリにPhotoshop(.psd)ファイルを
| アップロードできるように許可します。
|
*/
function allow_upload_psd( $mimes ) {
    $mimes[ 'psd' ] = 'image/vnd.adobe.photoshop';
    return $mimes;
}
add_filter( 'upload_mimes' , 'allow_upload_psd' );

/*
|--------------------------------------------------------------------------
| メディア：FIGファイルのアップロード許可
|--------------------------------------------------------------------------
|
| WordPressのメディアライブラリにFigma(.fig)ファイルを
| アップロードできるように許可します。
|
*/
function allow_upload_fig( $mimes ) {
    $mimes[ 'fig' ] = 'application/octet-stream';
    return $mimes;
}
add_filter( 'upload_mimes' , 'allow_upload_fig' );


/*
|--------------------------------------------------------------------------
| Swiper：アセット読み込みとショートコード
|--------------------------------------------------------------------------
|
| Swiperスライダーを利用するためのCSS/JSの読み込みと、
| [swiper_slider], [swiper_slide] ショートコードを定義します。
|
*/
function enqueue_swiper_assets() {
    // Swiperの基本アセットを常に読み込む
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), null, true);
    
    // カスタムCSSとJSを読み込む
    wp_enqueue_style('swiper-custom-css', get_stylesheet_directory_uri() . '/assets/css/swiper-custom.css');
    wp_enqueue_script('swiper-custom-js', get_stylesheet_directory_uri() . '/assets/js/swiper-custom.js', array('swiper-js'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_swiper_assets');

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

function swiper_slide_shortcode($atts, $content = null) {
    return '<div class="swiper-slide">' . do_shortcode($content) . '</div>';
}
add_shortcode('swiper_slide', 'swiper_slide_shortcode');


/*
|--------------------------------------------------------------------------
| LearnDash：テンプレートのオーバーライド
|--------------------------------------------------------------------------
|
| LearnDashのテンプレートファイルを子テーマ内のファイルで上書きするための
| 設定です。これにより、プラグインのアップデート時も変更が保持されます。
|
*/
function learndash_template_override_setup() {
    // テーマディレクトリのテンプレートを優先
    add_filter('learndash_template_paths', function($paths) {
        $theme_template_path = get_stylesheet_directory() . '/learndash/';
        // テーマパスを最初に追加
        array_unshift($paths, $theme_template_path);
        return $paths;
    }, 999); // 優先度を高く設定
}
add_action('init', 'learndash_template_override_setup', 1);

// デバッグ用：テンプレートパスを確認
function debug_learndash_template_path() {
    if (current_user_can('administrator') && is_singular('sfwd-topic')) {
        error_log('LearnDash Template Path: ' . get_stylesheet_directory() . '/learndash/ld30/templates/focus/index.php');
        error_log('File exists: ' . (file_exists(get_stylesheet_directory() . '/learndash/ld30/templates/focus/index.php') ? 'YES' : 'NO'));
    }
}
add_action('wp_footer', 'debug_learndash_template_path');

// LearnDashのテンプレートを完全にオーバーライド
function force_learndash_template_override($template) {
    // 管理画面ではこの処理を適用しない
    if (is_admin()) {
        return $template;
    }

    global $post;
    
    if (
        $post &&
        in_array($post->post_type, ['sfwd-topic', 'sfwd-lessons', 'sfwd-courses'])
    ) {
        $custom_template = get_stylesheet_directory() . '/learndash/ld30/templates/focus/index.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    return $template;
}
add_filter('template_include', 'force_learndash_template_override', 999);

/*
|--------------------------------------------------------------------------
| LearnDash：フッターリンク用カスタマイザー設定
|--------------------------------------------------------------------------
|
| 管理画面のカスタマイザーからLDフッターリンクのURLとテキストを編集できるようにします。
|
*/
function ld_footer_links_customize_register($wp_customize) {
    $wp_customize->add_section('ld_footer_links_section', array(
        'title'    => 'LDフッターリンク',
        'priority' => 30,
    ));

    $links = [
        ['slug' => 'mypage', 'label' => 'マイページ'],
        ['slug' => 'guideline', 'label' => '初心者ガイド'],
        ['slug' => 'faq', 'label' => 'よくある質問'],
        ['slug' => 'design_tips', 'label' => 'デザインTIPs'],
    ];

    foreach ($links as $link) {
        // URL設定
        $wp_customize->add_setting("ld_footer_link_url_{$link['slug']}", [
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport' => 'refresh', // 追加
        ]);
        
        // テキスト設定
        $wp_customize->add_setting("ld_footer_link_text_{$link['slug']}", [
            'default' => $link['label'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'refresh', // 追加
        ]);
        
        // URLコントロール
        $wp_customize->add_control("ld_footer_link_url_{$link['slug']}", [
            'label' => "{$link['label']}のURL",
            'section' => 'ld_footer_links_section',
            'type' => 'url',
            'description' => 'URLを入力してください', // 追加
        ]);
        
        // テキストコントロール
        $wp_customize->add_control("ld_footer_link_text_{$link['slug']}", [
            'label' => "{$link['label']}のボタンテキスト",
            'section' => 'ld_footer_links_section',
            'type' => 'text',
            'description' => 'ボタンに表示するテキストを入力してください', // 追加
        ]);
    }
}
add_action('customize_register', 'ld_footer_links_customize_register');

/*
|--------------------------------------------------------------------------
| LearnDash：フッターリンクの表示
|--------------------------------------------------------------------------
|
| カスタマイザーで設定したフッターリンクをHTMLとして出力します。
|
*/
function display_ld_footer_links() {
    $links = [
        ['slug' => 'mypage', 'label' => 'マイページ'],
        ['slug' => 'guideline', 'label' => '初心者ガイド'],
        ['slug' => 'faq', 'label' => 'よくある質問'],
        ['slug' => 'design_tips', 'label' => 'デザインTIPs'],
    ];

    $output = '<div class="ld-footer-category-links">';

    foreach ($links as $link) {
        $url = get_theme_mod("ld_footer_link_url_{$link['slug']}");
        $text = get_theme_mod("ld_footer_link_text_{$link['slug']}", $link['label']);

        if (!empty($url) && !empty($text)) {
            $output .= '<a href="' . esc_url($url) . '" class="ld-footer-btn">' . esc_html($text) . '</a>';
        }
    }

    $output .= '</div>';

    // In a function to be called from a template file, we should return the output.
    return $output;
}

/*
|--------------------------------------------------------------------------
| 非ログインユーザーのリダイレクト
|--------------------------------------------------------------------------
|
| ログインしていないユーザーをログインページにリダイレクトします。
| ホームページ、ログインページ、およびそのサブページは除外されます。
|
*/
function redirect_non_logged_in_users() {
    // ログイン済みユーザー、REST APIリクエスト、WP-CLI、CRONジョブの場合は何もしない
    if ( is_user_logged_in() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'WP_CLI' ) && WP_CLI ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
        return;
    }

    // ログインページとそのサブページかを判定する
    $is_login_page = false;
    $login_page = get_page_by_path('login');
    if ($login_page) {
        if (is_page($login_page->ID)) {
            $is_login_page = true;
        } else {
            // 現在のページの祖先をチェック
            $ancestors = get_post_ancestors(get_the_ID());
            if (!empty($ancestors) && in_array($login_page->ID, $ancestors)) {
                $is_login_page = true;
            }
        }
    }
    
    // フロントページでもログインページでもない場合、リダイレクト
    if ( ! is_front_page() && ! $is_login_page ) {
        wp_redirect( home_url( '/login/' ) );
        exit;
    }
}
add_action( 'template_redirect', 'redirect_non_logged_in_users' );

/*
|--------------------------------------------------------------------------
| FAQページ用検索クエリ変数の追加
|--------------------------------------------------------------------------
|
| FAQページで検索機能を使用するためのクエリ変数を追加します。
|
*/
function add_faq_search_query_var($vars) {
    $vars[] = 'faq_search';
    return $vars;
}
add_filter('query_vars', 'add_faq_search_query_var');

/**
 * FAQページ専用のJSを読み込む
 */
function enqueue_faq_search_script() {
    // 固定ページ「よくある質問」でのみスクリプトを読み込む
    if ( is_page_template('page-faq.php') ) {
        $version = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/assets/js/faq-search.js' ) );
        wp_enqueue_script(
            'faq-search',
            get_stylesheet_directory_uri() . '/assets/js/faq-search.js',
            array(),
            $version,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_faq_search_script');


/* スクロールに応じて、該当セクションのボタンだけがハイライト */
function enqueue_section_highlight_script() {
    wp_enqueue_script(
        'section-highlight',
        get_stylesheet_directory_uri() . '/assets/js/section-highlight.js',
        array(),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_section_highlight_script');

/*
|--------------------------------------------------------------------------
| スクリプト：TIPsページ用ドロワーメニュー
|--------------------------------------------------------------------------
|
| 学習のコツ・デザインTIPsページでのみドロワーメニューの
| JavaScriptを読み込みます。
|
*/
function enqueue_tips_drawer_script() {
    wp_enqueue_script(
        'tips-drawer',
        get_stylesheet_directory_uri() . '/assets/js/tips-drawer.js',
        array(),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_tips_drawer_script');


/*
|--------------------------------------------------------------------------
| 独自クラスの付与
|--------------------------------------------------------------------------
|
| bodyタグに案件獲得サポート、プロ専用実践ノウハウの独自クラスが付与される
| 
|
*/

function add_custom_body_class($classes) {
    if (is_singular('sfwd-courses')) {
        global $post;
        if ($post) {
            if ($post->post_name === 'project_order_support') {
                $classes[] = 'course-project_order_support';
            }
            if ($post->post_name === 'pro_only') {
                $classes[] = 'course-pro_only';
            }
        }
    }
    return $classes;
}
add_filter('body_class', 'add_custom_body_class');

/*
|--------------------------------------------------------------------------
| LDの全ページにチャプターリンク追加実装
|--------------------------------------------------------------------------
|
| xxxxxxxxxxxx
| 
|
*/

/*
if (!function_exists('display_user_accessible_chapters')) {
    function display_user_accessible_chapters($course_id, $user_id) {
        echo '<div style="color:red;">[DEBUG] 関数の最初に到達: $course_id=' . htmlspecialchars(print_r($course_id, true)) . ' / $user_id=' . htmlspecialchars(print_r($user_id, true)) . '</div>';
        if (!$course_id || !$user_id) return;
        $lesson_ids = learndash_course_get_children_of($course_id, 'sfwd-lessons');
        echo '<div style="color:orange;">[DEBUG] learndash_course_get_children_of $lesson_ids: ' . htmlspecialchars(print_r($lesson_ids, true)) . '</div>';
        $lesson_ids_by_post = get_posts(array(
            'post_type' => 'sfwd-lessons',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'course_id',
                    'value' => $course_id,
                    'compare' => '='
                )
            ),
            'fields' => 'ids'
        ));
        echo '<div style="color:orange;">[DEBUG] get_posts $lesson_ids: ' . htmlspecialchars(print_r($lesson_ids_by_post, true)) . '</div>';
        if (empty($lesson_ids) && empty($lesson_ids_by_post)) return;
        echo '<div class="ld-user-chapter-links"><h3>アクセス可能なチャプター</h3><ul>';
        foreach ($lesson_ids as $lesson_id) {
            $lesson_title = get_the_title($lesson_id);
            echo '<li style="color:purple;">[DEBUG] lesson_id: ' . $lesson_id . ' / title: ' . esc_html($lesson_title) . '</li>';
            if (learndash_is_lesson_accessible($lesson_id, $user_id, $course_id)) {
                $lesson_url = get_permalink($lesson_id);
                echo '<li><a href="' . esc_url($lesson_url) . '">' . esc_html($lesson_title) . '</a></li>';
            }
        }
        foreach ($lesson_ids_by_post as $lesson_id) {
            $lesson_title = get_the_title($lesson_id);
            echo '<li style="color:purple;">[DEBUG:get_posts] lesson_id: ' . $lesson_id . ' / title: ' . esc_html($lesson_title) . '</li>';
            if (learndash_is_lesson_accessible($lesson_id, $user_id, $course_id)) {
                $lesson_url = get_permalink($lesson_id);
                echo '<li><a href="' . esc_url($lesson_url) . '">' . esc_html($lesson_title) . '</a></li>';
            }
        }
        echo '</ul></div>';
    }
}

add_action('shutdown', function() {
    echo '<div style="color:magenta;">[DEBUG:shutdown] shutdownフック到達</div>';
    if (is_user_logged_in() && function_exists('learndash_course_get_children_of') && function_exists('display_user_accessible_chapters')) {
        global $post;
        $course_id = learndash_get_course_id($post->ID);
        $user_id = get_current_user_id();
        echo '<div style="color:blue;">[DEBUG:shutdown] $course_id: ' . htmlspecialchars(print_r($course_id, true)) . ' / $user_id: ' . htmlspecialchars(print_r($user_id, true)) . '</div>';
        display_user_accessible_chapters($course_id, $user_id);
    }
});
*/

add_action('wp_ajax_ld_get_course_structure', 'ld_get_course_structure');
add_action('wp_ajax_nopriv_ld_get_course_structure', 'ld_get_course_structure');

function ld_get_course_structure() {
    // セキュリティチェック
    if (empty($_POST['course_id'])) {
        wp_send_json_error('No course_id');
    }
    $course_id = intval($_POST['course_id']);
    $lessons = learndash_course_get_children_of($course_id, 'sfwd-lessons');
    $result = [];
    foreach ($lessons as $lesson_id) {
        $lesson = [
            'id' => $lesson_id,
            'title' => get_the_title($lesson_id),
            'url' => get_permalink($lesson_id),
            'topics' => []
        ];
        $topics = learndash_course_get_children_of($lesson_id, 'sfwd-topic');
        foreach ($topics as $topic_id) {
            $lesson['topics'][] = [
                'id' => $topic_id,
                'title' => get_the_title($topic_id),
                'url' => get_permalink($topic_id)
            ];
        }
        $result[] = $lesson;
    }
    wp_send_json_success($result);
}