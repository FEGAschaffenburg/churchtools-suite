<?php
/**
 * Event Modal - Minimal
 * 
 * Sehr einfaches Modal ohne Schnörkel - nur die Basics
 * 
 * @package ChurchTools_Suite
 * @since   0.9.9.85
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="cts-modal-overlay" id="cts-modal-overlay">
	<div class="cts-modal-container cts-modal-minimal">
		<div class="cts-modal-content">
			
			<!-- Close Button -->
			<button class="cts-modal-close" id="cts-modal-close" aria-label="Modal schließen">
				<span aria-hidden="true">×</span>
			</button>
			
			<!-- Event Title -->
			<h2 id="cts-modal-event-title" class="cts-modal-title"></h2>
			
			<!-- Calendar Badge -->
			<div id="cts-modal-calendar" class="cts-modal-calendar"></div>
			
			<!-- Date & Time -->
			<div id="cts-modal-datetime" class="cts-modal-datetime">
				<div id="cts-modal-date"></div>
				<div id="cts-modal-time"></div>
			</div>
			
			<!-- Location -->
			<div id="cts-modal-location" class="cts-modal-location" style="display: none;">
				<strong>📍 Ort:</strong>
				<div id="cts-modal-location-value"></div>
			</div>
			
			<!-- Event Description -->
			<div id="cts-modal-event-description" class="cts-modal-section" style="display: none;">
				<div id="cts-modal-event-description-content"></div>
			</div>
			
			<!-- Appointment Description -->
			<div id="cts-modal-appointment-description" class="cts-modal-section" style="display: none;">
				<div id="cts-modal-appointment-description-content"></div>
			</div>
			
		</div>
	</div>
</div>

<style>
.cts-modal-minimal {
	max-width: 500px;
}

.cts-modal-minimal .cts-modal-title {
	font-size: 1.5rem;
	margin: 0 0 1rem 0;
	color: #1e293b;
	border-left: 3px solid var(--cts-calendar-color, #3498db);
	padding-left: 0.75rem;
}

.cts-modal-minimal .cts-modal-calendar {
	display: inline-block;
	padding: 4px 12px;
	background: var(--cts-calendar-color, #e2e8f0);
	color: #ffffff;
	border-radius: 4px;
	font-size: 0.85rem;
	margin-bottom: 1rem;
}

.cts-modal-minimal .cts-modal-datetime {
	margin-bottom: 1.5rem;
	padding: 0.75rem;
	background: #f8fafc;
	border-radius: 6px;
	font-size: 0.95rem;
}

.cts-modal-minimal .cts-modal-location,
.cts-modal-minimal .cts-modal-section {
	margin-bottom: 1rem;
	line-height: 1.6;
}

.cts-modal-minimal .cts-modal-location strong {
	display: block;
	margin-bottom: 0.5rem;
	color: #64748b;
	font-size: 0.9rem;
}
</style>
