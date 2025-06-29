<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<?php astra_content_bottom(); ?>
</div> <!-- ast-container -->
</div><!-- #content -->
<?php
	astra_content_after();

	astra_footer_before();

	astra_footer();

	astra_footer_after();
?>
</div><!-- #page -->
<?php
	astra_body_bottom();
	wp_footer();
?>

<!-- マイページの表示名 〇〇さんのチャプター -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ユーザーの姓・名をPHPから取得して結合
    const firstName = "<?php echo esc_html(get_user_meta(get_current_user_id(), 'first_name', true)); ?>";
    const lastName = "<?php echo esc_html(get_user_meta(get_current_user_id(), 'last_name', true)); ?>";
    const fullName = lastName + '' + firstName;

    // タイトルテキストを書き換える
    const heading = document.querySelector('.ld-section-heading h3');
    if (heading && heading.textContent.trim() === 'あなたのチャプター') {
        heading.textContent = `${fullName}さんの進捗状況`;
    }
});
</script>


</body>

</html>