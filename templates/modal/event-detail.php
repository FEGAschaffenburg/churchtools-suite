<?php
/**
 * Template: Event Detail Modal
 * 
 * Displays full event details in a modal popup.
 * Triggered by detail buttons in grid/list views.
 *
 * @package ChurchTools_Suite
 * @since   0.5.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Modal Overlay -->
<div id="cts-modal-overlay" class="cts-modal-overlay">
	<div class="cts-modal-container">
		<div class="cts-modal-content">
			
			<!-- Modal Header -->
			<div class="cts-modal-header">
				<h2 class="cts-modal-title" id="cts-modal-title"></h2>
				<button class="cts-modal-close" id="cts-modal-close" aria-label="<?php esc_attr_e( 'Schließen', 'churchtools-suite' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="18" y1="6" x2="6" y2="18"/>
						<line x1="6" y1="6" x2="18" y2="18"/>
					</svg>
				</button>
			</div>
			
			<!-- Modal Body -->
			<div class="cts-modal-body" id="cts-modal-body">
				
				<!-- Loading State -->
				<div class="cts-modal-loading" id="cts-modal-loading">
					<div class="cts-modal-spinner"></div>
					<p><?php esc_html_e( 'Lädt Event-Details...', 'churchtools-suite' ); ?></p>
				</div>
				
				<!-- Event Details (will be populated via JS) -->
				<div class="cts-modal-details" id="cts-modal-details" style="display: none;">
					
					<!-- Calendar Badge -->
					<div class="cts-modal-calendar-badge" id="cts-modal-calendar"></div>
					
					<!-- Date & Time -->
					<div class="cts-modal-datetime">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
							<line x1="16" y1="2" x2="16" y2="6"/>
							<line x1="8" y1="2" x2="8" y2="6"/>
							<line x1="3" y1="10" x2="21" y2="10"/>
						</svg>
						<div>
							<div class="cts-modal-date" id="cts-modal-date"></div>
							<div class="cts-modal-time" id="cts-modal-time"></div>
						</div>
					</div>
					
					<!-- Location -->
					<div class="cts-modal-location" id="cts-modal-location-wrapper" style="display: none;">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
							<circle cx="12" cy="10" r="3"/>
						</svg>
						<span id="cts-modal-location"></span>
					</div>
					
					<!-- Description -->
					<div class="cts-modal-description" id="cts-modal-description-wrapper" style="display: none;">
						<h3><?php esc_html_e( 'Beschreibung', 'churchtools-suite' ); ?></h3>
						<div id="cts-modal-description"></div>
					</div>
					
					<!-- Services -->
					<div class="cts-modal-services" id="cts-modal-services-wrapper" style="display: none;">
						<h3><?php esc_html_e( 'Dienste', 'churchtools-suite' ); ?></h3>
						<div class="cts-modal-services-list" id="cts-modal-services"></div>
					</div>
					
				</div>
				
				<!-- Error State -->
				<div class="cts-modal-error" id="cts-modal-error" style="display: none;">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="10"/>
						<line x1="12" y1="8" x2="12" y2="12"/>
						<line x1="12" y1="16" x2="12.01" y2="16"/>
					</svg>
					<p><?php esc_html_e( 'Event konnte nicht geladen werden.', 'churchtools-suite' ); ?></p>
				</div>
				
			</div>
			
			<!-- Modal Footer -->
			<div class="cts-modal-footer">
				<button class="cts-modal-btn-secondary" id="cts-modal-close-btn">
					<?php esc_html_e( 'Schließen', 'churchtools-suite' ); ?>
				</button>
			</div>
			
		</div>
	</div>
</div>

<style>
/* Modal Overlay */
.cts-modal-overlay {
	display: none;
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, 0.75);
	backdrop-filter: blur(4px);
	z-index: 999999;
	animation: cts-modal-fade-in 0.2s ease;
}

.cts-modal-overlay.active {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 20px;
}

@keyframes cts-modal-fade-in {
	from {
		opacity: 0;
	}
	to {
		opacity: 1;
	}
}

/* Modal Container */
.cts-modal-container {
	background: #fff;
	border-radius: 12px;
	max-width: 700px;
	width: 100%;
	max-height: 90vh;
	overflow: hidden;
	box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
	animation: cts-modal-slide-up 0.3s ease;
}

@keyframes cts-modal-slide-up {
	from {
		transform: translateY(30px);
		opacity: 0;
	}
	to {
		transform: translateY(0);
		opacity: 1;
	}
}

/* Modal Header */
.cts-modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 24px 28px;
	border-bottom: 1px solid #e5e7eb;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.cts-modal-title {
	margin: 0;
	font-size: 22px;
	font-weight: 600;
	color: #fff;
	line-height: 1.3;
	padding-right: 20px;
}

.cts-modal-close {
	flex-shrink: 0;
	width: 36px;
	height: 36px;
	border: none;
	background: rgba(255, 255, 255, 0.2);
	color: #fff;
	border-radius: 8px;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.2s;
}

.cts-modal-close:hover {
	background: rgba(255, 255, 255, 0.3);
	transform: rotate(90deg);
}

.cts-modal-close svg {
	width: 20px;
	height: 20px;
}

/* Modal Body */
.cts-modal-body {
	padding: 28px;
	max-height: calc(90vh - 180px);
	overflow-y: auto;
}

/* Loading State */
.cts-modal-loading {
	text-align: center;
	padding: 40px 20px;
}

.cts-modal-spinner {
	width: 48px;
	height: 48px;
	border: 4px solid #f3f4f6;
	border-top-color: #667eea;
	border-radius: 50%;
	animation: cts-modal-spin 0.8s linear infinite;
	margin: 0 auto 16px;
}

@keyframes cts-modal-spin {
	to {
		transform: rotate(360deg);
	}
}

.cts-modal-loading p {
	color: #6b7280;
	font-size: 14px;
	margin: 0;
}

/* Event Details */
.cts-modal-details {
	display: flex;
	flex-direction: column;
	gap: 24px;
}

.cts-modal-calendar-badge {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 8px 16px;
	border-radius: 20px;
	font-size: 14px;
	font-weight: 600;
	color: #fff;
	width: fit-content;
}

.cts-modal-datetime {
	display: flex;
	gap: 16px;
	padding: 16px;
	background: #f9fafb;
	border-radius: 8px;
	border-left: 4px solid #667eea;
}

.cts-modal-datetime svg {
	flex-shrink: 0;
	color: #667eea;
}

.cts-modal-date {
	font-size: 16px;
	font-weight: 600;
	color: #1f2937;
	margin-bottom: 4px;
}

.cts-modal-time {
	font-size: 14px;
	color: #6b7280;
}

.cts-modal-location {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px 16px;
	background: #fef3c7;
	border-radius: 8px;
	font-size: 14px;
	color: #78350f;
}

.cts-modal-location svg {
	flex-shrink: 0;
	color: #f59e0b;
}

.cts-modal-description h3,
.cts-modal-services h3 {
	font-size: 16px;
	font-weight: 600;
	color: #1f2937;
	margin: 0 0 12px;
}

.cts-modal-description {
	line-height: 1.7;
	color: #374151;
	font-size: 15px;
}

.cts-modal-services-list {
	display: flex;
	flex-wrap: wrap;
	gap: 12px;
}

.cts-modal-service-item {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 10px 16px;
	background: #f3f4f6;
	border-radius: 8px;
	font-size: 14px;
}

.cts-modal-service-name {
	font-weight: 600;
	color: #1f2937;
}

.cts-modal-service-person {
	color: #6b7280;
}

/* Error State */
.cts-modal-error {
	text-align: center;
	padding: 40px 20px;
	color: #dc2626;
}

.cts-modal-error svg {
	margin-bottom: 16px;
}

.cts-modal-error p {
	margin: 0;
	font-size: 15px;
}

/* Modal Footer */
.cts-modal-footer {
	padding: 20px 28px;
	border-top: 1px solid #e5e7eb;
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	background: #f9fafb;
}

.cts-modal-btn-secondary {
	padding: 10px 20px;
	border: 1px solid #d1d5db;
	background: #fff;
	color: #374151;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 500;
	cursor: pointer;
	transition: all 0.2s;
}

.cts-modal-btn-secondary:hover {
	background: #f3f4f6;
	border-color: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
	.cts-modal-container {
		max-width: 100%;
		max-height: 100vh;
		border-radius: 0;
	}
	
	.cts-modal-header,
	.cts-modal-body,
	.cts-modal-footer {
		padding: 20px;
	}
	
	.cts-modal-title {
		font-size: 18px;
	}
	
	.cts-modal-body {
		max-height: calc(100vh - 160px);
	}
}
</style>
