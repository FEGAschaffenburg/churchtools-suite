<?php
/**
 * Check Actual Page Content
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.5
 */

require_once dirname( __DIR__, 4 ) . '/wp-load.php';

$page = get_page_by_path( 'list-ansichten/list-modern', OBJECT, 'page' );

if ( ! $page ) {
	die( "Page not found\n" );
}

echo "Page: {$page->post_title} (ID: {$page->ID})\n\n";

// Check for HTML entity encoded shortcodes
$content = $page->post_content;

// Count &#91; (escaped [)
$escaped_open = substr_count( $content, '&#91;' );
$escaped_close = substr_count( $content, '&#93;' );

// Count raw [ in code blocks
preg_match_all( '/<code>.*?\[(?!cts_list).*?<\/code>/s', $content, $other_brackets );
preg_match_all( '/<code>.*?\[cts_list.*?\].*?<\/code>/s', $content, $unescaped_shortcodes );

echo "Escaped opening brackets (&#91;): $escaped_open\n";
echo "Escaped closing brackets (&#93;): $escaped_close\n";
echo "Unescaped shortcodes in code blocks: " . count( $unescaped_shortcodes[0] ) . "\n";

// Show first code block as sample
preg_match( '/<code>(.*?)<\/code>/s', $content, $first_code );
if ( ! empty( $first_code ) ) {
	echo "\nFirst code block sample:\n";
	echo $first_code[0] . "\n";
}
