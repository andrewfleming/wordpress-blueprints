<?php
/**
 * NCA News blueprint: runs after the uploads zip is extracted and before the WXR import.
 *
 * - Removes WordPress's default post, page and comment so the imported content keeps its original IDs.
 * - Creates the newsroom users so imported posts and Co-Authors Plus bylines map to them by login.
 * - Registers the extracted media files as attachments with their original IDs and metadata,
 *   so featured images and image blocks resolve without re-downloading or regenerating sizes.
 */
defined( 'ABSPATH' ) || exit; // Loaded by the blueprint's runPHP step after wp-load.php.

$manifest = json_decode( file_get_contents( WP_CONTENT_DIR . '/nca-news-setup/nca-news-manifest.json' ), true );
if ( ! is_array( $manifest ) ) {
	throw new Exception( 'NCA News: manifest missing or invalid.' );
}

foreach ( array( 1, 2, 3 ) as $default_id ) {
	if ( get_post( $default_id ) ) {
		wp_delete_post( $default_id, true );
	}
}
if ( get_comment( 1 ) ) {
	wp_delete_comment( 1, true );
}

foreach ( $manifest['users'] as $u ) {
	if ( username_exists( $u['login'] ) ) {
		continue;
	}
	$user_id = wp_insert_user(
		array(
			'user_login'   => $u['login'],
			'user_email'   => $u['email'],
			'user_pass'    => wp_generate_password( 24 ),
			'display_name' => $u['display_name'],
			'first_name'   => $u['first_name'],
			'last_name'    => $u['last_name'],
			'nickname'     => $u['nickname'],
			'description'  => $u['description'],
			'role'         => $u['role'],
		)
	);
	if ( is_wp_error( $user_id ) ) {
		error_log( 'NCA News: could not create user ' . $u['login'] . ': ' . $user_id->get_error_message() );
		continue;
	}
	if ( ! empty( $u['nca_job_title'] ) ) {
		update_user_meta( $user_id, 'nca_job_title', $u['nca_job_title'] );
	}
}

$uploads = wp_get_upload_dir();
$missing = 0;
foreach ( $manifest['attachments'] as $a ) {
	if ( get_post( $a['id'] ) ) {
		continue;
	}
	if ( ! file_exists( $uploads['basedir'] . '/' . $a['file'] ) ) {
		$missing++;
		continue;
	}
	$author = get_user_by( 'login', $a['author'] );
	$id     = wp_insert_post(
		array(
			'import_id'      => $a['id'],
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_title'     => $a['title'],
			'post_name'      => $a['name'],
			'post_date'      => $a['date'],
			'post_date_gmt'  => $a['date_gmt'],
			'post_mime_type' => $a['mime'],
			'post_parent'    => (int) $a['parent'],
			'post_author'    => $author ? $author->ID : 1,
			'post_excerpt'   => $a['excerpt'],
			'post_content'   => $a['content'],
			'guid'           => $uploads['baseurl'] . '/' . $a['file'],
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		error_log( 'NCA News: attachment ' . $a['id'] . ' failed: ' . $id->get_error_message() );
		continue;
	}
	update_post_meta( $id, '_wp_attached_file', $a['file'] );
	update_post_meta( $id, '_wp_attachment_metadata', $a['metadata'] );
	if ( '' !== $a['alt'] ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $a['alt'] );
	}
	if ( '' !== $a['wpai_generated'] ) {
		update_post_meta( $id, 'wpai_generated', $a['wpai_generated'] );
	}
}
if ( $missing ) {
	error_log( "NCA News: $missing attachment files were missing from the uploads zip." );
}
