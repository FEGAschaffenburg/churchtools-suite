<?php
/**
 * Render Subtabs Partial
 * Expects:
 * - $subtabs (associative array slug => label)
 * - $subtab_active (string) active slug
 * - $subtab_parent_tab (string) parent tab slug for URL (optional, defaults to current ?tab)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( empty( $subtabs ) || ! is_array( $subtabs ) ) {
    return;
}

$active = isset( $subtab_active ) ? (string) $subtab_active : '';
$parent_tab = isset( $subtab_parent_tab ) ? (string) $subtab_parent_tab : ( isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings' );

?>
<!-- Sub-Navigation -->
<div class="cts-sub-tabs">
    <?php foreach ( $subtabs as $key => $label ) : ?>
        <a href="?page=churchtools-suite&tab=<?php echo esc_attr( $parent_tab ); ?>&subtab=<?php echo esc_attr( $key ); ?>" class="cts-sub-tab <?php echo $active === (string) $key ? 'active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
    <?php endforeach; ?>
</div>
