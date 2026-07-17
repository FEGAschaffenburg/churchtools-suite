<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTS_Presentations_Renderer {
	public static function render_slider( array $config ): string {
		$event_id = isset( $config['event_id'] ) ? (int) $config['event_id'] : 0;
		$special_tags = isset( $config['special_tags'] ) ? (string) $config['special_tags'] : '';
		$slide_seconds = isset( $config['slide_seconds'] ) ? max( 3, (int) $config['slide_seconds'] ) : 10;
		$slide_1_view = isset( $config['slide_1_view'] ) ? (string) $config['slide_1_view'] : 'list-classic';
		$slide_2_view = isset( $config['slide_2_view'] ) ? (string) $config['slide_2_view'] : 'grid-modern';

		$slides = [];
		$slides[] = self::render_shortcode_slide(
			__( 'Nächste Termine', 'churchtools-suite-presentations' ),
			sprintf(
				'[churchtools_events view="%s" limit="5" show_services="true" show_location="true" show_time="true"]',
				esc_attr( $slide_1_view )
			)
		);

		$special_shortcode = sprintf(
			'[churchtools_events view="%s" limit="6" show_services="true" show_location="true" show_time="true" tags="%s"]',
			esc_attr( $slide_2_view ),
			esc_attr( $special_tags )
		);
		$slides[] = self::render_shortcode_slide( __( 'Besondere Termine', 'churchtools-suite-presentations' ), $special_shortcode );

		$slides[] = self::render_today_services_slide( $event_id );

		$slides = array_filter( $slides );
		if ( empty( $slides ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="cts-presentation" data-slide-seconds="<?php echo esc_attr( $slide_seconds ); ?>">
			<div class="cts-presentation-stage">
				<?php foreach ( $slides as $index => $slide_html ) : ?>
					<section class="cts-presentation-slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-slide-index="<?php echo esc_attr( (string) $index ); ?>">
						<?php echo $slide_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</section>
				<?php endforeach; ?>
			</div>
			<div class="cts-presentation-controls">
				<button type="button" class="cts-presentation-btn" data-cts-prev="1"><?php esc_html_e( 'Zurück', 'churchtools-suite-presentations' ); ?></button>
				<div class="cts-presentation-dots" aria-hidden="true"></div>
				<button type="button" class="cts-presentation-btn" data-cts-next="1"><?php esc_html_e( 'Weiter', 'churchtools-suite-presentations' ); ?></button>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	private static function render_shortcode_slide( string $title, string $shortcode ): string {
		$content = do_shortcode( $shortcode );
		if ( trim( $content ) === '' ) {
			return '';
		}

		ob_start();
		?>
		<header class="cts-presentation-header">
			<h2><?php echo esc_html( $title ); ?></h2>
		</header>
		<div class="cts-presentation-content">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	private static function render_today_services_slide( int $event_id ): string {
		if ( $event_id <= 0 ) {
			return '';
		}

		if ( ! class_exists( 'ChurchTools_Suite_Template_Data' ) ) {
			require_once CHURCHTOOLS_SUITE_PATH . 'includes/services/class-churchtools-suite-template-data.php';
		}

		$data = new ChurchTools_Suite_Template_Data();
		$event = $data->get_event_by_id( $event_id );
		if ( empty( $event ) || ! is_array( $event ) ) {
			return '';
		}

		$services = isset( $event['services'] ) && is_array( $event['services'] ) ? $event['services'] : [];
		ob_start();
		?>
		<header class="cts-presentation-header">
			<h2><?php esc_html_e( 'Heute Dienste', 'churchtools-suite-presentations' ); ?></h2>
		</header>
		<div class="cts-presentation-content">
			<div class="cts-presentation-event-card">
				<h3><?php echo esc_html( (string) ( $event['title'] ?? '' ) ); ?></h3>
				<p>
					<?php echo esc_html( (string) ( $event['date_formatted'] ?? '' ) ); ?>
					<?php if ( ! empty( $event['time_formatted'] ) ) : ?>
						- <?php echo esc_html( (string) $event['time_formatted'] ); ?>
					<?php endif; ?>
				</p>
				<?php if ( ! empty( $event['location_name'] ) ) : ?>
					<p><?php echo esc_html( (string) $event['location_name'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="cts-presentation-services">
				<?php if ( empty( $services ) ) : ?>
					<p><?php esc_html_e( 'Keine Dienste hinterlegt.', 'churchtools-suite-presentations' ); ?></p>
				<?php else : ?>
					<ul>
						<?php foreach ( $services as $service ) : ?>
							<li>
								<strong><?php echo esc_html( (string) ( $service['service_name'] ?? '' ) ); ?></strong>
								<?php if ( ! empty( $service['person_name'] ) ) : ?>
									<span> - <?php echo esc_html( (string) $service['person_name'] ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
