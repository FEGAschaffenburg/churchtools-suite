<?php
/**
 * Single Event Template - Professional
 *
 * Professional event detail layout with hero image, description, and sidebar details.
 * Layout: Large hero image + title + description on left, metadata sidebar on right
 *
 * Variables available: $event, $calendar
 *
 * @package ChurchTools_Suite
 * @since   0.9.9.68
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prepare timezone
$timezone_string = get_option( 'timezone_string' );
if ( ! empty( $timezone_string ) ) {
	$timezone = new DateTimeZone( $timezone_string );
} else {
	$timezone = wp_timezone();
}

// Format dates
$start_date = '';
$start_time = '';
$end_time = '';
$day = '';
$month_str = '';

if ( ! empty( $event->start_datetime ) ) {
	$start_dt = new DateTime( $event->start_datetime, new DateTimeZone( 'UTC' ) );
	$start_dt->setTimezone( $timezone );
	$start_date = date_i18n( get_option( 'date_format' ), $start_dt->getTimestamp() );
	$start_time = date_i18n( get_option( 'time_format' ), $start_dt->getTimestamp() );
	$day = date_i18n( 'd', $start_dt->getTimestamp() );
	$month_str = date_i18n( 'M Y', $start_dt->getTimestamp() );
}

if ( ! empty( $event->end_datetime ) ) {
	$end_dt = new DateTime( $event->end_datetime, new DateTimeZone( 'UTC' ) );
	$end_dt->setTimezone( $timezone );
	$end_time = date_i18n( get_option( 'time_format' ), $end_dt->getTimestamp() );
}

// Parse tags (Labels)
$tags = [];
if ( ! empty( $event->tags ) ) {
	$tags_data = json_decode( $event->tags, true );
	if ( is_array( $tags_data ) ) {
		$tags = $tags_data;
	}
}

// Get image (fallback chain: event → calendar → dummy)
require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-image-helper.php';
$image_url = ChurchTools_Suite_Image_Helper::get_image_url( $event );

// Get location address
$location_name = $event->address_name ?? $event->location_name ?? '';
$location_city = $event->address_city ?? '';
$location_street = $event->address_street ?? '';
$location_zip = $event->address_zip ?? '';

// Build full location string
$location_full = [];
if ( ! empty( $location_name ) ) {
	$location_full[] = $location_name;
}
if ( ! empty( $location_street ) ) {
	$location_full[] = $location_street;
}
if ( ! empty( $location_zip ) ) {
	$location_full[] = $location_zip;
}
if ( ! empty( $location_city ) ) {
	$location_full[] = $location_city;
}

// Calendar color (for styling)
$calendar_color = '#2563eb';
if ( ! empty( $calendar ) && ! empty( $calendar->color ) ) {
	$calendar_color = $calendar->color;
}

// Get descriptions
$event_description = $event->event_description ?? '';
$appointment_description = $event->appointment_description ?? '';
$full_description = '';

if ( ! empty( $event_description ) ) {
	$full_description = $event_description;
}
if ( ! empty( $appointment_description ) ) {
	if ( ! empty( $full_description ) ) {
		$full_description .= "\n\n";
	}
	$full_description .= $appointment_description;
}

// Apply WordPress text filters
$full_description = wpautop( wp_kses_post( $full_description ) );
?>

<div class="cts-single-professional" style="--calendar-color: <?php echo esc_attr( $calendar_color ); ?>">
	<div class="cts-single-container">
		
		<!-- Main Content (Left) -->
		<div class="cts-single-main">
			echo "Pro";
			<!-- Hero Image -->
			<?php if ( ! empty( $image_url ) ) : ?>
				<div class="cts-single-hero">
					<img src="<?php echo esc_url( $image_url ); ?>" 
					     alt="<?php echo esc_attr( $event->title ?? '' ); ?>"
					     class="cts-single-hero-image" />
				</div>
			<?php endif; ?>
			
			<!-- Event Title -->
			<h1 class="cts-single-title">
				<?php echo esc_html( 'pro'. $event->title ?? __( 'Untitled Event', 'churchtools-suite' ) ); ?>
			</h1>
			
			<!-- Event Description -->
			<?php if ( ! empty( $full_description ) ) : ?>
				<div class="cts-single-description">
					<?php echo wp_kses_post( $full_description ); ?>
				</div>
			<?php endif; ?>
			
		</div>
		
		<!-- Sidebar (Right) -->
		<div class="cts-single-sidebar">
			
			<!-- Date Box -->
			<div class="cts-sidebar-section">
				<div class="cts-sidebar-header">
					<span class="dashicons dashicons-calendar-alt"></span>
					<span class="cts-sidebar-label"><?php esc_html_e( 'DATE', 'churchtools-suite' ); ?></span>
				</div>
				<div class="cts-sidebar-content">
					<?php echo esc_html( $month_str ); ?>
				</div>
			</div>
			
			<!-- Time Box -->
			<?php if ( ! empty( $start_time ) ) : ?>
				<div class="cts-sidebar-section">
					<div class="cts-sidebar-header">
						<span class="dashicons dashicons-clock"></span>
						<span class="cts-sidebar-label"><?php esc_html_e( 'TIME', 'churchtools-suite' ); ?></span>
					</div>
					<div class="cts-sidebar-content">
						<?php 
							echo esc_html( $start_time );
							if ( ! empty( $end_time ) ) {
								echo ' - ' . esc_html( $end_time );
							}
						?>
					</div>
				</div>
			<?php endif; ?>
			
			<!-- Local Time Box -->
			<?php if ( ! empty( $timezone_string ) ) : ?>
				<div class="cts-sidebar-section">
					<div class="cts-sidebar-header">
						<span class="dashicons dashicons-globe"></span>
						<span class="cts-sidebar-label"><?php esc_html_e( 'LOCAL TIME', 'churchtools-suite' ); ?></span>
					</div>
					<div class="cts-sidebar-content">
						<div class="cts-local-time-item">
							<span class="cts-local-time-label"><?php esc_html_e( 'Timezone:', 'churchtools-suite' ); ?></span>
							<span><?php echo esc_html( $timezone_string ); ?></span>
						</div>
						<?php if ( ! empty( $start_date ) ) : ?>
							<div class="cts-local-time-item">
								<span class="cts-local-time-label"><?php esc_html_e( 'Date:', 'churchtools-suite' ); ?></span>
								<span><?php echo esc_html( $start_date ); ?></span>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $start_time ) ) : ?>
							<div class="cts-local-time-item">
								<span class="cts-local-time-label"><?php esc_html_e( 'Time:', 'churchtools-suite' ); ?></span>
								<span>
									<?php 
										echo esc_html( $start_time );
										if ( ! empty( $end_time ) ) {
											echo ' - ' . esc_html( $end_time );
										}
									?>
								</span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			
			<!-- Labels/Tags -->
			<?php if ( ! empty( $tags ) ) : ?>
				<div class="cts-sidebar-section">
					<div class="cts-sidebar-header">
						<span class="dashicons dashicons-tag"></span>
						<span class="cts-sidebar-label"><?php esc_html_e( 'LABELS', 'churchtools-suite' ); ?></span>
					</div>
					<div class="cts-sidebar-content">
						<div class="cts-single-tags">
							<?php foreach ( $tags as $tag ) : ?>
								<span class="cts-single-tag" style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>">
									<?php echo esc_html( $tag['name'] ?? '' ); ?>
								</span>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
			
			<!-- Location Box with Map/Image -->
			<?php if ( ! empty( $location_full ) ) : ?>
				<div class="cts-sidebar-section">
					<div class="cts-sidebar-header">
						<span class="dashicons dashicons-location-alt"></span>
						<span class="cts-sidebar-label"><?php esc_html_e( 'LOCATION', 'churchtools-suite' ); ?></span>
					</div>
					<div class="cts-sidebar-content">
						
						<!-- Location Map/Image -->
						<?php if ( ! empty( $event->image_url ) || ! empty( $image_url ) ) : ?>
							<div class="cts-location-image-container">
								<img src="<?php echo esc_url( $event->image_url ?? $image_url ); ?>" 
								     alt="<?php echo esc_attr( implode( ', ', $location_full ) ); ?>"
								     class="cts-location-image" />
							</div>
						<?php endif; ?>
						
						<!-- Location Info -->
						<div class="cts-location-info">
							<?php if ( ! empty( $location_name ) ) : ?>
								<div class="cts-location-name">
									<?php echo esc_html( $location_name ); ?>
								</div>
							<?php endif; ?>
							
							<?php if ( ! empty( $location_street ) || ! empty( $location_zip ) || ! empty( $location_city ) ) : ?>
								<div class="cts-location-address">
									<?php if ( ! empty( $location_street ) ) : ?>
										<?php echo esc_html( $location_street ); ?><br />
									<?php endif; ?>
									<?php if ( ! empty( $location_zip ) ) : ?>
										<?php echo esc_html( $location_zip ); ?>
									<?php endif; ?>
									<?php if ( ! empty( $location_city ) ) : ?>
										<?php echo esc_html( $location_city ); ?>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
						
					</div>
				</div>
			<?php endif; ?>
			
		</div>
		
	</div>
</div>

<style>
	:root {
		--calendar-color: #2563eb;
	}
	
	.cts-single-professional {
		background: #fff;
		padding: 20px;
	}
	
	.cts-single-container {
		display: grid;
		grid-template-columns: 2fr 1fr;
		gap: 40px;
		max-width: 1200px;
		margin: 0 auto;
	}
	
	/* Main Content */
	.cts-single-main {
		padding: 0;
	}
	
	.cts-single-hero {
		width: 100%;
		height: 400px;
		overflow: hidden;
		border-radius: 8px;
		margin-bottom: 30px;
	}
	
	.cts-single-hero-image {
		width: 100%;
		height: 100%;
		object-fit: cover;
		object-position: center;
		display: block;
	}
	
	.cts-single-title {
		color: #1e293b;
		font-size: 36px;
		font-weight: 700;
		line-height: 1.3;
		margin: 0 0 20px 0;
		word-break: break-word;
	}
	
	.cts-single-description {
		color: #64748b;
		font-size: 15px;
		line-height: 1.8;
		margin: 0;
	}
	
	.cts-single-description p {
		margin-bottom: 15px;
	}
	
	.cts-single-description p:last-child {
		margin-bottom: 0;
	}
	
	/* Sidebar */
	.cts-single-sidebar {
		display: flex;
		flex-direction: column;
		gap: 24px;
	}
	
	.cts-sidebar-section {
		background: #f8fafc;
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		padding: 16px;
		overflow: hidden;
	}
	
	.cts-sidebar-header {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 12px;
		font-weight: 700;
		color: #1e293b;
	}
	
	.cts-sidebar-header .dashicons {
		width: 20px;
		height: 20px;
		font-size: 20px;
		color: var(--calendar-color);
		flex-shrink: 0;
	}
	
	.cts-sidebar-label {
		font-size: 13px;
		text-transform: uppercase;
		letter-spacing: 1px;
	}
	
	.cts-sidebar-content {
		color: #475569;
		font-size: 14px;
	}
	
	/* Tags */
	.cts-single-tags {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}
	
	.cts-single-tag {
		display: inline-block;
		padding: 6px 12px;
		border-radius: 4px;
		font-size: 12px;
		font-weight: 600;
		color: #fff;
		white-space: nowrap;
	}
	
	/* Location Info */
	.cts-location-image-container {
		width: 100%;
		height: 180px;
		overflow: hidden;
		border-radius: 6px;
		margin-bottom: 12px;
	}
	
	.cts-location-image {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
	}
	
	.cts-location-name {
		font-weight: 700;
		font-size: 14px;
		color: #1e293b;
		margin-bottom: 4px;
	}
	
	.cts-location-address {
		font-size: 13px;
		color: #64748b;
		line-height: 1.6;
	}
	
	/* Local Time Layout */
	.cts-local-time-item {
		display: flex;
		flex-direction: column;
		margin-bottom: 8px;
	}
	
	.cts-local-time-item:last-child {
		margin-bottom: 0;
	}
	
	.cts-local-time-label {
		font-weight: 600;
		font-size: 12px;
		color: #475569;
		margin-bottom: 2px;
	}
	
	/* Responsive */
	@media (max-width: 768px) {
		.cts-single-container {
			grid-template-columns: 1fr;
			gap: 30px;
		}
		
		.cts-single-hero {
			height: 280px;
			margin-bottom: 20px;
		}
		
		.cts-single-title {
			font-size: 28px;
		}
		
		.cts-single-description {
			font-size: 14px;
		}
		
		.cts-single-sidebar {
			gap: 16px;
		}
		
		.cts-location-image-container {
			height: 200px;
		}
	}
</style>
