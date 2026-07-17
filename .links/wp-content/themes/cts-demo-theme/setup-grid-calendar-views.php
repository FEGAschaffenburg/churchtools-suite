<?php
/**
 * Complete Grid & Calendar View Setup
 * 
 * Löscht alte Seiten, erstellt neue mit korrekten Templates, organisiert Menü
 * 
 * Führe aus via CLI: php setup-grid-calendar-views.php
 */

// WordPress laden
$wp_load = dirname(__FILE__) . '/../../../../wp-load.php';
if (!file_exists($wp_load)) {
    $wp_load = dirname(__FILE__) . '/../../../wp-load.php';
}
require_once($wp_load);

// Sicherheitsprüfung: Nur mit Parameter ausführbar
if (!defined('WP_CLI') && (!isset($_GET['run']) || $_GET['run'] !== 'now')) {
    wp_die('Zugriff verweigert. Füge ?run=now zur URL hinzu.');
}

echo "========================================\n";
echo "Grid & Calendar View Setup\n";
echo "========================================\n\n";

// SCHRITT 1: Alte Seiten löschen
echo "SCHRITT 1: Alte Seiten löschen\n";
echo "----------------------------------------\n";

$pages_to_delete = [
    'grid-ansichten/grid-cards',
    'grid-ansichten/grid-compact',
    'grid-ansichten/grid-masonry',
    'grid-ansichten/grid-modern',
    'grid-ansichten/grid-simple',
    'grid-ansichten/grid-background-images',
    'calendar-ansichten/calendar-monthly',
];

$deleted_count = 0;
foreach ($pages_to_delete as $page_path) {
    $page = get_page_by_path($page_path);
    if ($page) {
        $result = wp_delete_post($page->ID, true);
        if ($result) {
            echo "✅ Gelöscht: {$page->post_title} (ID: {$page->ID})\n";
            $deleted_count++;
        }
    }
}
echo "→ {$deleted_count} Seiten gelöscht\n\n";

// SCHRITT 2: Neue Seiten erstellen
echo "SCHRITT 2: Neue Seiten erstellen\n";
echo "----------------------------------------\n";

$pages = [
    [
        'slug' => 'grid-cards',
        'title' => 'Grid Cards',
        'parent' => 'grid-ansichten',
        'template' => 'grid-cards-gutenberg.html'
    ],
    [
        'slug' => 'grid-compact',
        'title' => 'Grid Compact',
        'parent' => 'grid-ansichten',
        'template' => 'grid-compact-gutenberg.html'
    ],
    [
        'slug' => 'grid-masonry',
        'title' => 'Grid Masonry',
        'parent' => 'grid-ansichten',
        'template' => 'grid-masonry-gutenberg.html'
    ],
    [
        'slug' => 'calendar-monthly',
        'title' => 'Calendar Monthly',
        'parent' => 'calendar-ansichten',
        'template' => 'calendar-monthly-gutenberg.html'
    ],
];

$created_pages = [];

foreach ($pages as $page) {
    // Parent-Seite finden
    $parent = get_page_by_path($page['parent']);
    if (!$parent) {
        echo "❌ Parent nicht gefunden: {$page['parent']}\n";
        continue;
    }
    
    // Template laden
    $template_path = get_stylesheet_directory() . '/gutenberg-templates/' . $page['template'];
    if (!file_exists($template_path)) {
        echo "❌ Template nicht gefunden: {$page['template']}\n";
        continue;
    }
    
    $content = file_get_contents($template_path);
    
    // Seite erstellen
    $page_id = wp_insert_post([
        'post_title' => $page['title'],
        'post_name' => $page['slug'],
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_parent' => $parent->ID,
    ]);
    
    if (!is_wp_error($page_id)) {
        echo "✅ Erstellt: {$page['title']} (ID: {$page_id})\n";
        $created_pages[] = [
            'id' => $page_id,
            'title' => $page['title'],
            'parent' => $page['parent'],
            'slug' => $page['slug']
        ];
    } else {
        echo "❌ Fehler: {$page['title']} - {$page_id->get_error_message()}\n";
    }
}

echo "→ " . count($created_pages) . " Seiten erstellt\n\n";

// SCHRITT 3: Menü organisieren
echo "SCHRITT 3: Menü organisieren\n";
echo "----------------------------------------\n";

$locations = get_nav_menu_locations();
$menu_id = $locations['primary'] ?? null;

if (!$menu_id) {
    echo "❌ Hauptmenü nicht gefunden\n";
    exit(1);
}

// Parent-Menü-Items finden
$menu_items = wp_get_nav_menu_items($menu_id);
$grid_parent_id = null;
$calendar_parent_id = null;

foreach ($menu_items as $item) {
    if ($item->title === 'Grid-Ansichten' && $item->menu_item_parent == 0) {
        $grid_parent_id = $item->ID;
    }
    if ($item->title === 'Calendar-Ansichten' && $item->menu_item_parent == 0) {
        $calendar_parent_id = $item->ID;
    }
}

if (!$grid_parent_id || !$calendar_parent_id) {
    echo "❌ Menü-Parents nicht gefunden\n";
    exit(1);
}

// Seiten zum Menü hinzufügen
$grid_pages = [
    ['slug' => 'grid-ansichten/grid-cards', 'title' => 'Grid Cards'],
    ['slug' => 'grid-ansichten/grid-compact', 'title' => 'Grid Compact'],
    ['slug' => 'grid-ansichten/grid-masonry', 'title' => 'Grid Masonry'],
];

$calendar_pages = [
    ['slug' => 'calendar-ansichten/calendar-monthly', 'title' => 'Calendar Monthly'],
];

$menu_count = 0;

foreach ($grid_pages as $page_info) {
    $page = get_page_by_path($page_info['slug']);
    if ($page) {
        $menu_item_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $page->ID,
            'menu-item-object' => 'page',
            'menu-item-parent-id' => $grid_parent_id,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
            'menu-item-title' => $page_info['title'],
        ]);
        
        if (!is_wp_error($menu_item_id)) {
            echo "✅ Menü: {$page_info['title']} (Menu Item {$menu_item_id})\n";
            $menu_count++;
        }
    }
}

foreach ($calendar_pages as $page_info) {
    $page = get_page_by_path($page_info['slug']);
    if ($page) {
        $menu_item_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $page->ID,
            'menu-item-object' => 'page',
            'menu-item-parent-id' => $calendar_parent_id,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
            'menu-item-title' => $page_info['title'],
        ]);
        
        if (!is_wp_error($menu_item_id)) {
            echo "✅ Menü: {$page_info['title']} (Menu Item {$menu_item_id})\n";
            $menu_count++;
        }
    }
}

echo "→ {$menu_count} Menü-Einträge erstellt\n\n";

// ZUSAMMENFASSUNG
echo "========================================\n";
echo "✅ FERTIG!\n";
echo "========================================\n";
echo "Gelöscht: {$deleted_count} Seiten\n";
echo "Erstellt: " . count($created_pages) . " Seiten\n";
echo "Menü: {$menu_count} Einträge\n\n";

echo "Neue Seiten:\n";
foreach ($created_pages as $page) {
    $url = get_permalink($page['id']);
    echo "  → {$page['title']}: {$url}\n";
}

echo "\n✅ Setup abgeschlossen!\n";
