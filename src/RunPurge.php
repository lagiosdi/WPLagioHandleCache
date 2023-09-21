<?php

namespace Lagio\HandleCache;

class RunPurge {
	use Singleton;

	private function __construct() {
		add_action( 'transition_post_status', [ __CLASS__, 'post_transition_action' ], 10, 3 );
		add_action( 'lagio_nginx_refresh_cache', [ __CLASS__, 'refresh_nginx_cache' ], 10, 1 );
		add_action( 'lagio_cloudflare_purge_cache', [ __CLASS__, 'purge_cloudflare_cache' ], 10, 1 );
		add_action( 'lagio_fb_sharing_debug', [ __CLASS__, 'debug_facebook_share' ], 10, 1 );
	}


	public static function after_setup_theme(): void {
		if ( self::$already_run === null ) {
			self::$already_run = new self();
		}
	}

	public static function post_transition_action( $new_status, $old_status, $post ): void {
		if ( $old_status !== 'publish' && $new_status !== 'publish' ) {
			return;
		}

		$post_id = $post->ID;
		if ( $post_id ) {
			wp_schedule_single_event( time() + 3, 'lagio_nginx_refresh_cache', [ $post_id ] );
			wp_schedule_single_event( time() + 4, 'lagio_cloudflare_purge_cache', [ $post_id ] );
			wp_schedule_single_event( time() + 7, 'lagio_fb_sharing_debug', [ $post_id ] );
		}
	}

	public static function refresh_nginx_cache( $post_id ): void {
		HandleNginxCache::getInstance()
		                ->setPostId( $post_id )
		                ->setUrlsToRefresh()
		                ->Refresh();
	}

	public static function purge_cloudflare_cache( $post_id ): void {
		CloudflarePurgeCache::getInstance()
		                    ->setPostId( $post_id )
		                    ->setUrlsToRefresh()
		                    ->purgeRequest();
	}

	public static function debug_facebook_share( $post_id ): void {
		FacebookDebug::getInstance()
		             ->setPostId( $post_id )
		             ->setUrlsToRefresh()
		             ->debugUrls();

	}


}

add_action( 'after_setup_theme', [ '\Lagio\HandleCache\RunPurge', 'after_setup_theme' ] );
