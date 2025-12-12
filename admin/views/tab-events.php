<?php
/**
 * Events Tab - Termine-Übersicht
 *
 * @package ChurchTools_Suite
 * @since   0.3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load repositories
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-repository-base.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';

$events_repo = new ChurchTools_Suite_Events_Repository();
$calendars_repo = new ChurchTools_Suite_Calendars_Repository();

// Filter-Parameter
$from = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
$to = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
$calendar_filter = isset( $_GET['calendar_id'] ) ? sanitize_text_field( wp_unslash( $_GET['calendar_id'] ) ) : '';
$page = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$limit = 50;
$offset = ( $page - 1 ) * $limit;

// Kombinierte Abfrage
global $wpdb;
$prefix = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX;
$events_table = $prefix . 'events';

$sql = "SELECT id, event_id, appointment_id, calendar_id, title, description, start_datetime, end_datetime, is_all_day, location_name FROM {$events_table} WHERE 1=1";
$count_sql = "SELECT COUNT(*) FROM {$events_table} WHERE 1=1";
$where_params = [];

if ( ! empty( $from ) ) {
	$sql .= " AND start_datetime >= %s";
	$count_sql .= " AND start_datetime >= %s";
	$where_params[] = $from . ' 00:00:00';
}

if ( ! empty( $to ) ) {
	$sql .= " AND start_datetime <= %s";
	$count_sql .= " AND start_datetime <= %s";
	$where_params[] = $to . ' 23:59:59';
}

if ( ! empty( $calendar_filter ) ) {
	$sql .= " AND calendar_id = %s";
	$count_sql .= " AND calendar_id = %s";
	$where_params[] = $calendar_filter;
}

$sql .= " ORDER BY start_datetime ASC";
$sql .= " LIMIT %d OFFSET %d";

// Prepare queries
$prepared_count = $count_sql;
if ( ! empty( $where_params ) ) {
	$prepared_count = $wpdb->prepare( $count_sql, ...$where_params );
}

$prepared_sql = $sql;
$query_params = array_merge( $where_params, [ $limit, $offset ] );
if ( ! empty( $query_params ) ) {
	$prepared_sql = $wpdb->prepare( $sql, ...$query_params );
}

$events = $wpdb->get_results( $prepared_sql );
$total = (int) $wpdb->get_var( $prepared_count );

$calendars = $calendars_repo->get_all();
$total_pages = ceil( $total / $limit );
?>

<div class="cts-events">
	
	<div class="cts-card">
		<div class="cts-card-header">
			<h3>📋 <?php esc_html_e( 'Termine-Übersicht', 'churchtools-suite' ); ?></h3>
			<p><?php esc_html_e( 'Alle synchronisierten Termine aus ChurchTools', 'churchtools-suite' ); ?></p>
		</div>
		
		<!-- Filter -->
		<form method="get" class="cts-filter-section">
			<input type="hidden" name="page" value="churchtools-suite" />
			<input type="hidden" name="tab" value="events" />
			
			<div class="cts-filter-grid">
				<div class="cts-form-group">
					<label>📅 <?php esc_html_e( 'Von', 'churchtools-suite' ); ?></label>
					<input type="date" name="from" value="<?php echo esc_attr( $from ); ?>" class="cts-form-input" />
				</div>
				
				<div class="cts-form-group">
					<label>📅 <?php esc_html_e( 'Bis', 'churchtools-suite' ); ?></label>
					<input type="date" name="to" value="<?php echo esc_attr( $to ); ?>" class="cts-form-input" />
				</div>
				
				<div class="cts-form-group" style="grid-column: span 2;">
					<label>🗂️ <?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></label>
					<select name="calendar_id" class="cts-form-input">
						<option value=""><?php esc_html_e( 'Alle Kalender', 'churchtools-suite' ); ?></option>
						<?php foreach ( $calendars as $cal ) : ?>
							<option value="<?php echo esc_attr( $cal->calendar_id ); ?>" <?php selected( $calendar_filter, $cal->calendar_id ); ?>>
								<?php echo esc_html( $cal->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			
			<div class="cts-filter-actions">
				<button type="submit" class="cts-btn cts-btn-primary">
					<span>🔍</span>
					<?php esc_html_e( 'Filtern', 'churchtools-suite' ); ?>
				</button>
				
				<?php if ( ! empty( $from ) || ! empty( $to ) || ! empty( $calendar_filter ) ) : ?>
					<a href="?page=churchtools-suite&tab=events" class="cts-btn cts-btn-secondary">
						<span>✖</span>
						<?php esc_html_e( 'Filter zurücksetzen', 'churchtools-suite' ); ?>
					</a>
				<?php endif; ?>
				
				<div class="cts-filter-count">
					📊 <?php printf( esc_html__( '%d Termine', 'churchtools-suite' ), $total ); ?>
				</div>
			</div>
			
			<?php if ( ! empty( $from ) || ! empty( $to ) || ! empty( $calendar_filter ) ) : ?>
				<div class="cts-active-filters">
					<strong>✓ <?php esc_html_e( 'Aktive Filter:', 'churchtools-suite' ); ?></strong>
					<?php if ( ! empty( $from ) ) : ?>
						<span><?php esc_html_e( 'Von:', 'churchtools-suite' ); ?> <strong><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $from ) ) ); ?></strong></span>
					<?php endif; ?>
					<?php if ( ! empty( $to ) ) : ?>
						<span><?php esc_html_e( 'Bis:', 'churchtools-suite' ); ?> <strong><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $to ) ) ); ?></strong></span>
					<?php endif; ?>
					<?php if ( ! empty( $calendar_filter ) ) : 
						foreach ( $calendars as $cal ) {
							if ( $cal->calendar_id === $calendar_filter ) : ?>
								<span><?php esc_html_e( 'Kalender:', 'churchtools-suite' ); ?> <strong><?php echo esc_html( $cal->name ); ?></strong></span>
							<?php endif;
						}
					endif; ?>
				</div>
			<?php endif; ?>
		</form>
		
		<!-- Tabelle -->
		<?php if ( empty( $events ) ) : ?>
			<div class="cts-empty-state">
				<span class="cts-empty-icon">📅</span>
				<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
				<p><?php esc_html_e( 'Versuchen Sie andere Filterwerte oder synchronisieren Sie die Termine im Tab "Sync".', 'churchtools-suite' ); ?></p>
			</div>
		<?php else : ?>
			<div class="cts-table-wrapper">
				<table class="cts-events-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Datum & Zeit', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Titel', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Kalender', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Ort', 'churchtools-suite' ); ?></th>
							<th><?php esc_html_e( 'Typ', 'churchtools-suite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $events as $event ) : 
							$calendar = null;
							foreach ( $calendars as $cal ) {
								if ( $cal->calendar_id === $event->calendar_id ) {
									$calendar = $cal;
									break;
								}
							}
							
							$start = strtotime( $event->start_datetime );
							$end = $event->end_datetime ? strtotime( $event->end_datetime ) : null;
							$is_all_day = (bool) $event->is_all_day;
							
							// Typ bestimmen (Event oder Appointment)
							$type_label = ! empty( $event->appointment_id ) 
								? __( 'Termin', 'churchtools-suite' ) 
								: __( 'Event', 'churchtools-suite' );
							$type_icon = ! empty( $event->appointment_id ) ? '📅' : '🎯';
						?>
							<tr>
								<td class="cts-event-date">
									<div class="cts-event-date-primary">
										<?php echo esc_html( date_i18n( 'D, d.m.Y', $start ) ); ?>
									</div>
									<?php if ( ! $is_all_day ) : ?>
										<div class="cts-event-date-time">
											<?php 
											echo esc_html( date_i18n( 'H:i', $start ) ); 
											if ( $end ) {
												echo ' - ' . esc_html( date_i18n( 'H:i', $end ) );
											}
											?>
										</div>
									<?php else : ?>
										<div class="cts-event-date-time">
											<?php esc_html_e( 'Ganztägig', 'churchtools-suite' ); ?>
										</div>
									<?php endif; ?>
								</td>
								
								<td class="cts-event-title">
									<div class="cts-event-title-main">
										<?php echo esc_html( $event->title ); ?>
									</div>
									<?php if ( ! empty( $event->description ) ) : ?>
										<div class="cts-event-description">
											<?php echo esc_html( wp_trim_words( $event->description, 15 ) ); ?>
										</div>
									<?php endif; ?>
								</td>
								
								<td class="cts-event-calendar">
									<?php if ( $calendar ) : ?>
										<span class="cts-calendar-badge" style="background-color: <?php echo esc_attr( $calendar->color ?? '#3498db' ); ?>;">
											<?php echo esc_html( $calendar->name ); ?>
										</span>
									<?php else : ?>
										<span class="cts-calendar-badge">
											<?php echo esc_html( $event->calendar_id ); ?>
										</span>
									<?php endif; ?>
								</td>
								
								<td class="cts-event-location">
									<?php if ( ! empty( $event->location_name ) ) : ?>
										<span>📍 <?php echo esc_html( $event->location_name ); ?></span>
									<?php else : ?>
										<span class="cts-muted">—</span>
									<?php endif; ?>
								</td>
								
								<td class="cts-event-type">
									<span class="cts-type-badge">
										<?php echo esc_html( $type_icon . ' ' . $type_label ); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			
			<!-- Pagination -->
			<?php if ( $total_pages > 1 ) : ?>
				<div class="cts-pagination">
					<?php
					$base_url = add_query_arg(
						[
							'page' => 'churchtools-suite',
							'tab' => 'events',
							'from' => $from,
							'to' => $to,
							'calendar_id' => $calendar_filter,
						],
						admin_url( 'admin.php' )
					);
					
					if ( $page > 1 ) :
						$prev_url = add_query_arg( 'paged', $page - 1, $base_url );
						?>
						<a href="<?php echo esc_url( $prev_url ); ?>" class="cts-btn cts-btn-secondary">
							← <?php esc_html_e( 'Zurück', 'churchtools-suite' ); ?>
						</a>
					<?php endif; ?>
					
					<span class="cts-pagination-info">
						<?php printf( esc_html__( 'Seite %d von %d', 'churchtools-suite' ), $page, $total_pages ); ?>
					</span>
					
					<?php if ( $page < $total_pages ) :
						$next_url = add_query_arg( 'paged', $page + 1, $base_url );
						?>
						<a href="<?php echo esc_url( $next_url ); ?>" class="cts-btn cts-btn-secondary">
							<?php esc_html_e( 'Weiter', 'churchtools-suite' ); ?> →
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	
</div>
