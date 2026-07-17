<?php
/**
 * Debug Tab
 *
 * @package ChurchTools_Suite
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Logger class
require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-logger.php';
$subtab = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'uebersicht';
$subtabs = [
    'uebersicht' => 'Übersicht',
    'manuelle-trigger' => 'Manuelle Trigger',
    'logs' => 'Logs',
    'reset-cleanup' => 'Reset & Cleanup',
];
?>
<div class="cts-debug">
  <div class="cts-subtab-nav" style="margin-bottom:24px;display:flex;gap:12px;">
    <?php foreach ($subtabs as $key => $label): ?>
      <a href="?page=churchtools-suite&tab=debug&subtab=<?php echo esc_attr($key); ?>" class="cts-subtab<?php echo $subtab === $key ? ' active' : ''; ?>">
        <?php echo esc_html($label); ?>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="cts-debug-subtab-content">
    <?php
      switch ($subtab) {
        case 'manuelle-trigger':
          include __DIR__ . '/debug/subtab-manuelle-trigger.php';
          break;
        case 'logs':
          include __DIR__ . '/debug/subtab-logs.php';
          break;
        case 'reset-cleanup':
          include __DIR__ . '/debug/subtab-reset-cleanup.php';
          break;
        case 'uebersicht':
        default:
          include __DIR__ . '/debug/subtab-uebersicht.php';
          break;
      }
    ?>
  </div>
</div>
		return strpos( $name, 'churchtools-suite/' ) === 0;
