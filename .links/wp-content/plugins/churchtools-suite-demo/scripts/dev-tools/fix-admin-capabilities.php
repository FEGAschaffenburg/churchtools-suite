<?php
/**
 * Force Admin CPT Capabilities
 * 
 * Ensures administrator has all CPT capabilities for cts_demo_page
 * Run this once if admin cannot edit demo pages
 */

// Load WordPress
require_once __DIR__ . '/../../../../../wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
	die( 'Access denied. You must be an administrator.' );
}

echo "<h1>Admin CPT Capabilities - Force Update</h1>";
echo "<hr>";

// Define all CPT capabilities
$cpt_caps = [
	'edit_cts_demo_pages',
	'edit_others_cts_demo_pages',
	'publish_cts_demo_pages',
	'read_private_cts_demo_pages',
	'delete_cts_demo_pages',
	'delete_private_cts_demo_pages',
	'delete_published_cts_demo_pages',
	'delete_others_cts_demo_pages',
	'edit_private_cts_demo_pages',
	'edit_published_cts_demo_pages',
	'edit_cts_demo_page',
	'read_cts_demo_page',
	'delete_cts_demo_page',
	'manage_cts_demo_pages',
];

// Get administrator role
$admin_role = get_role( 'administrator' );

if ( ! $admin_role ) {
	die( '<p style="color: red;">ERROR: Administrator role not found!</p>' );
}

echo "<h2>Current Administrator Capabilities:</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Capability</th><th>Has Cap (Before)</th><th>Action</th><th>Has Cap (After)</th></tr>";

$added_count = 0;
$existing_count = 0;

foreach ( $cpt_caps as $cap ) {
	$has_before = $admin_role->has_cap( $cap );
	
	// Force add capability
	$admin_role->add_cap( $cap );
	
	// Re-fetch role to check
	$admin_role = get_role( 'administrator' );
	$has_after = $admin_role->has_cap( $cap );
	
	$action = $has_before ? 'Already had' : 'ADDED';
	$color = $has_before ? '#666' : 'green';
	
	if ( ! $has_before ) {
		$added_count++;
	} else {
		$existing_count++;
	}
	
	echo "<tr>";
	echo "<td><code>{$cap}</code></td>";
	echo "<td>" . ( $has_before ? '✓ Yes' : '✗ No' ) . "</td>";
	echo "<td style='color: {$color}; font-weight: bold;'>{$action}</td>";
	echo "<td>" . ( $has_after ? '✓ Yes' : '✗ No' ) . "</td>";
	echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>Summary:</h2>";
echo "<ul>";
echo "<li><strong>Total CPT Capabilities:</strong> " . count( $cpt_caps ) . "</li>";
echo "<li><strong>Already existing:</strong> {$existing_count}</li>";
echo "<li><strong style='color: green;'>Newly added:</strong> {$added_count}</li>";
echo "</ul>";

// Check current user's capabilities
echo "<hr>";
echo "<h2>Current User Check:</h2>";
$current_user = wp_get_current_user();
echo "<p><strong>Username:</strong> {$current_user->user_login}</p>";
echo "<p><strong>Roles:</strong> " . implode( ', ', $current_user->roles ) . "</p>";

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Capability</th><th>Current User Has?</th></tr>";

foreach ( $cpt_caps as $cap ) {
	$has = current_user_can( $cap );
	echo "<tr>";
	echo "<td><code>{$cap}</code></td>";
	echo "<td style='color: " . ( $has ? 'green' : 'red' ) . ";'>" . ( $has ? '✓ Yes' : '✗ No' ) . "</td>";
	echo "</tr>";
}

echo "</table>";

// Test specific capability
echo "<hr>";
echo "<h2>Key Capability Test:</h2>";
echo "<ul>";
echo "<li><strong>edit_others_cts_demo_pages:</strong> " . ( current_user_can( 'edit_others_cts_demo_pages' ) ? '<span style="color: green;">✓ YES</span>' : '<span style="color: red;">✗ NO</span>' ) . "</li>";
echo "<li><strong>edit_cts_demo_pages:</strong> " . ( current_user_can( 'edit_cts_demo_pages' ) ? '<span style="color: green;">✓ YES</span>' : '<span style="color: red;">✗ NO</span>' ) . "</li>";
echo "<li><strong>manage_cts_demo_pages:</strong> " . ( current_user_can( 'manage_cts_demo_pages' ) ? '<span style="color: green;">✓ YES</span>' : '<span style="color: red;">✗ NO</span>' ) . "</li>";
echo "</ul>";

// Get a demo page to test
$demo_pages = get_posts( [
	'post_type' => 'cts_demo_page',
	'posts_per_page' => 1,
	'post_status' => 'any',
] );

if ( ! empty( $demo_pages ) ) {
	$demo_page = $demo_pages[0];
	echo "<hr>";
	echo "<h2>Test with Actual Demo Page:</h2>";
	echo "<p><strong>Page:</strong> {$demo_page->post_title} (ID: {$demo_page->ID})</p>";
	echo "<p><strong>Author:</strong> " . get_the_author_meta( 'user_login', $demo_page->post_author ) . " (ID: {$demo_page->post_author})</p>";
	echo "<ul>";
	echo "<li><strong>Can edit this post?</strong> " . ( current_user_can( 'edit_post', $demo_page->ID ) ? '<span style="color: green;">✓ YES</span>' : '<span style="color: red;">✗ NO</span>' ) . "</li>";
	echo "<li><strong>Can delete this post?</strong> " . ( current_user_can( 'delete_post', $demo_page->ID ) ? '<span style="color: green;">✓ YES</span>' : '<span style="color: red;">✗ NO</span>' ) . "</li>";
	echo "</ul>";
}

echo "<hr>";
echo "<h2>✓ Done!</h2>";
echo "<p>Administrator role has been updated with all CPT capabilities.</p>";
echo "<p><a href='" . admin_url( 'edit.php?post_type=cts_demo_page' ) . "' style='font-size: 16px; padding: 10px 20px; background: #2271b1; color: white; text-decoration: none; border-radius: 3px;'>Go to Demo Pages</a></p>";
