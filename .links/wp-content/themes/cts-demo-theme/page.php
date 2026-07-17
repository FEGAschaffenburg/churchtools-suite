<?php
/**
 * Standard Fallback Page Template
 *
 * Falls kein spezifisches Page Template ausgewählt wurde,
 * wird dieses Standard-Template verwendet.
 *
 * @package CTS_Demo_Theme
 * @since 1.0.0
 */

// Use the unified standard page template
locate_template( 'page-templates/standard-page.php', true );
