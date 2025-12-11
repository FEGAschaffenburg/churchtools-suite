<?php
/**
 * Plugin Loader - Register hooks and filters
 *
 * @package ChurchTools_Suite
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Loader {
	
	/**
	 * Actions to be registered
	 */
	protected $actions = [];
	
	/**
	 * Filters to be registered
	 */
	protected $filters = [];
	
	/**
	 * Add an action
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];
	}
	
	/**
	 * Add a filter
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];
	}
	
	/**
	 * Register all hooks
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				[ $hook['component'], $hook['callback'] ],
				$hook['priority'],
				$hook['accepted_args']
			);
		}
		
		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				[ $hook['component'], $hook['callback'] ],
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
