<?php
/**
 * Debug/Erweitert Subtab: Übersicht
 *
 * @package ChurchTools_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cts-debug-subtab-content">
	<h2>🔎 Übersicht</h2>
	<p>Hier finden Sie eine Übersicht der wichtigsten System- und Debug-Informationen.</p>
	<?php
	// Optional: Systeminfos, letzte Syncs, Log-Auszug etc. einbinden
	include __DIR__ . '/../tab-debug-minimal.php';
	?>
</div>
