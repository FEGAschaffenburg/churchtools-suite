<?php
/**
 * Quick check for demo users
 */

require_once __DIR__ . '/../../../../../wp-load.php';

global $wpdb;

echo "=== Demo Users ===\n\n";

$users = $wpdb->get_results("
    SELECT id, email, first_name, last_name, verified_at, wordpress_user_id, created_at 
    FROM plugin_cts_demo_users 
    ORDER BY created_at DESC 
    LIMIT 5
");

if (empty($users)) {
    echo "No users found.\n";
} else {
    foreach ($users as $user) {
        echo "ID: {$user->id}\n";
        echo "Email: {$user->email}\n";
        echo "Name: {$user->first_name} {$user->last_name}\n";
        echo "Verified: " . ($user->verified_at ? "YES ({$user->verified_at})" : "NO") . "\n";
        echo "WP User ID: " . ($user->wordpress_user_id ?? 'NULL') . "\n";
        echo "Created: {$user->created_at}\n";
        echo "---\n";
    }
}

// Check specific token
$token = '00b8e19d43c3455601c1deee70127cfc5e95c2928077e0a17e9c714016692d1a';
echo "\n=== Token Check ===\n";
echo "Looking for token: $token\n\n";

$user_by_token = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM plugin_cts_demo_users WHERE verification_token = %s",
    $token
));

if ($user_by_token) {
    echo "Found user:\n";
    echo "ID: {$user_by_token->id}\n";
    echo "Email: {$user_by_token->email}\n";
    echo "Name: {$user_by_token->first_name} {$user_by_token->last_name}\n";
    echo "Verified: " . ($user_by_token->verified_at ? "YES" : "NO") . "\n";
    echo "WP User ID: " . ($user_by_token->wordpress_user_id ?? 'NULL') . "\n";
} else {
    echo "No user found with this token.\n";
}
