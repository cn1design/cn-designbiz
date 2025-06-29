<?php
/**
 * Template Name: FAQページ
 *
 * @package Astra Child
 */

get_header(); ?>

<div class="faq_page">
    <div class="faq-container">
        <h1><?php the_title(); ?></h1>

        <!-- 検索フォーム -->
        <div class="faq-search-container">
            <form id="faq-search-form" class="faq-search-form">
                <input type="text" id="faq-search-input" placeholder="よくある質問を検索..."
                    value="<?php echo esc_attr(get_query_var('faq_search')); ?>" class="faq-search-input">
                <button type="submit" class="faq-search-button">検索</button>
            </form>
        </div>

        <!-- FAQコンテンツ -->
        <div class="faq-content" id="faq-content-original">
            <?php
            // 通常のコンテンツを表示
            while (have_posts()) : the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>