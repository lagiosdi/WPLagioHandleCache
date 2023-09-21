<?php

namespace Lagio\HandleCache;

/**
 * Singleton trait for creating a single instance of a class.
 */
trait Singleton {
	/**
	 * @var object|null Stores the single instance of the class.
	 */
	private static ?object $instance = null;

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {
		// Initialize your singleton instance here, if needed
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return object The singleton instance.
	 */
	public static function getInstance(): object {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
