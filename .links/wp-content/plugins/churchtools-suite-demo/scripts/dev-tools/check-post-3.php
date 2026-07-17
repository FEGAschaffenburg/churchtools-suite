<?php
/**
 * Check what Post ID 3 is
 */

define('WP_USE_THEMES', false);
require_once('../../../../../wp-load.php');

echo "=== Checking Post ID 3 ===" . PHP_EOL . PHP_EOL;

$post = get_post(3);
if ($post) {
    echo "Post ID: " . $post->ID . PHP_EOL;
    echo "Post Type: " . $post->post_type . PHP_EOL;
    echo "Post Status: " . $post->post_status . PHP_EOL;
    echo "Post Title: " . $post->post_title . PHP_EOL;
    echo "Post Author: " . $post->post_author . PHP_EOL;
} else {
    echo "❌ Post ID 3 does not exist!" . PHP_EOL;
}

// Check what cap is required for editing post ID 3
echo PHP_EOL . "Required caps for edit_post (ID 3):" . PHP_EOL;
$user = get_user_by('id', 13);
if ($user) {
    $required_caps = map_meta_cap('edit_post', $user->ID, 3);
    print_r($required_caps);
    
    echo PHP_EOL . "User can edit post 3: " . (user_can($user, 'edit_post', 3) ? "✓ YES" : "❌ NO") . PHP_EOL;
}

// Check when post-new.php loads, what happens
echo PHP_EOL . "=== Simulating post-new.php behavior ===" . PHP_EOL;
$_GET['post_type'] = 'cts_demo_page';

// WordPress checks if user can create posts of this type
$post_type_object = get_post_type_object('cts_demo_page');
if ($post_type_object) {
    echo "Create posts capability: " . $post_type_object->cap->create_posts . PHP_EOL;
    echo "User has capability: " . (user_can($user, $post_type_object->cap->create_posts) ? "✓ YES" : "❌ NO") . PHP_EOL;
}

echo PHP_EOL . "Done." . PHP_EOL;
