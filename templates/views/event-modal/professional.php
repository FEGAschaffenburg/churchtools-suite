<?php
/**
 * Modal Template - Professional
 *
 * Professional modal overlay for event details.
 * Displays event information in a modern, clean modal with sidebar layout.
 *
 * @package ChurchTools_Suite
 * @since   0.9.9.69
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- ChurchTools Suite Event Modal - Professional -->
<div id="cts-modal-overlay" class="cts-modal-overlay cts-modal-professional">
	<div class="cts-modal-container">
		
		<!-- Modal Header with Close Button -->
		<div class="cts-modal-header">
			<h2 id="cts-modal-title" class="cts-modal-title">
				<?php esc_html_e( 'Event Details', 'churchtools-suite' ); ?>
			</h2>
			<button id="cts-modal-close-btn" class="cts-modal-close-icon" aria-label="<?php esc_attr_e( 'Schließen', 'churchtools-suite' ); ?>" type="button">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		
		<div class="cts-modal-body">
			
			<!-- Loading State -->
			<div id="cts-modal-loading" class="cts-modal-loading">
				<div class="cts-spinner"></div>
				<p><?php esc_html_e( 'Loading event details...', 'churchtools-suite' ); ?></p>
			</div>
			
			<!-- Error State -->
			<div id="cts-modal-error" class="cts-modal-error" style="display: none;">
				<div class="cts-error-content">
					<span class="cts-error-icon dashicons dashicons-warning"></span>
					<h3><?php esc_html_e( 'Error Loading Event', 'churchtools-suite' ); ?></h3>
					<p id="cts-modal-error-message"></p>
				</div>
			</div>
			
			<!-- Event Content (populated by JavaScript) -->
			<div id="cts-modal-content" class="cts-modal-content" style="display: none;">
				
				<!-- Main: Image + Description -->
				<div class="cts-modal-main">
					
					<!-- Event Image -->
					<div id="cts-modal-image" class="cts-modal-image-container" style="display: none;">
						<img id="cts-modal-image-img" src="" alt="" class="cts-modal-image" />
					</div>
					
					<!-- Event Title -->
					<h1 id="cts-modal-event-title" class="cts-modal-event-title"></h1>
					
					<!-- Calendar Badge -->
					<div id="cts-modal-calendar" class="cts-modal-calendar-badge"></div>
					
					<!-- Event Description -->
					<div id="cts-modal-event-description" class="cts-modal-section" style="display: none;">
						<div id="cts-modal-event-description-content" class="cts-modal-description"></div>
					</div>
					
					<!-- Appointment Description -->
					<div id="cts-modal-appointment-description" class="cts-modal-section" style="display: none;">
						<h3><?php esc_html_e( 'Event Details', 'churchtools-suite' ); ?></h3>
						<div id="cts-modal-appointment-description-content" class="cts-modal-description"></div>
					</div>
					
					<!-- Services -->
					<div id="cts-modal-services" class="cts-modal-section" style="display: none;">
						<h3><?php esc_html_e( 'Services', 'churchtools-suite' ); ?></h3>
						<div id="cts-modal-services-list" class="cts-modal-services-list"></div>
					</div>
					
				</div>
				
				<!-- Sidebar: Meta Information -->
				<div class="cts-modal-sidebar">
					
					<!-- Date -->
					<div id="cts-modal-date" class="cts-modal-sidebar-section" style="display: none;">
						<div class="cts-modal-sidebar-header">
							<span class="dashicons dashicons-calendar-alt"></span>
							<span class="cts-modal-sidebar-label"><?php esc_html_e( 'DATE', 'churchtools-suite' ); ?></span>
						</div>
						<div id="cts-modal-date-value" class="cts-modal-sidebar-content"></div>
					</div>
					
					<!-- Time -->
					<div id="cts-modal-time" class="cts-modal-sidebar-section" style="display: none;">
						<div class="cts-modal-sidebar-header">
							<span class="dashicons dashicons-clock"></span>
							<span class="cts-modal-sidebar-label"><?php esc_html_e( 'TIME', 'churchtools-suite' ); ?></span>
						</div>
						<div id="cts-modal-time-value" class="cts-modal-sidebar-content"></div>
					</div>
					
					<!-- Tags/Labels -->
					<div id="cts-modal-tags" class="cts-modal-sidebar-section" style="display: none;">
						<div class="cts-modal-sidebar-header">
							<span class="dashicons dashicons-tag"></span>
							<span class="cts-modal-sidebar-label"><?php esc_html_e( 'LABELS', 'churchtools-suite' ); ?></span>
						</div>
						<div class="cts-modal-sidebar-content">
							<div id="cts-modal-tags-content" class="cts-modal-tags"></div>
						</div>
					</div>
					
					<!-- Location -->
					<div id="cts-modal-location" class="cts-modal-sidebar-section" style="display: none;">
						<div class="cts-modal-sidebar-header">
							<span class="dashicons dashicons-location-alt"></span>
							<span class="cts-modal-sidebar-label"><?php esc_html_e( 'LOCATION', 'churchtools-suite' ); ?></span>
						</div>
						<div id="cts-modal-location-value" class="cts-modal-sidebar-content"></div>
					</div>
					
				</div>
				
			</div>
			
		</div>
		
	</div>
</div>

<style>
	/* Modal Professional Styling */
	.cts-modal-professional .cts-modal-container {
		width: 90%;
		max-width: 900px;
		background: #fff;
		border-radius: 12px;
		box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
		display: flex;
		flex-direction: column;
		max-height: 90vh;
		overflow: hidden;
	}
	
	/* Modal Header */
	.cts-modal-professional .cts-modal-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20px 24px;
		border-bottom: 1px solid #e5e7eb;
		background: #f8fafc;
	}
	
	.cts-modal-professional .cts-modal-title {
		margin: 0;
		font-size: 20px;
		font-weight: 700;
		color: #1e293b;
	}
	
	.cts-modal-professional .cts-modal-close-icon {
		background: none;
		border: none;
		cursor: pointer;
		padding: 4px;
		color: #64748b;
		transition: color 0.15s;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	
	.cts-modal-professional .cts-modal-close-icon:hover {
		color: #1e293b;
	}
	
	.cts-modal-professional .cts-modal-close-icon .dashicons {
		width: 24px;
		height: 24px;
		font-size: 24px;
	}
	
	/* Modal Body */
	.cts-modal-professional .cts-modal-body {
		flex: 1;
		overflow-y: auto;
		padding: 0;
	}
	
	/* Modal Content Grid Layout */
	.cts-modal-professional .cts-modal-content {
		display: grid;
		grid-template-columns: 2fr 1fr;
		gap: 0;
		min-height: 100%;
	}
	
	/* Main Content */
	.cts-modal-professional .cts-modal-main {
		padding: 28px;
		border-right: 1px solid #e5e7eb;
		overflow-y: auto;
	}
	
	.cts-modal-professional .cts-modal-image-container {
		width: 100%;
		height: 200px;
		border-radius: 8px;
		overflow: hidden;
		margin-bottom: 20px;
	}
	
	.cts-modal-professional .cts-modal-image {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}
	
	.cts-modal-professional .cts-modal-event-title {
		font-size: 28px;
		font-weight: 700;
		color: #1e293b;
		margin: 0 0 16px 0;
		word-break: break-word;
		line-height: 1.3;
	}
	
	.cts-modal-professional .cts-modal-calendar-badge {
		display: inline-block;
		padding: 6px 12px;
		background: #e0e7ff;
		border-radius: 4px;
		font-size: 12px;
		font-weight: 600;
		color: #3730a3;
		margin-bottom: 16px;
	}
	
	.cts-modal-professional .cts-modal-section {
		margin-bottom: 24px;
	}
	
	.cts-modal-professional .cts-modal-section h3 {
		font-size: 15px;
		font-weight: 700;
		color: #1e293b;
		margin: 0 0 12px 0;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}
	
	.cts-modal-professional .cts-modal-description {
		font-size: 14px;
		line-height: 1.7;
		color: #64748b;
	}
	
	.cts-modal-professional .cts-modal-description p {
		margin: 0 0 12px 0;
	}
	
	.cts-modal-professional .cts-modal-description p:last-child {
		margin-bottom: 0;
	}
	
	/* Sidebar */
	.cts-modal-professional .cts-modal-sidebar {
		padding: 28px;
		background: #f8fafc;
		overflow-y: auto;
		display: flex;
		flex-direction: column;
		gap: 16px;
	}
	
	.cts-modal-professional .cts-modal-sidebar-section {
		background: #fff;
		border: 1px solid #e5e7eb;
		border-radius: 6px;
		padding: 14px;
	}
	
	.cts-modal-professional .cts-modal-sidebar-header {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 10px;
		font-weight: 700;
		color: #1e293b;
	}
	
	.cts-modal-professional .cts-modal-sidebar-header .dashicons {
		width: 18px;
		height: 18px;
		font-size: 18px;
		color: #2563eb;
	}
	
	.cts-modal-professional .cts-modal-sidebar-label {
		font-size: 11px;
		text-transform: uppercase;
		letter-spacing: 0.8px;
		flex: 1;
	}
	
	.cts-modal-professional .cts-modal-sidebar-content {
		font-size: 13px;
		color: #475569;
		line-height: 1.6;
	}
	
	/* Tags in Sidebar */
	.cts-modal-professional .cts-modal-tags {
		display: flex;
		flex-wrap: wrap;
		gap: 6px;
	}
	
	.cts-modal-professional .cts-modal-tags .cts-tag {
		display: inline-block;
		padding: 4px 10px;
		border-radius: 4px;
		font-size: 11px;
		font-weight: 600;
		color: #fff;
		white-space: nowrap;
	}
	
	/* Services List */
	.cts-modal-professional .cts-modal-services-list {
		list-style: none;
		margin: 0;
		padding: 0;
	}
	
	.cts-modal-professional .cts-modal-services-list li {
		padding: 8px 0;
		border-bottom: 1px solid #e5e7eb;
		font-size: 13px;
	}
	
	.cts-modal-professional .cts-modal-services-list li:last-child {
		border-bottom: none;
	}
	
	.cts-modal-professional .cts-modal-services-list strong {
		color: #1e293b;
		display: block;
		margin-bottom: 2px;
	}
	
	/* Loading State */
	.cts-modal-professional .cts-modal-loading {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 60px 20px;
		color: #64748b;
		text-align: center;
	}
	
	.cts-modal-professional .cts-spinner {
		width: 40px;
		height: 40px;
		border: 4px solid #e5e7eb;
		border-top: 4px solid #2563eb;
		border-radius: 50%;
		animation: spin 0.8s linear infinite;
		margin-bottom: 16px;
	}
	
	@keyframes spin {
		to { transform: rotate(360deg); }
	}
	
	/* Error State */
	.cts-modal-professional .cts-modal-error {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 40px 20px;
		text-align: center;
	}
	
	.cts-modal-professional .cts-error-content {
		max-width: 400px;
	}
	
	.cts-modal-professional .cts-error-icon {
		display: block;
		font-size: 48px;
		margin-bottom: 16px;
		color: #dc2626;
	}
	
	.cts-modal-professional .cts-error-content h3 {
		font-size: 18px;
		font-weight: 700;
		color: #1e293b;
		margin: 0 0 8px 0;
	}
	
	.cts-modal-professional .cts-error-content p {
		color: #64748b;
		margin: 0;
		font-size: 14px;
	}
	
	/* Responsive */
	@media (max-width: 768px) {
		.cts-modal-professional .cts-modal-container {
			width: 95%;
			max-height: 95vh;
		}
		
		.cts-modal-professional .cts-modal-content {
			grid-template-columns: 1fr;
		}
		
		.cts-modal-professional .cts-modal-main {
			border-right: none;
			border-bottom: 1px solid #e5e7eb;
			padding: 20px;
		}
		
		.cts-modal-professional .cts-modal-sidebar {
			padding: 20px;
		}
		
		.cts-modal-professional .cts-modal-event-title {
			font-size: 22px;
		}
		
		.cts-modal-professional .cts-modal-image-container {
			height: 160px;
			margin-bottom: 16px;
		}
	}
</style>
