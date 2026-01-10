<?php
/**
 * Single Event Template - Professional (Modern 2-Column Design)
 *
 * @package ChurchTools_Suite
 * @since   0.9.9.91
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// DEBUG: Verify template loaded
echo '<!-- ChurchTools Suite Professional Template v0.9.9.91 LOADED -->';

// Timezone
$timezone = ! empty( get_option( 'timezone_string' ) ) 
	? new DateTimeZone( get_option( 'timezone_string' ) ) 
	: wp_timezone();

// Format dates
$start_date_short = '';
$start_date_long = '';
$start_time = '';
$end_time = '';

if ( ! empty( $event->start_datetime ) ) {
	$start_dt = new DateTime( $event->start_datetime, new DateTimeZone( 'UTC' ) );
	$start_dt->setTimezone( $timezone );
	$start_date_short = date_i18n( 'M d Y', $start_dt->getTimestamp() );
	$start_date_long = date_i18n( get_option( 'date_format' ), $start_dt->getTimestamp() );
	$start_time = date_i18n( get_option( 'time_format' ), $start_dt->getTimestamp() );
}

if ( ! empty( $event->end_datetime ) ) {
	$end_dt = new DateTime( $event->end_datetime, new DateTimeZone( 'UTC' ) );
	$end_dt->setTimezone( $timezone );
	$end_time = date_i18n( get_option( 'time_format' ), $end_dt->getTimestamp() );
}

// Parse tags
$tags = [];
if ( ! empty( $event->tags ) ) {
	$tags_data = json_decode( $event->tags, true );
	if ( is_array( $tags_data ) ) $tags = $tags_data;
}

// Get image
require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-image-helper.php';
$image_url = ChurchTools_Suite_Image_Helper::get_image_url( $event );

// Location
$location_parts = [];
if ( ! empty( $event->address_name ?? $event->location_name ) ) $location_parts[] = $event->address_name ?? $event->location_name;
if ( ! empty( $event->address_street ) ) $location_parts[] = $event->address_street;
if ( ! empty( $event->address_zip ) && ! empty( $event->address_city ) ) {
	$location_parts[] = $event->address_zip . ', ' . $event->address_city;
} elseif ( ! empty( $event->address_city ) ) {
	$location_parts[] = $event->address_city;
}

// Description
$full_description = '';
if ( ! empty( $event->event_description ) ) $full_description = $event->event_description;
if ( ! empty( $event->appointment_description ) ) {
	if ( ! empty( $full_description ) ) $full_description .= "\n\n";
	$full_description .= $event->appointment_description;
}
$full_description = wpautop( wp_kses_post( $full_description ) );
?>

<div class="cts-single-pro">
	<div class="cts-single-pro-wrap">
		
		<!-- Main (Left) -->
		<div class="cts-pro-main">
			<?php if ( ! empty( $image_url ) ) : ?>
				<div class="cts-pro-image">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $event->title ?? '' ); ?>" />
				</div>
			<?php endif; ?>
			
			<h1 class="cts-pro-h1"><?php echo esc_html( $event->title ?? __( 'Event', 'churchtools-suite' ) ); ?></h1>
			
			<?php if ( ! empty( $full_description ) ) : ?>
				<div class="cts-pro-desc"><?php echo wp_kses_post( $full_description ); ?></div>
			<?php endif; ?>
		</div>
		
		<!-- Sidebar (Right) -->
		<aside class="cts-pro-side">
			
			<!-- Date -->
			<div class="cts-pro-box">
				<div class="cts-pro-ico" style="background:#e0f2fe">
					<svg width="20" height="20" fill="none" stroke="#0284c7" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
				</div>
				<div class="cts-pro-txt">
					<div class="cts-pro-lbl">DATE</div>
					<div class="cts-pro-val"><?php echo esc_html( $start_date_short ); ?></div>
				</div>
			</div>
			
			<!-- Time -->
			<?php if ( ! empty( $start_time ) ) : ?>
			<div class="cts-pro-box">
				<div class="cts-pro-ico" style="background:#e0f2fe">
					<svg width="20" height="20" fill="none" stroke="#0284c7" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
				</div>
				<div class="cts-pro-txt">
					<div class="cts-pro-lbl">TIME</div>
					<div class="cts-pro-val"><?php echo esc_html( $start_time ); if ( ! empty( $end_time ) ) echo ' - ' . esc_html( $end_time ); ?></div>
				</div>
			</div>
			<?php endif; ?>
			
			<!-- Local Time -->
			<?php if ( ! empty( get_option( 'timezone_string' ) ) ) : ?>
			<div class="cts-pro-tz">
				<div class="cts-pro-tz-title">LOCAL TIME</div>
				<div class="cts-pro-tz-line"><span>Timezone:</span><strong><?php echo esc_html( str_replace( '_', '/', get_option( 'timezone_string' ) ) ); ?></strong></div>
				<?php if ( ! empty( $start_date_long ) ) : ?>
				<div class="cts-pro-tz-line"><span>Date:</span><strong><?php echo esc_html( $start_date_long ); ?></strong></div>
				<?php endif; ?>
				<?php if ( ! empty( $start_time ) ) : ?>
				<div class="cts-pro-tz-line"><span>Time:</span><strong><?php echo esc_html( $start_time ); if ( ! empty( $end_time ) ) echo ' - ' . esc_html( $end_time ); ?></strong></div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			
			<!-- Tags -->
			<?php if ( ! empty( $tags ) ) : ?>
			<div class="cts-pro-box">
				<div class="cts-pro-ico" style="background:#fef3c7">
					<svg width="20" height="20" fill="none" stroke="#d97706" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
				</div>
				<div class="cts-pro-txt">
					<div class="cts-pro-lbl">LABELS</div>
					<div class="cts-pro-tags">
						<?php foreach ( $tags as $tag ) : ?>
							<span class="cts-pro-tag" style="background:<?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>20;color:<?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>">
								<?php echo esc_html( $tag['name'] ?? '' ); ?>
							</span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
			
			<!-- Location -->
			<?php if ( ! empty( $location_parts ) ) : ?>
			<div class="cts-pro-box">
				<div class="cts-pro-ico" style="background:#dbeafe">
					<svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				</div>
				<div class="cts-pro-txt">
					<div class="cts-pro-lbl">LOCATION</div>
					<div class="cts-pro-val" style="white-space:pre-line"><?php echo esc_html( implode( "\n", $location_parts ) ); ?></div>
				</div>
			</div>
			<?php endif; ?>
			
			<!-- Services -->
			<?php if ( ! empty( $services ) && is_array( $services ) ) : ?>
			<div class="cts-pro-box">
				<div class="cts-pro-ico" style="background:#f3e8ff">
					<svg width="20" height="20" fill="none" stroke="#9333ea" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
				</div>
				<div class="cts-pro-txt">
					<div class="cts-pro-lbl">TEAM</div>
					<div class="cts-pro-servs">
						<?php foreach ( $services as $service ) : ?>
							<div class="cts-pro-serv">
								<span style="color:#6b7280;font-weight:500"><?php echo esc_html( $service->service_name ?? '' ); ?>:</span>
								<span style="color:#111827;font-weight:600"><?php echo esc_html( $service->person_name ?: __( 'TBD', 'churchtools-suite' ) ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
			
		</aside>
	</div>
</div>

<style>
.cts-single-pro{max-width:1400px;margin:0 auto!important;padding:20px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif!important}
.cts-single-pro *{box-sizing:border-box}
.cts-single-pro-wrap{display:grid!important;grid-template-columns:2fr 1fr;gap:40px;align-items:start}
.cts-pro-main{background:#fff}
.cts-pro-image{width:100%;border-radius:8px;overflow:hidden;margin-bottom:30px}
.cts-pro-image img{width:100%!important;height:auto!important;display:block!important}
.cts-pro-h1{font-size:2rem!important;font-weight:700!important;color:#111827!important;margin:0 0 24px 0!important;line-height:1.3!important}
.cts-pro-desc{font-size:1rem!important;line-height:1.7!important;color:#4b5563!important}
.cts-pro-desc p{margin-bottom:16px!important}
.cts-pro-side{position:sticky;top:20px}
.cts-pro-box{display:flex!important;gap:16px;padding:20px 0;border-bottom:1px solid #e5e7eb}
.cts-pro-box:first-child{padding-top:0}
.cts-pro-box:last-child{border-bottom:none}
.cts-pro-ico{width:44px!important;height:44px!important;min-width:44px;border-radius:6px;display:flex!important;align-items:center;justify-content:center;flex-shrink:0}
.cts-pro-txt{flex:1}
.cts-pro-lbl{font-size:.75rem!important;font-weight:600!important;color:#6b7280!important;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px!important}
.cts-pro-val{font-size:1rem!important;font-weight:500!important;color:#111827!important;line-height:1.5!important}
.cts-pro-tz{background:#f9fafb!important;padding:16px!important;border-radius:6px;margin:12px 0!important;border:1px solid #e5e7eb}
.cts-pro-tz-title{font-size:.75rem!important;font-weight:600!important;color:#6b7280!important;text-transform:uppercase;margin-bottom:12px!important}
.cts-pro-tz-line{display:flex!important;justify-content:space-between;font-size:.875rem!important;color:#4b5563!important;margin-bottom:6px}
.cts-pro-tz-line strong{color:#111827!important;font-weight:600!important}
.cts-pro-tags{display:flex!important;flex-wrap:wrap;gap:6px;margin-top:4px!important}
.cts-pro-tag{padding:4px 10px!important;border-radius:4px;font-size:.75rem!important;font-weight:500!important;display:inline-block!important}
.cts-pro-servs{display:flex;flex-direction:column;gap:8px;margin-top:4px!important}
.cts-pro-serv{font-size:.875rem!important;line-height:1.5!important}
@media (max-width:1024px){.cts-single-pro-wrap{grid-template-columns:1fr!important;gap:30px}.cts-pro-side{position:static!important}}
@media (max-width:768px){.cts-single-pro{padding:16px!important}.cts-pro-h1{font-size:1.5rem!important}.cts-single-pro-wrap{gap:20px!important}}
</style>
