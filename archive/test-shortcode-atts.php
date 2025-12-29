<?php
/**
 * Test: Was macht shortcode_atts() mit boolean false?
 */

require_once __DIR__ . '/../../wp-load.php';

echo '<h1>Test: shortcode_atts() mit boolean false</h1>';

// Test 1: Boolean false
$atts_input = [
    'show_description' => false,
    'show_location' => true,
];

$defaults = [
    'show_description' => true,
    'show_location' => true,
];

echo '<h2>Input:</h2><pre>';
var_dump($atts_input);
echo '</pre>';

$result = shortcode_atts($defaults, $atts_input);

echo '<h2>Nach shortcode_atts():</h2><pre>';
var_dump($result);
echo '</pre>';

echo '<h2>Type Checks:</h2>';
echo 'show_description === false? ' . ($result['show_description'] === false ? 'JA' : 'NEIN') . '<br>';
echo 'show_description === ""? ' . ($result['show_description'] === '' ? 'JA' : 'NEIN') . '<br>';
echo 'show_description === 0? ' . ($result['show_description'] === 0 ? 'JA' : 'NEIN') . '<br>';
echo 'Type: ' . gettype($result['show_description']);
