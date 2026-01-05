<?php
/**
 * CSS Load Debug
 * 
 * Aufruf: /wp-content/plugins/churchtools-suite/debug-css-load.php
 */

define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Keine Berechtigung');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ChurchTools Suite - CSS Debug</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .test-box { border: 2px solid #ddd; padding: 20px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>ChurchTools Suite - CSS Load Debug</h1>
    
    <h2>1. Plugin Info</h2>
    <pre><?php
    echo "Version: " . (defined('CHURCHTOOLS_SUITE_VERSION') ? CHURCHTOOLS_SUITE_VERSION : 'NICHT DEFINIERT') . "\n";
    echo "Pfad: " . (defined('CHURCHTOOLS_SUITE_PATH') ? CHURCHTOOLS_SUITE_PATH : 'NICHT DEFINIERT') . "\n";
    echo "URL: " . (defined('CHURCHTOOLS_SUITE_URL') ? CHURCHTOOLS_SUITE_URL : 'NICHT DEFINIERT') . "\n";
    ?></pre>
    
    <h2>2. CSS-Datei Check</h2>
    <?php
    $css_path = CHURCHTOOLS_SUITE_PATH . 'assets/css/churchtools-suite-public.css';
    $css_url = CHURCHTOOLS_SUITE_URL . 'assets/css/churchtools-suite-public.css';
    
    if (file_exists($css_path)) {
        echo '<p class="success">✅ CSS-Datei existiert</p>';
        echo '<p>Pfad: <code>' . $css_path . '</code></p>';
        echo '<p>URL: <code>' . $css_url . '</code></p>';
        echo '<p>Größe: ' . round(filesize($css_path) / 1024, 2) . ' KB</p>';
        echo '<p>Geändert: ' . date('Y-m-d H:i:s', filemtime($css_path)) . '</p>';
        
        // Prüfe Media Queries
        $css_content = file_get_contents($css_path);
        
        echo '<h3>3. Media Queries Check</h3>';
        if (strpos($css_content, '@media (max-width: 480px)') !== false) {
            echo '<p class="success">✅ Mobile Media Query (480px) vorhanden</p>';
        } else {
            echo '<p class="error">❌ Mobile Media Query (480px) FEHLT!</p>';
        }
        
        if (strpos($css_content, '@media (max-width: 768px)') !== false) {
            echo '<p class="success">✅ Tablet Media Query (768px) vorhanden</p>';
        } else {
            echo '<p class="error">❌ Tablet Media Query (768px) FEHLT!</p>';
        }
        
        // Zeige relevante CSS-Zeilen
        echo '<h3>4. CSS Inhalt (Ausschnitt)</h3>';
        echo '<pre style="max-height: 400px; overflow-y: scroll;">';
        
        // Finde und zeige Media Query Bereich
        $lines = explode("\n", $css_content);
        $in_media = false;
        $count = 0;
        foreach ($lines as $num => $line) {
            if (strpos($line, '@media (max-width:') !== false || strpos($line, '@media (max-width :') !== false) {
                $in_media = true;
                $count = 0;
            }
            
            if ($in_media) {
                echo 'Line ' . ($num + 1) . ': ' . htmlspecialchars($line) . "\n";
                $count++;
                if ($count > 30) $in_media = false;
            }
        }
        echo '</pre>';
        
    } else {
        echo '<p class="error">❌ CSS-Datei NICHT gefunden!</p>';
        echo '<p>Erwarteter Pfad: <code>' . $css_path . '</code></p>';
    }
    ?>
    
    <h2>5. WordPress CSS Enqueue Check</h2>
    <?php
    global $wp_styles;
    if (isset($wp_styles->registered['churchtools-suite-public'])) {
        $style = $wp_styles->registered['churchtools-suite-public'];
        echo '<p class="success">✅ CSS ist in WordPress registriert</p>';
        echo '<pre>';
        echo 'Handle: ' . $style->handle . "\n";
        echo 'Source: ' . $style->src . "\n";
        echo 'Version: ' . $style->ver . "\n";
        echo '</pre>';
    } else {
        echo '<p class="error">❌ CSS ist NICHT in WordPress registriert!</p>';
        echo '<p>Verfügbare Styles: ' . implode(', ', array_keys($wp_styles->registered)) . '</p>';
    }
    ?>
    
    <h2>6. Live Test</h2>
    <p>CSS-Datei direkt laden:</p>
    <link rel="stylesheet" href="<?php echo $css_url . '?v=' . time(); ?>">
    
    <div class="test-box cts-event-classic">
        <div class="cts-date">7. JAN.</div>
        <div class="cts-time">20:00 Uhr - 21:15 Uhr</div>
        <div class="cts-title-block">
            <span class="cts-title">Test Event</span>
            <span class="cts-description"> - Dies ist eine Test-Beschreibung</span>
        </div>
    </div>
    
    <p><strong>Teste responsive Ansicht:</strong> Browser-Entwicklertools öffnen (F12) → Responsive-Modus → Breite ändern</p>
    
    <script>
    console.log('ChurchTools Suite CSS Debug geladen');
    console.log('CSS URL:', '<?php echo $css_url; ?>');
    </script>
</body>
</html>
