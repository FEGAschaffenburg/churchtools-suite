<?php
/**
 * Calendar View - Monthly Modern
 *
 * Moderne Monatsansicht mit Grid-Layout
 *
 * @package ChurchTools_Suite
 * @since   0.5.12.0
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Gruppiere Events nach Datum
$events_by_date = [];
foreach ( $events as $event ) {
	$date = date( 'Y-m-d', strtotime( $event['start_datetime'] ) );
	if ( ! isset( $events_by_date[ $date ] ) ) {
		$events_by_date[ $date ] = [];
	}
	$events_by_date[ $date ][] = $event;
}
?>

<div class="churchtools-suite-wrapper">
<div class="cts-calendar cts-calendar-monthly" data-view="calendar-monthly">
	
	<div class="cts-calendar-header">
		<button class="cts-nav-btn cts-prev-month">‹</button>
		<h2 class="cts-calendar-title"><?php echo esc_html( date_i18n( 'F Y' ) ); ?></h2>
		<button class="cts-nav-btn cts-next-month">›</button>
	</div>
	
	<?php if ( empty( $events ) ) : ?>
		
		<div class="cts-calendar-empty">
			<span class="cts-empty-icon">📅</span>
			<p><?php esc_html_e( 'Keine Termine in diesem Monat gefunden.', 'churchtools-suite' ); ?></p>
		</div>
		
	<?php else : ?>
		
		<div class="cts-calendar-grid">
			
			<!-- Wochentage -->
			<div class="cts-weekday"><?php esc_html_e( 'Mo', 'churchtools-suite' ); ?></div>
			<div class="cts-weekday"><?php esc_html_e( 'Di', 'churchtools-suite' ); ?></div>
			<div class="cts-weekday"><?php esc_html_e( 'Mi', 'churchtools-suite' ); ?></div>
			<div class="cts-weekday"><?php esc_html_e( 'Do', 'churchtools-suite' ); ?></div>
			<div class="cts-weekday"><?php esc_html_e( 'Fr', 'churchtools-suite' ); ?></div>
			<div class="cts-weekday"><?php esc_html_e( 'Sa', 'churchtools-suite' ); ?></div>
			<div class="cts-weekday"><?php esc_html_e( 'So', 'churchtools-suite' ); ?></div>
			
			<!-- Tage -->
			<?php
			$first_day = date( 'Y-m-01' );
			$last_day = date( 'Y-m-t' );
			$start_weekday = date( 'N', strtotime( $first_day ) );
			$days_in_month = date( 't' );
			
			// Leere Zellen vor dem 1. Tag
			for ( $i = 1; $i < $start_weekday; $i++ ) {
				echo '<div class="cts-day cts-day-empty"></div>';
			}
			
			// Tage des Monats
			for ( $day = 1; $day <= $days_in_month; $day++ ) {
				$date = date( 'Y-m-' . sprintf( '%02d', $day ) );
				$has_events = isset( $events_by_date[ $date ] );
				$is_today = $date === date( 'Y-m-d' );
				?>
				<div class="cts-day <?php echo $is_today ? 'cts-day-today' : ''; ?> <?php echo $has_events ? 'cts-day-has-events' : ''; ?>" data-date="<?php echo esc_attr( $date ); ?>">
					<div class="cts-day-number"><?php echo $day; ?></div>
					<?php if ( $has_events ) : ?>
						<div class="cts-day-events">
							<?php foreach ( array_slice( $events_by_date[ $date ], 0, 3 ) as $event ) : ?>
								<div class="cts-event-dot" style="background-color: <?php echo esc_attr( $event['calendar_color'] ?? '#667eea' ); ?>" title="<?php echo esc_attr( $event['start_day'] . '. ' . $event['start_month'] . ' ' . $event['start_year'] . ' - ' . $event['title'] ); ?>">
									<span class="cts-event-time"><?php echo esc_html( $event['start_time'] ); ?></span>
									<span class="cts-event-title-small"><?php echo esc_html( wp_trim_words( $event['title'], 3 ) ); ?></span>
								</div>
							<?php endforeach; ?>
							<?php if ( count( $events_by_date[ $date ] ) > 3 ) : ?>
								<div class="cts-more-events">+<?php echo count( $events_by_date[ $date ] ) - 3; ?></div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php
			}
			?>
			
		</div>
		
	<?php endif; ?>
	
</div>
</div>

<style>
.cts-calendar-monthly {
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	padding: 24px;
}

/* Header */
.cts-calendar-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 24px;
}

.cts-calendar-title {
	margin: 0;
	font-size: 24px;
	font-weight: 700;
	color: #1f2937;
}

.cts-nav-btn {
	width: 40px;
	height: 40px;
	border: none;
	background: #f3f4f6;
	border-radius: 8px;
	font-size: 24px;
	font-weight: 700;
	color: #374151;
	cursor: pointer;
	transition: background 0.2s;
}

.cts-nav-btn:hover {
	background: #e5e7eb;
}

/* Grid */
.cts-calendar-grid {
	display: grid;
	grid-template-columns: repeat(7, 1fr);
	gap: 8px;
}

/* Weekdays */
.cts-weekday {
	padding: 12px;
	text-align: center;
	font-size: 12px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: #6b7280;
}

/* Days */
.cts-day {
	min-height: 100px;
	padding: 8px;
	background: #fff;
	border: 1px solid #e5e7eb;
	border-radius: 8px;
	transition: background 0.2s;
	cursor: pointer;
}

.cts-day:hover {
	background: #f9fafb;
}

.cts-day-empty {
	background: transparent;
	border-color: transparent;
	cursor: default;
}

.cts-day-today {
	border-color: #667eea;
	border-width: 2px;
	background: #eff6ff;
}

.cts-day-number {
	font-size: 14px;
	font-weight: 600;
	color: #1f2937;
	margin-bottom: 8px;
}

/* Events */
.cts-day-events {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.cts-event-dot {
	padding: 4px 8px;
	border-radius: 4px;
	font-size: 11px;
	color: #fff;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	display: block !important;
}

.cts-event-time {
	font-weight: 700;
	margin-right: 4px;
	display: inline !important;
}

.cts-event-title-small {
	font-weight: 500;
	display: inline !important;
	white-space: nowrap !important;
}

.cts-more-events {
	padding: 4px 8px;
	text-align: center;
	font-size: 11px;
	font-weight: 600;
	color: #6b7280;
	background: #f3f4f6;
	border-radius: 4px;
}

/* Empty State */
.cts-calendar-empty {
	padding: 60px 20px;
	text-align: center;
	color: #6b7280;
}

.cts-empty-icon {
	font-size: 64px;
	display: block;
	margin-bottom: 16px;
}

/* Responsive */
@media (max-width: 768px) {
	.cts-calendar-grid {
		gap: 4px;
	}
	
	.cts-day {
		min-height: 80px;
		padding: 4px;
	}
	
	.cts-day-number {
		font-size: 12px;
	}
	
	.cts-event-dot {
		font-size: 10px;
		padding: 2px 4px;
	}
	
	.cts-event-title-small {
		display: none;
	}
}
</style>
