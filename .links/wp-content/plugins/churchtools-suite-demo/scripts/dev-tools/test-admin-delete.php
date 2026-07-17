<?php
/**
 * Test Admin Delete Capabilities
 * 
 * Tests if administrator can delete demo pages
 */

// Load WordPress
require_once __DIR__ . '/../../../../../wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
	die( 'Access denied. You must be an administrator.' );
}

echo "<h1>Admin Delete Capabilities Test</h1>";
echo "<hr>";

$current_user = wp_get_current_user();
echo "<p><strong>Current User:</strong> {$current_user->user_login}</p>";
echo "<p><strong>Roles:</strong> " . implode( ', ', $current_user->roles ) . "</p>";

echo "<hr>";
echo "<h2>Delete Capabilities:</h2>";

$delete_caps = [
	'delete_cts_demo_pages',
	'delete_cts_demo_page',
	'delete_others_cts_demo_pages',
	'delete_private_cts_demo_pages',
	'delete_published_cts_demo_pages',
];

echo "<ul>";
foreach ( $delete_caps as $cap ) {
	$has = current_user_can( $cap );
	echo "<li><strong>{$cap}:</strong> " . ( $has ? '<span style="color: green;">✓ YES</span>' : '<span style="color: red;">✗ NO</span>' ) . "</li>";
}
echo "</ul>";

// Test with actual posts
echo "<hr>";
echo "<h2>Test with Actual Demo Pages:</h2>";

// Get demo pages from different authors
$demo_pages = get_posts( [
	'post_type' => 'cts_demo_page',
	'posts_per_page' => 10,
	'post_status' => 'any',
] );

if ( empty( $demo_pages ) ) {
	echo "<p>No demo pages found.</p>";
} else {
	echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
	echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Status</th><th>Can Delete?</th><th>Meta Cap Result</th></tr>";
	
	foreach ( $demo_pages as $page ) {
		$author = get_the_author_meta( 'user_login', $page->post_author );
		$can_delete = current_user_can( 'delete_post', $page->ID );
		
		// Test meta cap mapping
		$meta_caps = map_meta_cap( 'delete_post', $current_user->ID, $page->ID );
		$meta_result = json_encode( $meta_caps );
		
		echo "<tr>";
		echo "<td>{$page->ID}</td>";
		echo "<td>" . esc_html( $page->post_title ) . "</td>";
		echo "<td>{$author} (ID: {$page->post_author})</td>";
		echo "<td>{$page->post_status}</td>";
		echo "<td style='color: " . ( $can_delete ? 'green' : 'red' ) . ";'>" . ( $can_delete ? '✓ YES' : '✗ NO' ) . "</td>";
		echo "<td style='font-size: 11px; font-family: monospace;'>{$meta_result}</td>";
		echo "</tr>";
	}
	
	echo "</table>";
}

// Test delete_others_posts capability mapping
echo "<hr>";
echo "<h2>Simulate Delete Action:</h2>";

if ( ! empty( $demo_pages ) ) {
	$test_page = $demo_pages[0];
	echo "<p><strong>Test Page:</strong> {$test_page->post_title} (ID: {$test_page->ID})</p>";
	echo "<p><strong>Author:</strong> " . get_the_author_meta( 'user_login', $test_page->post_author ) . " (ID: {$test_page->post_author})</p>";
	
	// Check raw capabilities
	$admin_role = get_role( 'administrator' );
	echo "<h3>Raw Administrator Role Capabilities:</h3>";
	echo "<ul>";
	echo "<li><strong>delete_cts_demo_pages:</strong> " . ( $admin_role->has_cap( 'delete_cts_demo_pages' ) ? '✓' : '✗' ) . "</li>";
	echo "<li><strong>delete_others_cts_demo_pages:</strong> " . ( $admin_role->has_cap( 'delete_others_cts_demo_pages' ) ? '✓' : '✗' ) . "</li>";
	echo "<li><strong>delete_published_cts_demo_pages:</strong> " . ( $admin_role->has_cap( 'delete_published_cts_demo_pages' ) ? '✓' : '✗' ) . "</li>";
	echo "</ul>";
	
	// Test meta cap
	echo "<h3>Meta Cap Test:</h3>";
	$required_caps = map_meta_cap( 'delete_post', $current_user->ID, $test_page->ID );
	echo "<p><strong>Required Caps:</strong> " . json_encode( $required_caps ) . "</p>";
	
	echo "<p><strong>Check each required cap:</strong></p>";
	echo "<ul>";
	foreach ( $required_caps as $req_cap ) {
		$has = current_user_can( $req_cap );
		echo "<li><code>{$req_cap}</code>: " . ( $has ? '<span style="color: green;">✓ YES</span>' : '<span style="color: red;">✗ NO</span>' ) . "</li>";
	}
	echo "</ul>";
	
	// Final verdict
	$can_delete = current_user_can( 'delete_post', $test_page->ID );
	if ( $can_delete ) {
		echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ Administrator CAN delete this page!</p>";
	} else {
		echo "<p style='color: red; font-size: 18px; font-weight: bold;'>✗ Administrator CANNOT delete this page!</p>";
		echo "<p style='color: red;'>This is a problem that needs to be fixed.</p>";
	}
}

echo "<hr>";
echo "<p><a href='" . admin_url( 'edit.php?post_type=cts_demo_page' ) . "' class='button button-primary'>Go to Demo Pages</a></p>";
