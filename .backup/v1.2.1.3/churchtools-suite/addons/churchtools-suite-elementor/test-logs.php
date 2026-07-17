<?php
/**
 * Debug Script: Display Elementor Integration Logs
 * 
 * Access via: /wp-content/plugins/churchtools-suite-elementor/test-logs.php
 * 
 * @package ChurchTools_Suite_Elementor
 * @since   0.6.1
 */

// Load WordPress
require_once '../../../wp-load.php';

// Security check
if (!current_user_can('manage_options')) {
	wp_die('Zugriff verweigert');
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>CTS Elementor - Debug Logs</title>
	<style>
		body {
			font-family: monospace;
			padding: 20px;
			background: #1e1e1e;
			color: #d4d4d4;
		}
		.log-entry {
			padding: 5px;
			border-bottom: 1px solid #333;
		}
		.header {
			background: #0078d4;
			color: white;
			padding: 15px;
			margin-bottom: 20px;
			border-radius: 4px;
		}
		.info {
			background: #264f78;
			padding: 10px;
			margin-bottom: 10px;
			border-radius: 4px;
		}
		button {
			background: #0078d4;
			color: white;
			border: none;
			padding: 10px 20px;
			cursor: pointer;
			border-radius: 4px;
			margin: 5px;
		}
		button:hover {
			background: #005a9e;
		}
	</style>
</head>
<body>
	<div class="header">
		<h1>🔍 ChurchTools Suite Elementor - Debug Logs</h1>
		<p>Live-Monitoring der Integration</p>
	</div>
	
	<div class="info">
		<strong>Plugin Version:</strong> <?php echo defined('CTS_ELEMENTOR_VERSION') ? CTS_ELEMENTOR_VERSION : 'Nicht geladen'; ?><br>
		<strong>WordPress Version:</strong> <?php echo get_bloginfo('version'); ?><br>
		<strong>Elementor aktiv:</strong> <?php echo class_exists('\\Elementor\\Plugin') ? '✅ Ja' : '❌ Nein'; ?><br>
		<strong>ChurchTools Suite aktiv:</strong> <?php echo class_exists('ChurchTools_Suite') ? '✅ Ja' : '❌ Nein'; ?><br>
		<strong>Aktualisiert:</strong> <?php echo date('d.m.Y H:i:s'); ?>
	</div>
	
	<div style="margin-bottom: 20px;">
		<button onclick="location.reload()">🔄 Logs aktualisieren</button>
		<button onclick="clearLogs()">🗑️ Logs löschen</button>
	</div>
	
	<h2>📋 Log-Einträge (letzte 50):</h2>
	
	<?php
	$logs = get_option('cts_elementor_log', []);
	
	if (empty($logs)) {
		echo '<p style="color: #808080;">Keine Logs vorhanden. Öffne Elementor-Editor um Logs zu generieren.</p>';
	} else {
		foreach (array_reverse($logs) as $log) {
			echo '<div class="log-entry">' . esc_html($log) . '</div>';
		}
	}
	?>
	
	<script>
	function clearLogs() {
		if (confirm('Alle Logs löschen?')) {
			fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'action=cts_elementor_clear_logs&nonce=<?php echo wp_create_nonce('cts_elementor_clear_logs'); ?>'
			}).then(() => location.reload());
		}
	}
	
	// Auto-refresh alle 5 Sekunden
	setTimeout(() => location.reload(), 5000);
	</script>
</body>
</html>
