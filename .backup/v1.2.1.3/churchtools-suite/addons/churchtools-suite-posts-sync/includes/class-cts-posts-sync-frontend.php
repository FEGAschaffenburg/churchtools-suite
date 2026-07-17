<?php
/**
 * Frontend integration for Posts Sync addon.
 *
 * Provides shortcode and Gutenberg block rendering for synced ChurchTools posts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Posts_Sync_Frontend {

	private const SHORTCODE_TAG = 'cts_posts';
	private const BLOCK_NAME = 'churchtools-suite-posts-sync/posts-list';
	private const FRONTEND_STYLE_HANDLE = 'cts-posts-sync-frontend';
	private const BLOCK_EDITOR_SCRIPT_HANDLE = 'cts-posts-sync-block-editor';
	private const SYNCED_POST_META_KEY = '_cts_ct_post_id';
	private const PUBLICATION_DATE_META_KEY = '_cts_ct_publication_date';
	private const PUBLISHED_DATE_META_KEY = '_cts_ct_published_date';
	private const EXPIRATION_DATE_META_KEY = '_cts_ct_expiration_date';

	/**
	 * Initialize frontend hooks.
	 */
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_shortcodes_and_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_block_editor_assets_fallback' ] );
	}

	/**
	 * Register shortcode and dynamic block.
	 */
	public static function register_shortcodes_and_blocks(): void {
		add_shortcode( self::SHORTCODE_TAG, [ __CLASS__, 'render_shortcode' ] );
		self::register_block_assets();

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			self::BLOCK_NAME,
			[
				'api_version' => 2,
				'title' => __( 'ChurchTools Berichte', 'churchtools-suite-posts-sync' ),
				'description' => __( 'Zeigt synchronisierte ChurchTools-Berichte als Liste an.', 'churchtools-suite-posts-sync' ),
				'category' => 'churchtools-suite',
				'icon' => 'media-document',
				'keywords' => [ 'churchtools', 'berichte', 'posts' ],
				'editor_script' => self::BLOCK_EDITOR_SCRIPT_HANDLE,
				'style' => self::FRONTEND_STYLE_HANDLE,
				'render_callback' => [ __CLASS__, 'render_block' ],
				'attributes' => [
					'limit' => [
						'type' => 'number',
						'default' => 10,
					],
					'postType' => [
						'type' => 'string',
						'default' => '',
					],
					'showDate' => [
						'type' => 'boolean',
						'default' => true,
					],
					'showExcerpt' => [
						'type' => 'boolean',
						'default' => true,
					],
					'excerptWords' => [
						'type' => 'number',
						'default' => 28,
					],
					'onlyNew' => [
						'type' => 'boolean',
						'default' => false,
					],
					'onlySynced' => [
						'type' => 'boolean',
						'default' => true,
					],
				],
			]
		);
	}

	/**
	 * Fallback enqueue for editor assets in case third-party setups skip auto-enqueue.
	 */
	public static function enqueue_block_editor_assets_fallback(): void {
		if ( function_exists( 'wp_enqueue_script' ) ) {
			wp_enqueue_script( self::BLOCK_EDITOR_SCRIPT_HANDLE );
		}

		if ( function_exists( 'wp_enqueue_style' ) ) {
			wp_enqueue_style( self::FRONTEND_STYLE_HANDLE );
		}

		if ( function_exists( 'wp_add_inline_script' ) ) {
			$inline_script = self::get_block_editor_inline_script();
			if ( $inline_script !== '' ) {
				wp_add_inline_script( 'wp-blocks', $inline_script, 'after' );
			}
		}
	}

	/**
	 * Build a minimal inline fallback block registration for editor reliability.
	 */
	private static function get_block_editor_inline_script(): string {
		$post_type_options = [];
		foreach ( self::get_supported_target_types() as $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );
			$post_type_options[] = [
				'value' => (string) $post_type,
				'label' => $post_type_obj && ! empty( $post_type_obj->labels->singular_name )
					? (string) $post_type_obj->labels->singular_name
					: (string) $post_type,
			];
		}

		$payload = wp_json_encode(
			[
				'blockName' => self::BLOCK_NAME,
				'defaultPostType' => (string) get_option( 'churchtools_suite_ct_posts_target_type', 'post' ),
				'postTypeOptions' => $post_type_options,
			],
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);

		if ( ! is_string( $payload ) || $payload === '' ) {
			return '';
		}

		return "(function(){\n"
			. "  var data = " . $payload . ";\n"
			. "  window.ctsPostsSyncBlockData = data;\n"
			. "  if (typeof console !== 'undefined' && console.info) { console.info('[CTS Posts Sync] Inline fallback active'); }\n"
			. "  if (!window.wp || !wp.blocks || !wp.element || !wp.components || !wp.blockEditor) { return; }\n"
			. "  if (wp.blocks.getBlockType && wp.blocks.getBlockType(data.blockName) && wp.blocks.unregisterBlockType) { wp.blocks.unregisterBlockType(data.blockName); }\n"
			. "  var el = wp.element.createElement;\n"
			. "  var InspectorControls = wp.blockEditor.InspectorControls;\n"
			. "  var PanelBody = wp.components.PanelBody;\n"
			. "  var RangeControl = wp.components.RangeControl;\n"
			. "  var SelectControl = wp.components.SelectControl;\n"
			. "  var ToggleControl = wp.components.ToggleControl;\n"
			. "  var Placeholder = wp.components.Placeholder;\n"
			. "  var options = Array.isArray(data.postTypeOptions) ? data.postTypeOptions.slice() : [];\n"
			. "  options.unshift({ value: '', label: 'Standard aus Sync-Einstellungen' });\n"
			. "  wp.blocks.registerBlockType(data.blockName, {\n"
			. "    title: 'ChurchTools Berichte',\n"
			. "    description: 'Zeigt synchronisierte ChurchTools-Berichte als Liste an.',\n"
			. "    icon: 'media-document',\n"
			. "    category: 'churchtools-suite',\n"
			. "    keywords: ['churchtools', 'berichte', 'posts'],\n"
			. "    attributes: {\n"
			. "      limit: { type: 'number', default: 10 },\n"
			. "      postType: { type: 'string', default: '' },\n"
			. "      showDate: { type: 'boolean', default: true },\n"
			. "      showExcerpt: { type: 'boolean', default: true },\n"
			. "      excerptWords: { type: 'number', default: 28 },\n"
			. "      onlyNew: { type: 'boolean', default: false },\n"
			. "      onlySynced: { type: 'boolean', default: true }\n"
			. "    },\n"
			. "    edit: function(props) {\n"
			. "      var a = props.attributes;\n"
			. "      return el(wp.element.Fragment, null,\n"
			. "        el(InspectorControls, null,\n"
			. "          el(PanelBody, { title: 'Berichte-Liste', initialOpen: true },\n"
			. "            el(RangeControl, { label: 'Anzahl', value: a.limit, onChange: function(v){ props.setAttributes({ limit: v || 10 }); }, min: 1, max: 100 }),\n"
			. "            el(SelectControl, { label: 'Post-Typ', value: a.postType, options: options, onChange: function(v){ props.setAttributes({ postType: v || '' }); } }),\n"
			. "            el(ToggleControl, { label: 'Nur synchronisierte Inhalte', checked: !!a.onlySynced, onChange: function(v){ props.setAttributes({ onlySynced: !!v }); } }),\n"
			. "            el(ToggleControl, { label: 'Nur neue', checked: !!a.onlyNew, onChange: function(v){ props.setAttributes({ onlyNew: !!v }); } }),\n"
			. "            el(ToggleControl, { label: 'Datum anzeigen', checked: !!a.showDate, onChange: function(v){ props.setAttributes({ showDate: !!v }); } }),\n"
			. "            el(ToggleControl, { label: 'Auszug anzeigen', checked: !!a.showExcerpt, onChange: function(v){ props.setAttributes({ showExcerpt: !!v }); } }),\n"
			. "            el(RangeControl, { label: 'Auszug-Wörter', value: a.excerptWords, onChange: function(v){ props.setAttributes({ excerptWords: v || 28 }); }, min: 8, max: 80, disabled: !a.showExcerpt })\n"
			. "          )\n"
			. "        ),\n"
			. "        el(Placeholder, { icon: 'media-document', label: 'ChurchTools Berichte', instructions: 'Die Ausgabe wird im Frontend dynamisch gerendert.' },\n"
			. "          el('p', null, 'Anzahl: ' + String(a.limit || 10)),\n"
			. "          el('p', null, 'Post-Typ: ' + (a.postType || 'Standard')),\n"
			. "          el('p', null, 'Nur synchronisiert: ' + (a.onlySynced ? 'Ja' : 'Nein')),\n"
			. "          el('p', null, 'Nur neue: ' + (a.onlyNew ? 'Ja' : 'Nein'))\n"
			. "        )\n"
			. "      );\n"
			. "    },\n"
			. "    save: function() { return null; }\n"
			. "  });\n"
			. "})();";
	}

	/**
	 * Register block assets for editor and frontend rendering.
	 */
	private static function register_block_assets(): void {
		if ( ! function_exists( 'wp_register_script' ) ) {
			return;
		}

		wp_register_style(
			self::FRONTEND_STYLE_HANDLE,
			CTS_POSTS_SYNC_URL . 'assets/css/churchtools-suite-posts-sync-frontend.css',
			[],
			CTS_POSTS_SYNC_VERSION
		);

		wp_register_script(
			self::BLOCK_EDITOR_SCRIPT_HANDLE,
			CTS_POSTS_SYNC_URL . 'assets/js/churchtools-suite-posts-sync-block.js',
			[ 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor' ],
			CTS_POSTS_SYNC_VERSION,
			true
		);

		$post_type_options = [];
		foreach ( self::get_supported_target_types() as $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );
			$post_type_options[] = [
				'value' => (string) $post_type,
				'label' => $post_type_obj && ! empty( $post_type_obj->labels->singular_name )
					? (string) $post_type_obj->labels->singular_name
					: (string) $post_type,
			];
		}

		wp_localize_script(
			self::BLOCK_EDITOR_SCRIPT_HANDLE,
			'ctsPostsSyncBlockData',
			[
				'blockName' => self::BLOCK_NAME,
				'defaultPostType' => (string) get_option( 'churchtools_suite_ct_posts_target_type', 'post' ),
				'postTypeOptions' => $post_type_options,
			]
		);
	}

	/**
	 * Render shortcode output.
	 *
	 * @param array<string, string> $atts Shortcode attributes.
	 */
	public static function render_shortcode( $atts = [] ): string {
		$atts = shortcode_atts(
			[
				'limit' => 10,
				'post_type' => '',
				'show_date' => 'true',
				'show_excerpt' => 'true',
				'excerpt_words' => 28,
				'only_new' => 'false',
				'only_synced' => 'true',
			],
			(array) $atts,
			self::SHORTCODE_TAG
		);

		return self::render_posts_list(
			[
				'limit' => (int) $atts['limit'],
				'post_type' => (string) $atts['post_type'],
				'show_date' => self::parse_bool( $atts['show_date'], true ),
				'show_excerpt' => self::parse_bool( $atts['show_excerpt'], true ),
				'excerpt_words' => (int) $atts['excerpt_words'],
				'only_new' => self::parse_bool( $atts['only_new'], false ),
				'only_synced' => self::parse_bool( $atts['only_synced'], true ),
			],
			false
		);
	}

	/**
	 * Render dynamic block output.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 */
	public static function render_block( array $attributes = [] ): string {
		return self::render_posts_list(
			[
				'limit' => isset( $attributes['limit'] ) ? (int) $attributes['limit'] : 10,
				'post_type' => isset( $attributes['postType'] ) ? (string) $attributes['postType'] : '',
				'show_date' => isset( $attributes['showDate'] ) ? (bool) $attributes['showDate'] : true,
				'show_excerpt' => isset( $attributes['showExcerpt'] ) ? (bool) $attributes['showExcerpt'] : true,
				'excerpt_words' => isset( $attributes['excerptWords'] ) ? (int) $attributes['excerptWords'] : 28,
				'only_new' => isset( $attributes['onlyNew'] ) ? (bool) $attributes['onlyNew'] : false,
				'only_synced' => isset( $attributes['onlySynced'] ) ? (bool) $attributes['onlySynced'] : true,
			],
			true
		);
	}

	/**
	 * Render list markup for shortcode and block.
	 *
	 * @param array<string, mixed> $config Render configuration.
	 */
	private static function render_posts_list( array $config, bool $is_block ): string {
		self::enqueue_frontend_assets();

		$limit = isset( $config['limit'] ) ? (int) $config['limit'] : 10;
		if ( $limit < 1 ) {
			$limit = 1;
		}
		if ( $limit > 100 ) {
			$limit = 100;
		}

		$post_type = isset( $config['post_type'] ) ? sanitize_key( (string) $config['post_type'] ) : '';
		if ( $post_type === '' ) {
			$post_type = (string) get_option( 'churchtools_suite_ct_posts_target_type', 'post' );
		}

		$supported_types = self::get_supported_target_types();
		if ( ! in_array( $post_type, $supported_types, true ) ) {
			$post_type = in_array( 'post', $supported_types, true ) ? 'post' : ( $supported_types[0] ?? 'post' );
		}

		$show_date = ! empty( $config['show_date'] );
		$show_excerpt = ! empty( $config['show_excerpt'] );
		$only_new = ! empty( $config['only_new'] );
		$only_synced = ! empty( $config['only_synced'] );
		$excerpt_words = isset( $config['excerpt_words'] ) ? (int) $config['excerpt_words'] : 28;
		if ( $excerpt_words < 8 ) {
			$excerpt_words = 8;
		}
		if ( $excerpt_words > 80 ) {
			$excerpt_words = 80;
		}

		$query_args = [
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => $only_new ? max( $limit * 4, 50 ) : $limit,
			'orderby' => 'date',
			'order' => 'DESC',
			'ignore_sticky_posts' => true,
		];

		if ( $only_synced ) {
			$query_args['meta_query'] = [
				[
					'key' => self::SYNCED_POST_META_KEY,
					'compare' => 'EXISTS',
				],
			];
		}

		$query = new WP_Query( $query_args );
		$posts = $query->posts;

		if ( $only_new ) {
			$posts = array_values(
				array_filter(
					$posts,
					static function ( $post ) {
						return $post instanceof WP_Post && self::is_post_currently_new( $post );
					}
				)
			);

			if ( count( $posts ) > $limit ) {
				$posts = array_slice( $posts, 0, $limit );
			}
		}

		if ( empty( $posts ) ) {
			wp_reset_postdata();
			return '<div class="cts-posts-sync-list cts-posts-sync-list-empty">' . esc_html__( 'Keine Beiträge gefunden.', 'churchtools-suite-posts-sync' ) . '</div>';
		}

		$wrapper_classes = $is_block
			? 'cts-posts-sync-list cts-posts-sync-list-block'
			: 'cts-posts-sync-list cts-posts-sync-list-shortcode';

		ob_start();
		echo '<div class="' . esc_attr( $wrapper_classes ) . '">';
		foreach ( $posts as $post ) {
			setup_postdata( $post );
			$post_id = (int) $post->ID;
			$title = get_the_title( $post_id );
			$permalink = get_permalink( $post_id );
			$post_type_obj = get_post_type_object( (string) get_post_type( $post_id ) );
			$allow_link = $post_type_obj && ! empty( $post_type_obj->publicly_queryable ) && ! empty( $post_type_obj->public );
			$excerpt = '';
			if ( $show_excerpt ) {
				$raw_excerpt = get_the_excerpt( $post_id );
				if ( $raw_excerpt === '' ) {
					$raw_excerpt = get_post_field( 'post_content', $post_id );
				}
				$excerpt = wp_trim_words( wp_strip_all_tags( (string) $raw_excerpt ), $excerpt_words );
			}
			$date_display = get_the_date( get_option( 'date_format' ), $post_id );
			?>
			<article class="cts-posts-sync-item">
				<h3 class="cts-posts-sync-title">
					<?php if ( $allow_link && ! empty( $permalink ) ) : ?>
						<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
					<?php else : ?>
						<span><?php echo esc_html( $title ); ?></span>
					<?php endif; ?>
				</h3>
				<?php if ( $show_date && $date_display ) : ?>
					<p class="cts-posts-sync-date"><?php echo esc_html( $date_display ); ?></p>
				<?php endif; ?>
				<?php if ( $show_excerpt && $excerpt !== '' ) : ?>
					<p class="cts-posts-sync-excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>
			</article>
			<?php
		}
		echo '</div>';
		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Check whether a synced post is currently within its "new" time window.
	 */
	private static function is_post_currently_new( WP_Post $post ): bool {
		$start_value = '';
		$start_meta_keys = [ self::PUBLICATION_DATE_META_KEY, self::PUBLISHED_DATE_META_KEY ];
		foreach ( $start_meta_keys as $meta_key ) {
			$candidate = (string) get_post_meta( $post->ID, $meta_key, true );
			if ( trim( $candidate ) !== '' ) {
				$start_value = $candidate;
				break;
			}
		}

		$end_value = (string) get_post_meta( $post->ID, self::EXPIRATION_DATE_META_KEY, true );

		$start = self::parse_datetime_value( $start_value );
		$end = self::parse_datetime_value( $end_value );
		$now = current_datetime();

		if ( $start instanceof DateTimeImmutable && $now < $start ) {
			return false;
		}

		if ( $end instanceof DateTimeImmutable ) {
			return $now <= $end;
		}

		return false;
	}

	/**
	 * Parse an incoming API datetime string into site-local immutable datetime.
	 */
	private static function parse_datetime_value( string $value ): ?DateTimeImmutable {
		$value = trim( $value );
		if ( $value === '' ) {
			return null;
		}

		try {
			$datetime = new DateTimeImmutable( $value );
			$site_timezone = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
			return $datetime->setTimezone( $site_timezone );
		} catch ( Exception $exception ) {
			return null;
		}
	}

	/**
	 * Enqueue frontend style used by shortcode and block output.
	 */
	private static function enqueue_frontend_assets(): void {
		wp_enqueue_style(
			self::FRONTEND_STYLE_HANDLE,
			CTS_POSTS_SYNC_URL . 'assets/css/churchtools-suite-posts-sync-frontend.css',
			[],
			CTS_POSTS_SYNC_VERSION
		);
	}

	/**
	 * @return array<int, string>
	 */
	private static function get_supported_target_types(): array {
		if ( class_exists( 'ChurchTools_Suite_Posts_Sync' ) && method_exists( 'ChurchTools_Suite_Posts_Sync', 'get_supported_target_types' ) ) {
			return ChurchTools_Suite_Posts_Sync::get_supported_target_types();
		}

		return [ 'post', 'page', defined( 'CTS_POSTS_SYNC_CPT' ) ? CTS_POSTS_SYNC_CPT : 'ct_post' ];
	}

	/**
	 * Parse common truthy/falsy string values.
	 *
	 * @param mixed $value Raw input value.
	 */
	private static function parse_bool( $value, bool $default = false ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( $value === null ) {
			return $default;
		}

		$normalized = strtolower( trim( (string) $value ) );
		if ( in_array( $normalized, [ '1', 'true', 'yes', 'on' ], true ) ) {
			return true;
		}
		if ( in_array( $normalized, [ '0', 'false', 'no', 'off' ], true ) ) {
			return false;
		}

		return $default;
	}
}
