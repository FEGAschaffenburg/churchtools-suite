<?php
/**
 * ChurchTools Suite Demo - Demo Pages (CPT)
 * 
 * Erlaubt Demo-Usern, ihre eigenen Test-Seiten zu erstellen
 * um Events und Shortcodes zu testen.
 * 
 * - Jeder Demo-User sieht nur seine eigenen Seiten
 * - Seiten sind PRIVAT (nicht öffentlich sichtbar)
 * - Beim User-Löschen werden auch seine Seiten gelöscht
 * 
 * NUR IM DEMO-PLUGIN verfügbar
 * 
 * @package ChurchTools_Suite_Demo
 * @since   1.0.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Demo_Template_CPT {
	/**
	 * Enable verbose debug behavior only in debug environments.
	 */
	private static function is_debug_enabled(): bool {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}
	
	/**
	 * Register Custom Post Type for Demo Pages
	 */
	public static function register(): void {
		// Ensure cts_demo_user role exists (copy of editor)
		self::ensure_demo_user_role();
		
		$args = [
			'label'               => 'Demo Pages',
			'description'         => 'Test-Seiten zum Ausprobieren von Events und Shortcodes',
			'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ], // Gutenberg & Post Features
			'hierarchical'        => false,
			'public'              => true, // ✅ ÖFFENTLICH SICHTBAR - URLs generieren
			'publicly_queryable'  => true, // ✅ Frontend URLs aufrufbar
			'show_ui'             => true,
		'show_in_menu'        => true, // ✅ Eigenständiges Top-Level Menü (direkt unter ChurchTools)
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => true, // Gutenberg Editor Support!
		'menu_position'       => 31, // ✅ DIREKT UNTER ChurchTools Suite (Position 30)
		'menu_icon'           => 'dashicons-welcome-write-blog', // ✅ ICON: Seiten/Content
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => true, // NICHT in Suche
			'rewrite'             => [
				'slug'       => 'demo-pages', // URL-Slug: /demo-pages/{name}
				'with_front' => false,
				'hierarchical' => false,
			],
			'capability_type'     => 'cts_demo_page',
			'capabilities'        => [
				// v1.1.0.8: Complete capability mapping rewrite
				// Match WordPress standard naming conventions
				'edit_post'              => 'edit_cts_demo_page',
				'read_post'              => 'read_cts_demo_page',
				'delete_post'            => 'delete_cts_demo_page',
				'edit_posts'             => 'edit_cts_demo_pages',
				'edit_others_posts'      => 'edit_others_cts_demo_pages',
				'publish_posts'          => 'publish_cts_demo_pages',
				'read_private_posts'     => 'read_private_cts_demo_pages',
				'delete_posts'           => 'delete_cts_demo_pages',
				'delete_private_posts'   => 'delete_private_cts_demo_pages',
				'delete_published_posts' => 'delete_published_cts_demo_pages',
				'delete_others_posts'    => 'delete_others_cts_demo_pages',
				'edit_private_posts'     => 'edit_private_cts_demo_pages',
				'edit_published_posts'   => 'edit_published_cts_demo_pages',
				'create_posts'           => 'edit_cts_demo_pages',  // WordPress convention: edit_* for creation
			],
			'map_meta_cap'        => true,
			'labels'              => [
				'name'                     => 'Demo Pages',
				'singular_name'            => 'Demo Page',
			'menu_name'                => 'CTS Demo',
			'all_items'                => 'Test-Seiten',
			'add_new'                  => 'Neue erstellen',
			'add_new_item'             => 'Neue Test-Seite erstellen',
			'edit_item'                => 'Test-Seite bearbeiten',
			'new_item'                 => 'Neue Test-Seite',
			'view_item'                => 'Test-Seite anzeigen',
			'search_items'             => 'Test-Seiten durchsuchen',
			'not_found'                => 'Keine Test-Seiten gefunden',
			'not_found_in_trash'       => 'Keine Test-Seiten im Papierkorb',
			],
		];
		
		$result = register_post_type( 'cts_demo_page', $args );
		
		// Always log registration failures, but avoid noisy success logs in production.
		if ( is_wp_error( $result ) ) {
			error_log( 'ChurchTools Demo: CPT register error - ' . $result->get_error_message() );
		} elseif ( self::is_debug_enabled() ) {
			error_log( 'ChurchTools Demo: CPT registered successfully' );
		}
		
		// Flush rewrite rules on first registration
		if ( ! get_option( 'churchtools_suite_demo_pages_rewrite_flushed' ) ) {
			flush_rewrite_rules();
			update_option( 'churchtools_suite_demo_pages_rewrite_flushed', true );
		}
		
		// Filter query to show only user's own pages
		add_action( 'pre_get_posts', [ __CLASS__, 'filter_demo_pages_by_user' ] );
		
		// Add custom admin columns
		add_filter( 'manage_cts_demo_page_posts_columns', [ __CLASS__, 'add_custom_columns' ] );
		add_action( 'manage_cts_demo_page_posts_custom_column', [ __CLASS__, 'render_custom_columns' ], 10, 2 );
		add_filter( 'manage_edit-cts_demo_page_sortable_columns', [ __CLASS__, 'add_sortable_columns' ] );
		
		// FIX: Map meta capabilities for demo pages (v1.1.0.8)
		// Required because demo users have edit_others_posts = false
		add_filter( 'map_meta_cap', [ __CLASS__, 'map_demo_page_meta_caps' ], 10, 4 );
		
		if ( self::is_debug_enabled() ) {
			// DEBUG: Check page creation access
			add_action( 'admin_init', [ __CLASS__, 'debug_page_creation_access' ] );
			
			// DEBUG: Track redirects
			add_filter( 'wp_redirect', [ __CLASS__, 'debug_redirect' ], 10, 2 );
		}
		
		// Hide Media menu for demo users (keep upload capability for editor)
		add_action( 'admin_menu', [ __CLASS__, 'hide_media_menu_for_demo_users' ], 999 );
	}
	
	/**
	 * Hide Media Library menu for demo users
	 * 
	 * Demo users keep 'upload_files' capability for image uploads in editor,
	 * but don't need direct access to Media Library menu.
	 * 
	 * @since 1.1.1.0
	 */
	public static function hide_media_menu_for_demo_users(): void {
		$user = wp_get_current_user();
		
		// Only hide for demo_tester role
		if ( in_array( 'demo_tester', $user->roles, true ) ) {
			remove_menu_page( 'upload.php' ); // Media Library
		}
	}
	
	/**
	 * Ensure demo_tester role exists with restricted capabilities
	 * 
	 * Creates the role if it doesn't exist, updates it if it does.
	 * Called on every plugin load to ensure role is always available.
	 * 
	 * RESTRICTED: Only CPT capabilities + read + upload_files + ChurchTools access
	 */
	private static function ensure_demo_user_role(): void {
		// Define CPT-specific capabilities
		$cpt_caps = [
			'edit_cts_demo_pages',
			'edit_others_cts_demo_pages',
			'publish_cts_demo_pages',
			'read_private_cts_demo_pages',
			'delete_cts_demo_pages',
			'delete_private_cts_demo_pages',
			'delete_published_cts_demo_pages',
			'delete_others_cts_demo_pages',
			'edit_private_cts_demo_pages',
			'edit_published_cts_demo_pages',
			'edit_cts_demo_page',
			'read_cts_demo_page',
			'delete_cts_demo_page',
			'manage_cts_demo_pages',
		];
		
		// Ensure administrator has CPT capabilities
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			foreach ( $cpt_caps as $cap ) {
				if ( ! $admin_role->has_cap( $cap ) ) {
					$admin_role->add_cap( $cap );
				}
			}
		}
		
		// Check if demo_tester role already exists
		$role = get_role( 'demo_tester' );
		
		if ( ! $role ) {
			// Create role with minimal capabilities
			add_role(
				'demo_tester',
				__( 'Demo Tester', 'churchtools-suite-demo' ),
				[
					'read' => true,
					'upload_files' => true,
					'manage_churchtools_suite' => true, // Access to ChurchTools menu
				]
			);
			
			$role = get_role( 'demo_tester' );
			if ( self::is_debug_enabled() ) {
				error_log( 'ChurchTools Demo: Created demo_tester role with restricted capabilities' );
			}
		}
		
		// Always ensure ChurchTools access capability is present
		if ( $role && ! $role->has_cap( 'manage_churchtools_suite' ) ) {
			$role->add_cap( 'manage_churchtools_suite' );
		}
		
		// Add CPT-specific capabilities to demo_tester (always, in case they're missing)
		if ( $role ) {
			foreach ( $cpt_caps as $cap ) {
				$role->add_cap( $cap );
			}
		}
	}
	
	/**
	 * Helper: Write to debug log
	 *
	 * @param string $message Log message
	 */
	private static function debug_log( string $message ): void {
		if ( ! self::is_debug_enabled() ) {
			return;
		}

		$log_file = WP_CONTENT_DIR . '/cts-demo-debug.log';
		$timestamp = date( 'Y-m-d H:i:s' );
		file_put_contents( $log_file, "[{$timestamp}] {$message}\n", FILE_APPEND );
		error_log( "[CTS Demo] {$message}" );
	}
	
	/**
	 * Debug page creation access (v1.1.0.9)
	 * 
	 * Logs when a demo user tries to create a new page
	 */
	public static function debug_page_creation_access(): void {
		// Only on post-new.php for cts_demo_page
		global $pagenow;
		
		if ( $pagenow !== 'post-new.php' ) {
			return;
		}
		
		$post_type = $_GET['post_type'] ?? 'post';
		
		if ( $post_type !== 'cts_demo_page' ) {
			return;
		}
		
		// Only for demo users
		$user = wp_get_current_user();
		if ( ! in_array( 'demo_tester', $user->roles, true ) ) {
			return;
		}
		
		self::debug_log( '========================================' );
		self::debug_log( 'POST-NEW.PHP ACCESSED' );
		self::debug_log( '========================================' );
		self::debug_log( "User ID: {$user->ID}" );
		self::debug_log( "User Login: {$user->user_login}" );
		self::debug_log( "User Roles: " . implode( ', ', $user->roles ) );
		self::debug_log( "Post Type: {$post_type}" );
		
		// Check key capabilities
		$cpt = get_post_type_object( 'cts_demo_page' );
		
		if ( $cpt ) {
			self::debug_log( "CPT create_posts cap: {$cpt->cap->create_posts}" );
			self::debug_log( "User has create_posts: " . ( current_user_can( $cpt->cap->create_posts ) ? 'YES' : 'NO' ) );
			self::debug_log( "User has edit_cts_demo_pages: " . ( current_user_can( 'edit_cts_demo_pages' ) ? 'YES' : 'NO' ) );
			self::debug_log( "User has publish_cts_demo_pages: " . ( current_user_can( 'publish_cts_demo_pages' ) ? 'YES' : 'NO' ) );
		} else {
			self::debug_log( "ERROR: CPT cts_demo_page not found!" );
		}
		
		// Check all user caps
		self::debug_log( "All user capabilities:" );
		foreach ( $user->allcaps as $cap => $value ) {
			if ( $value && strpos( $cap, 'demo' ) !== false ) {
				self::debug_log( "  - {$cap}: " . ( $value ? 'YES' : 'NO' ) );
			}
		}
		
		self::debug_log( '========================================' );
	}
	
	/**
	 * Grant create_posts capability for cts_demo_page on-the-fly
	 * 
	 * This is called BEFORE WordPress checks capabilities, so we can
	 * dynamically grant the create_posts capability when needed.
	 *
	 * @param array   $allcaps All capabilities of the user
	 * @param array   $caps    Required capabilities
	 * @param array   $args    Additional context (capability, user_id, object_id)
	 * @param WP_User $user    User object
	 * @return array Modified capabilities
	 */
	public static function grant_demo_page_create_cap( array $allcaps, array $caps, array $args, $user ): array {
		// Only for demo users
		if ( ! in_array( 'demo_tester', $user->roles ?? [], true ) ) {
			return $allcaps;
		}
		
		// Check if we're trying to create a cts_demo_page
		$post_type = $_GET['post_type'] ?? $_POST['post_type'] ?? 'post';
		
		if ( $post_type !== 'cts_demo_page' ) {
			return $allcaps;
		}
		
		// Get the CPT object
		$cpt = get_post_type_object( 'cts_demo_page' );
		
		if ( ! $cpt ) {
			return $allcaps;
		}
		
		// Grant all required capabilities
		$allcaps[ $cpt->cap->create_posts ] = true;
		$allcaps[ $cpt->cap->edit_posts ] = true;
		$allcaps[ $cpt->cap->publish_posts ] = true;
		
		self::debug_log( "GRANTED CAPABILITIES: create={$cpt->cap->create_posts}, edit={$cpt->cap->edit_posts}, publish={$cpt->cap->publish_posts}" );
		
		return $allcaps;
	}
	
	/**
	 * Map meta capabilities for demo pages (v1.1.0.8)
	 * 
	 * This fixes the redirect issue when demo users try to create new pages.
	 * WordPress checks edit_others_posts for new posts, but demo users don't have that.
	 * We explicitly tell WordPress: "Use manage_cts_demo_pages instead"
	 *
	 * @param array  $caps    Required capabilities
	 * @param string $cap     Capability being checked
	 * @param int    $user_id User ID
	 * @param array  $args    Additional context (post ID, etc.)
	 * @return array Modified capabilities
	 */
	public static function map_demo_page_meta_caps( array $caps, string $cap, int $user_id, array $args ): array {
		// DEBUG: Log all capability checks for demo pages
		$debug = false;
		
		// Only enable debug for demo users
		$user = get_userdata( $user_id );
		if ( self::is_debug_enabled() && $user && in_array( 'demo_tester', $user->roles, true ) ) {
			$debug = true;
		}
		
		if ( $debug && in_array( $cap, [ 'edit_post', 'delete_post', 'read_post' ], true ) ) {
			$log_file = WP_CONTENT_DIR . '/cts-demo-debug.log';
			$log_msg = sprintf(
				"[%s] map_meta_cap: cap=%s, user_id=%d, args=%s, original_caps=%s\n",
				date( 'Y-m-d H:i:s' ),
				$cap,
				$user_id,
				json_encode( $args ),
				json_encode( $caps )
			);
			file_put_contents( $log_file, $log_msg, FILE_APPEND );
			error_log( $log_msg );
		}
		
		// Only handle demo page capabilities
		if ( ! in_array( $cap, [ 'edit_post', 'delete_post', 'read_post' ], true ) ) {
			return $caps;
		}
		
		// Get post if ID provided
		$post = null;
		if ( ! empty( $args[0] ) ) {
			$post = get_post( $args[0] );
		}
		
		// Determine if this is about cts_demo_page
		$is_demo_page = false;
		if ( $post && $post->post_type === 'cts_demo_page' ) {
			// Existing demo page
			$is_demo_page = true;
		} elseif ( ! $post && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'cts_demo_page' ) {
			// New demo page (creating)
			$is_demo_page = true;
		} elseif ( ! $post && isset( $_POST['post_type'] ) && $_POST['post_type'] === 'cts_demo_page' ) {
			// New demo page (saving)
			$is_demo_page = true;
		}
		
		// Only handle demo pages
		if ( ! $is_demo_page ) {
			return $caps;
		}
		
		// Get post type object
		$post_type = get_post_type_object( 'cts_demo_page' );
		if ( ! $post_type ) {
			if ( $debug ) {
				$log_msg = '[CTS Demo Debug] Post type cts_demo_page not found!';
				file_put_contents( WP_CONTENT_DIR . '/cts-demo-debug.log', "[" . date( 'Y-m-d H:i:s' ) . "] {$log_msg}\n", FILE_APPEND );
				error_log( $log_msg );
			}
			return $caps;
		}
		
		// Map capabilities based on action
		switch ( $cap ) {
			case 'edit_post':
				if ( $post ) {
					// Editing existing post
					if ( $post->post_author == $user_id ) {
						// Own post
						$caps = [ $post_type->cap->edit_posts ];
						if ( $debug ) {
							error_log( "[CTS Demo Debug] edit_post (own): {$post_type->cap->edit_posts}" );
						}
					} elseif ( user_can( $user_id, 'manage_options' ) ) {
						// Administrator can edit all demo pages
						$caps = [ $post_type->cap->edit_others_posts ];
						if ( $debug ) {
							error_log( "[CTS Demo Debug] edit_post (admin): {$post_type->cap->edit_others_posts}" );
						}
					} else {
						// Someone else's post - not allowed for demo users
						$caps = [ 'do_not_allow' ];
						if ( $debug ) {
							error_log( "[CTS Demo Debug] edit_post (others): do_not_allow" );
						}
					}
				} else {
					// Creating new post
					$caps = [ $post_type->cap->create_posts ];
					if ( $debug ) {
						error_log( "[CTS Demo Debug] edit_post (new): {$post_type->cap->create_posts}" );
					}
				}
				break;
				
			case 'delete_post':
				if ( $post && $post->post_author == $user_id ) {
					// Can delete own posts (status-specific)
					if ( 'publish' === $post->post_status ) {
						$caps = [ $post_type->cap->delete_published_posts ];
					} elseif ( 'private' === $post->post_status ) {
						$caps = [ $post_type->cap->delete_private_posts ];
					} else {
						$caps = [ $post_type->cap->delete_posts ];
					}
					if ( $debug ) {
						error_log( "[CTS Demo Debug] delete_post (own): " . json_encode( $caps ) );
					}
				} elseif ( user_can( $user_id, 'manage_options' ) ) {
					// Administrator can delete all demo pages (status-specific)
					if ( $post && 'publish' === $post->post_status ) {
						$caps = [ $post_type->cap->delete_published_posts ];
					} elseif ( $post && 'private' === $post->post_status ) {
						$caps = [ $post_type->cap->delete_private_posts ];
					} else {
						$caps = [ $post_type->cap->delete_others_posts ];
					}
					if ( $debug ) {
						error_log( "[CTS Demo Debug] delete_post (admin): " . json_encode( $caps ) );
					}
				} else {
					$caps = [ 'do_not_allow' ];
					if ( $debug ) {
						error_log( "[CTS Demo Debug] delete_post (others): do_not_allow" );
					}
				}
				break;
				
			case 'read_post':
				if ( $post && ( $post->post_author == $user_id || user_can( $user_id, 'manage_options' ) ) ) {
					// Can read own posts or admin can read all
					$caps = [ 'read' ];
					if ( $debug ) {
						error_log( "[CTS Demo Debug] read_post: read" );
					}
				} else {
					$caps = [ 'do_not_allow' ];
					if ( $debug ) {
						error_log( "[CTS Demo Debug] read_post: do_not_allow" );
					}
				}
				break;
		}
		
		if ( $debug ) {
			error_log( "[CTS Demo Debug] Final caps: " . json_encode( $caps ) );
		}
		
		return $caps;
	}
	
	/**
	 * Debug redirects (v1.1.0.9)
	 * 
	 * Track when WordPress redirects demo users
	 *
	 * @param string $location Redirect location
	 * @param int    $status   HTTP status code
	 * @return string Modified location
	 */
	public static function debug_redirect( string $location, int $status ): string {
		$user = wp_get_current_user();
		
		// Only track redirects for demo users
		if ( ! in_array( 'demo_tester', $user->roles, true ) ) {
			return $location;
		}
		
		// Get backtrace to see where redirect is coming from
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );
		$caller = 'unknown';
		
		foreach ( $backtrace as $trace ) {
			if ( isset( $trace['file'] ) && strpos( $trace['file'], 'wp-admin' ) !== false ) {
				$caller = basename( $trace['file'] ) . ':' . ( $trace['line'] ?? '?' );
				break;
			}
		}
		
		self::debug_log( '========================================' );
		self::debug_log( 'REDIRECT DETECTED' );
		self::debug_log( '========================================' );
		self::debug_log( "User: {$user->user_login} (ID: {$user->ID})" );
		self::debug_log( "From: " . ( $_SERVER['REQUEST_URI'] ?? 'unknown' ) );
		self::debug_log( "To: {$location}" );
		self::debug_log( "Status: {$status}" );
		self::debug_log( "Called from: {$caller}" );
		self::debug_log( "Current page: " . ( $GLOBALS['pagenow'] ?? 'unknown' ) );
		self::debug_log( "Post type: " . ( $_GET['post_type'] ?? $_POST['post_type'] ?? 'none' ) );
		self::debug_log( '========================================' );
		
		return $location;
	}
	
	/**
	 * Filter demo pages - Demo Users see only their OWN pages
	 * 
	 * @param WP_Query $query Query object
	 */
	public static function filter_demo_pages_by_user( $query ): void {
		// Only in admin
		if ( ! is_admin() ) {
			return;
		}
		
		// Only for demo pages
		if ( $query->get( 'post_type' ) !== 'cts_demo_page' ) {
			return;
		}
		
		$user = wp_get_current_user();
		
		// Demo users see only their own pages
		if ( in_array( 'demo_tester', (array) $user->roles, true ) ) {
			$query->set( 'author', $user->ID );
		}
		// Admins see all (no filter)
	}
	
	/**
	 * Add custom columns to Demo Pages list
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 */
	public static function add_custom_columns( array $columns ): array {
		// Insert 'demo_user' column after 'title'
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( $key === 'title' ) {
				$new_columns['demo_user'] = 'Demo User';
			}
		}
		return $new_columns;
	}
	
	/**
	 * Render custom column content
	 *
	 * @param string $column_name Column name
	 * @param int    $post_id     Post ID
	 */
	public static function render_custom_columns( string $column_name, int $post_id ): void {
		if ( $column_name !== 'demo_user' ) {
			return;
		}
		
		$post = get_post( $post_id );
		if ( ! $post ) {
			echo '—';
			return;
		}
		
		$author = get_user_by( 'id', $post->post_author );
		if ( ! $author ) {
			echo '—';
			return;
		}
		
		// Show display name and user login
		printf(
			'<strong>%s</strong><br><small>%s</small>',
			esc_html( $author->display_name ),
			esc_html( $author->user_login )
		);
	}
	
	/**
	 * Make custom columns sortable
	 *
	 * @param array $columns Sortable columns
	 * @return array Modified sortable columns
	 */
	public static function add_sortable_columns( array $columns ): array {
		$columns['demo_user'] = 'author';
		return $columns;
	}
	
	/**
	 * Register capabilities for Demo Pages
	 */
	public static function add_capabilities(): void {
		$caps = [
			'read',                             // WordPress Core - ESSENTIELL!
			'manage_cts_demo_pages',
			'edit_cts_demo_page',
			'read_cts_demo_page',               // Lesen einzelner Pages - WICHTIG!
			'edit_cts_demo_pages',              // Plural - WICHTIG!
			'view_cts_demo_pages',
			'delete_cts_demo_page',
			'delete_cts_demo_pages',            // Plural - WICHTIG!
			'publish_cts_demo_pages',           // Veröffentlichen - WICHTIG!
			'edit_published_cts_demo_pages',    // Published bearbeiten - WICHTIG!
			'delete_published_cts_demo_pages',  // Published löschen - WICHTIG!
		];
		
		// Add to Administrator
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( $caps as $cap ) {
				$admin->add_cap( $cap );
			}
		}
		
		// Add to cts_demo_user role
		$demo_user = get_role( 'cts_demo_user' );
		if ( $demo_user ) {
			foreach ( $caps as $cap ) {
				$demo_user->add_cap( $cap );
			}
		}
		
		// Update ALL existing demo users with current capabilities
		self::update_existing_demo_users_capabilities( $caps );
	}
	
	/**
	 * Update capabilities for all existing demo users
	 * 
	 * This ensures that when we add new capabilities to the role,
	 * existing users get them too (not just new users)
	 * 
	 * @param array $caps Capabilities to add
	 */
	private static function update_existing_demo_users_capabilities( array $caps ): void {
		$demo_users = get_users( [
			'role'   => 'cts_demo_user',
			'fields' => 'ID',
		] );
		
		foreach ( $demo_users as $user_id ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user ) {
				foreach ( $caps as $cap ) {
					$user->add_cap( $cap );
				}
			}
		}
		
		// Log für Debugging (nur wenn WP_DEBUG aktiv)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'ChurchTools Demo: Updated capabilities for %d existing demo users',
				count( $demo_users )
			) );
		}
	}
	
	/**
	 * Clean up demo pages when user is deleted
	 * 
	 * Hook this in the main plugin init
	 * 
	 * @param int $user_id User ID being deleted
	 */
	public static function delete_user_demo_pages( int $user_id ): void {
		global $wpdb;
		
		// Get all demo pages by this user
		$pages = get_posts( [
			'post_type'   => 'cts_demo_page',
			'author'      => $user_id,
			'numberposts' => -1,
			'fields'      => 'ids',
		] );
		
		// Force delete all pages (skip trash)
		foreach ( $pages as $page_id ) {
			wp_delete_post( $page_id, true );
		}
	}
}
