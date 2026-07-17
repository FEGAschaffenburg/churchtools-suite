<?php
/**
 * Test exact capability check when creating new post
 */

require_once __DIR__ . '/../../../../../wp-load.php';

echo "=== Test Create Post Capability Check ===\n\n";

// Get demo user
$demo_user = get_user_by( 'login', 'demo_kn1402' );

if ( ! $demo_user ) {
	echo "❌ User demo_kn1402 not found\n";
	exit( 1 );
}

echo "Testing with: {$demo_user->user_login} (ID: {$demo_user->ID})\n\n";

// Set as current user
wp_set_current_user( $demo_user->ID );

// Get CPT
$post_type = 'cts_demo_page';
$post_type_object = get_post_type_object( $post_type );

if ( ! $post_type_object ) {
	echo "❌ Post type not found!\n";
	exit( 1 );
}

echo "Post Type Object:\n";
echo "  Name: {$post_type_object->name}\n";
echo "  Label: {$post_type_object->label}\n";
echo "  Public: " . ( $post_type_object->public ? 'YES' : 'NO' ) . "\n";
echo "  Show UI: " . ( $post_type_object->show_ui ? 'YES' : 'NO' ) . "\n";
echo "  Show in menu: " . ( $post_type_object->show_in_menu ? 'YES' : 'NO' ) . "\n";
echo "  Map meta cap: " . ( $post_type_object->map_meta_cap ? 'YES' : 'NO' ) . "\n\n";

echo "Capability Mapping:\n";
echo "  create_posts: {$post_type_object->cap->create_posts}\n";
echo "  edit_posts: {$post_type_object->cap->edit_posts}\n";
echo "  edit_post: {$post_type_object->cap->edit_post}\n";
echo "  publish_posts: {$post_type_object->cap->publish_posts}\n\n";

echo "User Capability Checks:\n";
echo "  User has create_posts ({$post_type_object->cap->create_posts}): " . 
     ( current_user_can( $post_type_object->cap->create_posts ) ? '✓ YES' : '✗ NO' ) . "\n";
echo "  User has edit_posts ({$post_type_object->cap->edit_posts}): " . 
     ( current_user_can( $post_type_object->cap->edit_posts ) ? '✓ YES' : '✗ NO' ) . "\n";
echo "  User has edit_cts_demo_pages: " . 
     ( current_user_can( 'edit_cts_demo_pages' ) ? '✓ YES' : '✗ NO' ) . "\n";
echo "  User has publish_cts_demo_pages: " . 
     ( current_user_can( 'publish_cts_demo_pages' ) ? '✓ YES' : '✗ NO' ) . "\n\n";

echo "WordPress Core Check (simulating post-new.php):\n";
echo "  Can user create posts? ";

// This is what WordPress checks in post-new.php
if ( ! current_user_can( $post_type_object->cap->create_posts ) ) {
	echo "✗ NO - WILL BE REDIRECTED!\n\n";
	
	echo "This is why the redirect happens!\n";
	echo "WordPress checks: current_user_can( '{$post_type_object->cap->create_posts}' )\n";
	echo "User has this capability: NO\n\n";
	
	echo "Debugging capability:\n";
	$user_obj = wp_get_current_user();
	echo "  User capabilities (all):\n";
	foreach ( $user_obj->allcaps as $cap => $val ) {
		if ( $val && strpos( $cap, 'demo' ) !== false ) {
			echo "    - {$cap}: " . ( $val ? 'YES' : 'NO' ) . "\n";
		}
	}
	
	echo "\n  Expected capability: {$post_type_object->cap->create_posts}\n";
	echo "  Has capability: " . ( isset( $user_obj->allcaps[$post_type_object->cap->create_posts] ) ? 
	                             ( $user_obj->allcaps[$post_type_object->cap->create_posts] ? 'YES' : 'NO' ) : 
	                             'NOT SET' ) . "\n\n";
	
	// Check role
	$role = get_role( 'cts_demo_user' );
	echo "  Role 'cts_demo_user' has this capability: ";
	echo ( isset( $role->capabilities[$post_type_object->cap->create_posts] ) ? 
	       ( $role->capabilities[$post_type_object->cap->create_posts] ? 'YES' : 'NO' ) : 
	       'NOT SET' ) . "\n";
	
} else {
	echo "✓ YES - Would be allowed!\n\n";
}

echo "\n=== Solution ===\n\n";
echo "If the check failed, the user needs the capability: {$post_type_object->cap->create_posts}\n";
echo "Currently set to: {$post_type_object->cap->create_posts}\n";
echo "User must have this capability in their role.\n";
