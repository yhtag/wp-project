<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Bridal
 */
 
/**
 * Returns true if a blog has more than 1 category
 */


if ( ! function_exists( 'bridal_the_custom_logo' ) ) :
/**
 * Displays the optional custom logo.
 *
 * Does nothing if the custom logo is not available.
 */
function bridal_the_custom_logo() {
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
}
endif;

/**
 * Flush out the transients used in bridal_categorized_blog
 */
function bridal_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'bridal_category_transient_flusher' );
add_action( 'save_post',     'bridal_category_transient_flusher' );

if ( ! function_exists( 'bridal_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function bridal_comment( $comment, $args, $depth ) {
	if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
		<div class="comment-body">
			<?php esc_html_e( 'Pingback:', 'bridal' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( esc_html( 'Edit', 'bridal' ), '<span class="edit-link">', '</span>' ); ?>
		</div>
	<?php else : ?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, 34 ); ?>
				</div><!-- .comment-author -->
				<div class="comment-metadata">
					<?php printf( '<cite class="fn">%s</cite> on', get_comment_author_link() ); ?>
					<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
						<time datetime="<?php comment_time( 'c' ); ?>">
							<?php printf( '%1$s | %2$s', esc_attr(get_comment_date()), esc_attr(get_comment_time()) ); ?>
						</time>
					</a>
					<?php edit_comment_link( esc_html( 'Edit', 'bridal' ), '<span class="edit-link">', '</span>' ); ?>
				</div><!-- .comment-metadata -->
				<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'bridal' ); ?></p>
				<?php endif; ?>
			</footer><!-- .comment-meta -->
			<div class="comment-content">
				<?php comment_text(); ?>
			</div><!-- .comment-content -->
			<?php
				comment_reply_link( array_merge( $args, array(
					'add_below' => 'div-comment',
					'depth'     => $depth,
					'max_depth' => $args['max_depth'],
					'before'    => '<div class="reply">',
					'after'     => '</div>',
				) ) );
			?>
		</article><!-- .comment-body -->
	<?php     
	endif;
}
endif; // ends check for bridal_comment()

if ( ! function_exists( 'bridal_the_attached_image' ) ) :
/**
 * Prints the attached image with a link to the next attached image.
 */
function bridal_the_attached_image() {
	$post                = get_post();
	$attachment_size     = apply_filters( 'bridal_attachment_size', array( 1200, 1200 ) );
	$next_attachment_url = wp_get_attachment_url();
	/**
	 * Grab the IDs of all the image attachments in a gallery so we can get the
	 * URL of the next adjacent image in a gallery, or the first image (if
	 * we're looking at the last image in a gallery), or, in a gallery of one,
	 * just the link to that image file.
	 */
	$attachment_ids = get_posts( array(
		'post_parent'    => $post->post_parent,
		'fields'         => 'ids',
		'numberposts'    => -1,
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'menu_order ID'
	) );
	// If there is more than 1 attachment in a gallery...
	if ( count( $attachment_ids ) > 1 ) {
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id == $post->ID ) {
				$next_id = current( $attachment_ids );
				break;
			}
		}
		// get the URL of the next image attachment...
		if ( $next_id )
			$next_attachment_url = get_attachment_link( $next_id );
		// or get the URL of the first image attachment.
		else
			$next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
	}
	printf( '<a href="%1$s" rel="attachment">%2$s</a>',
		esc_url( $next_attachment_url ),
		wp_get_attachment_image( $post->ID, $attachment_size )
	);
}
endif;
if ( ! function_exists( 'bridal_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function bridal_posted_on() {
	$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) )
		$time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);
	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		get_the_date(),
		esc_attr( get_the_modified_date( 'c' ) ),
		get_the_modified_date()
	);
	printf( // WPCS: XSS OK
                '<span class="posted-on">%1$s<a href="%2$s" rel="bookmark">%3$s</a></span>',
                wp_kses_post( _x( '<span class="screen-reader-text">Posted on</span>', 'Used before publish date.', 'bridal' ) ),
                esc_url( get_permalink() ),
                esc_html($time_string)
            );
}
endif;
/**
 * Returns true if a blog has more than 1 category
 */
function bridal_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );
		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );
		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}
	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so bridal_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so bridal_categorized_blog should return false
		return false;
	}
}
