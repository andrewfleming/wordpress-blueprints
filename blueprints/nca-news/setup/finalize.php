<?php
/**
 * NCA News blueprint: runs after the WXR import.
 *
 * - Restores every term to its original ID. The theme's home template queries sections by
 *   category ID, and the WXR importer assigns new IDs, so this keeps the templates working.
 * - Restores post authors, Co-Authors Plus byline order, sticky posts and the privacy page.
 */
require_once '/wordpress/wp-load.php';
global $wpdb;

$manifest = json_decode( file_get_contents( '/tmp/nca-news-manifest.json' ), true );
if ( ! is_array( $manifest ) ) {
	throw new Exception( 'NCA News: manifest missing or invalid.' );
}

// 1. Map each manifest term (taxonomy + slug) to its current term_id / term_taxonomy_id.
$term_map = array(); // current term_id => target term_id
$tt_map   = array(); // current tt_id   => target tt_id
foreach ( $manifest['terms'] as $t ) {
	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT t.term_id, tt.term_taxonomy_id FROM $wpdb->terms t JOIN $wpdb->term_taxonomy tt ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.slug = %s",
			$t['taxonomy'],
			$t['slug']
		)
	);
	if ( $row ) {
		$term_map[ (int) $row->term_id ]        = (int) $t['id'];
		$tt_map[ (int) $row->term_taxonomy_id ] = (int) $t['tt'];
	}
}

// Terms not in the manifest (e.g. the wp_theme term) move above the highest target ID.
$next = max( array_merge( array_values( $term_map ), array_values( $tt_map ) ) ) + 1;
foreach ( $wpdb->get_results( "SELECT term_id, term_taxonomy_id FROM $wpdb->term_taxonomy" ) as $r ) {
	if ( ! isset( $term_map[ (int) $r->term_id ] ) ) {
		$term_map[ (int) $r->term_id ] = $next;
		$tt_map[ (int) $r->term_taxonomy_id ] = $next;
		$next++;
	}
}

// 2. Two-phase re-key so no ID collides mid-update: shift everything up, then down to targets.
$offset = 10000000;
$wpdb->query( "UPDATE $wpdb->terms SET term_id = term_id + $offset" );
$wpdb->query( "UPDATE $wpdb->term_taxonomy SET term_id = term_id + $offset, term_taxonomy_id = term_taxonomy_id + $offset, parent = CASE WHEN parent > 0 THEN parent + $offset ELSE 0 END" );
$wpdb->query( "UPDATE $wpdb->term_relationships SET term_taxonomy_id = term_taxonomy_id + $offset" );
$wpdb->query( "UPDATE $wpdb->termmeta SET term_id = term_id + $offset" );

foreach ( $term_map as $from => $to ) {
	$f = $from + $offset;
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->terms SET term_id = %d WHERE term_id = %d", $to, $f ) );
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->term_taxonomy SET term_id = %d WHERE term_id = %d", $to, $f ) );
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->term_taxonomy SET parent = %d WHERE parent = %d", $to, $f ) );
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->termmeta SET term_id = %d WHERE term_id = %d", $to, $f ) );
}
foreach ( $tt_map as $from => $to ) {
	$f = $from + $offset;
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->term_taxonomy SET term_taxonomy_id = %d WHERE term_taxonomy_id = %d", $to, $f ) );
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->term_relationships SET term_taxonomy_id = %d WHERE term_taxonomy_id = %d", $to, $f ) );
}

foreach ( get_taxonomies() as $tax ) {
	delete_option( "{$tax}_children" );
}
wp_cache_flush();

// 3. Authors: map each post back to its original author by login.
$user_ids = array();
foreach ( $manifest['post_authors'] as $post_id => $login ) {
	if ( ! isset( $user_ids[ $login ] ) ) {
		$user               = get_user_by( 'login', $login );
		$user_ids[ $login ] = $user ? $user->ID : 1;
	}
	$wpdb->update( $wpdb->posts, array( 'post_author' => $user_ids[ $login ] ), array( 'ID' => (int) $post_id ) );
}

// 4. Co-Authors Plus byline order (the importer does not keep term_order).
foreach ( $manifest['coauthor_order'] as $post_id => $slugs ) {
	foreach ( array_values( $slugs ) as $i => $slug ) {
		$term = get_term_by( 'slug', $slug, 'author' );
		if ( $term ) {
			$wpdb->update(
				$wpdb->term_relationships,
				array( 'term_order' => $i ),
				array( 'object_id' => (int) $post_id, 'term_taxonomy_id' => (int) $term->term_taxonomy_id )
			);
		}
	}
}

// 5. Sticky posts, privacy page, counts, permalinks.
update_option( 'sticky_posts', array_map( 'intval', $manifest['sticky_posts'] ) );
if ( get_post( 3 ) ) {
	update_option( 'wp_page_for_privacy_policy', 3 );
}
foreach ( get_taxonomies() as $tax ) {
	$ids = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'fields' => 'tt_ids' ) );
	if ( $ids && ! is_wp_error( $ids ) ) {
		wp_update_term_count_now( $ids, $tax );
	}
}
clean_post_cache( 0 );
wp_cache_flush();
flush_rewrite_rules();
