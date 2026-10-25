<?php
/**
 * Komentar artikel.
 *
 * Label ditulis eksplisit dalam bahasa Indonesia karena bahasa situs tidak diubah.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section class="comments" id="komentar" aria-label="<?php esc_attr_e( 'Komentar', 'mea-orbit' ); ?>">
	<?php if ( have_comments() ) : ?>
		<p class="comments-title">
			<?php
			/* translators: %s: jumlah komentar. */
			printf( esc_html( _n( '%s komentar', '%s komentar', get_comments_number(), 'mea-orbit' ) ), esc_html( number_format_i18n( get_comments_number() ) ) );
			?>
		</p>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 40,
				)
			);
			?>
		</ol>
		<?php
		the_comments_navigation(
			array(
				'prev_text' => __( 'Komentar lebih lama', 'mea-orbit' ),
				'next_text' => __( 'Komentar lebih baru', 'mea-orbit' ),
			)
		);
	endif;

	if ( comments_open() ) {
		$mea_orbit_commenter = wp_get_current_commenter();
		comment_form(
			array(
				'title_reply'          => __( 'Tulis komentar', 'mea-orbit' ),
				/* translators: %s: nama penulis komentar yang dibalas. */
				'title_reply_to'       => __( 'Balas %s', 'mea-orbit' ),
				'title_reply_before'   => '<p id="reply-title" class="comments-title">',
				'title_reply_after'    => '</p>',
				'cancel_reply_link'    => __( 'Batal', 'mea-orbit' ),
				'label_submit'         => __( 'Kirim komentar', 'mea-orbit' ),
				'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'Alamat email Anda tidak akan ditampilkan.', 'mea-orbit' ) . '</p>',
				'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Komentar', 'mea-orbit' ) . '</label><textarea id="comment" name="comment" rows="6" maxlength="65525" required></textarea></p>',
				'fields'               => array(
					'author'  => '<p class="comment-form-author"><label for="author">' . esc_html__( 'Nama', 'mea-orbit' ) . '</label><input id="author" name="author" type="text" autocomplete="name" maxlength="245" value="' . esc_attr( $mea_orbit_commenter['comment_author'] ) . '" required></p>',
					'email'   => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'mea-orbit' ) . '</label><input id="email" name="email" type="email" autocomplete="email" maxlength="100" value="' . esc_attr( $mea_orbit_commenter['comment_author_email'] ) . '" required></p>',
					'cookies' => '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"><label for="wp-comment-cookies-consent">' . esc_html__( 'Simpan nama dan email saya di browser ini untuk komentar berikutnya.', 'mea-orbit' ) . '</label></p>',
				),
			)
		);
	}
	?>
</section>
