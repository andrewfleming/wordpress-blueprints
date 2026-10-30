<?php
/**
 * NCA News theme functions.
 *
 * @package nca-news
 */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'nca-news-style',
		get_parent_theme_file_uri( 'style.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);
} );

add_action( 'after_setup_theme', function () {
	add_editor_style( 'style.css' );
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'nca-storyblock', 640, 360, true );
} );

/**
 * Block style variations used across templates.
 */
add_action( 'init', function () {
	register_block_style( 'core/post-terms', array( 'name' => 'primary-section', 'label' => __( 'Primary section only', 'nca-news' ) ) );
	register_block_style( 'core/heading', array( 'name' => 'mosaic-title', 'label' => __( 'Mosaic title', 'nca-news' ) ) );
	register_block_style( 'core/group', array( 'name' => 'storyblock', 'label' => __( 'Storyblock', 'nca-news' ) ) );
	register_block_style( 'core/group', array( 'name' => 'ad-slot', 'label' => __( 'Ad slot', 'nca-news' ) ) );
	register_block_style( 'core/button', array( 'name' => 'outline', 'label' => __( 'Outline', 'nca-news' ) ) );
	register_block_style( 'core/post-template', array( 'name' => 'just-in', 'label' => __( 'Just In list', 'nca-news' ) ) );
	register_block_style( 'core/post-template', array( 'name' => 'numbered', 'label' => __( 'Numbered (Trending)', 'nca-news' ) ) );

	// Story label (Breaking, Updated, Exclusive, Opinion). Editable in the post sidebar via the REST API.
	register_post_meta( 'post', 'nca_label', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'default'       => '',
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );

	// Block Bindings source: today's date in Sydney time, for the date bar.
	if ( function_exists( 'register_block_bindings_source' ) ) {
		register_block_bindings_source( 'nca/today', array(
			'label'              => __( 'Today (Sydney)', 'nca-news' ),
			'get_value_callback' => function ( $args ) {
				$tz = new DateTimeZone( 'Australia/Sydney' );
				$now = new DateTime( 'now', $tz );
				return ( $args['key'] ?? '' ) === 'time' ? $now->format( 'g:i a' ) : $now->format( 'l, j F Y' );
			},
		) );
	}
} );

/**
 * "Primary section only": show the most specific category (a child term)
 * instead of every category the story is filed in.
 */
add_filter( 'render_block_core/post-terms', function ( $html, $block, $instance ) {
	$class = $block['attrs']['className'] ?? '';
	if ( false === strpos( $class, 'is-style-primary-section' ) ) {
		return $html;
	}
	$post_id = $instance->context['postId'] ?? get_the_ID();
	$terms   = get_the_terms( $post_id, 'category' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return $html;
	}
	usort( $terms, function ( $a, $b ) { return ( $b->parent ? 1 : 0 ) - ( $a->parent ? 1 : 0 ); } );
	$term   = $terms[0];
	$root   = $term->parent ? get_term( $term->parent, 'category' ) : $term;
	$wrap   = sprintf( 'wp-block-post-terms taxonomy-category %s section-%s', esc_attr( $class ), esc_attr( $root->slug ) );
	return sprintf( '<div class="%s"><a href="%s" rel="tag">%s</a></div>', $wrap, esc_url( get_term_link( $term ) ), esc_html( $term->name ) );
}, 10, 3 );

/**
 * Body class with the root section, so archives and single stories pick up the section accent.
 */
add_filter( 'body_class', function ( $classes ) {
	$term = null;
	if ( is_category() ) {
		$term = get_queried_object();
	} elseif ( is_single() ) {
		$cats = get_the_category();
		$term = $cats ? $cats[0] : null;
	}
	if ( $term ) {
		while ( $term->parent ) { $term = get_term( $term->parent, 'category' ); }
		$classes[] = 'section-' . $term->slug;
	}
	return $classes;
} );

/**
 * Hide an empty bound story label instead of printing an empty paragraph.
 */
add_filter( 'render_block_core/paragraph', function ( $html, $block ) {
	if ( false !== strpos( $block['attrs']['className'] ?? '', 'story-label' ) && '' === trim( wp_strip_all_tags( $html ) ) ) {
		return '';
	}
	return $html;
}, 10, 2 );
