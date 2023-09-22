<?php

namespace Lagio\HandleCache;

/**
 * Class HandleNginxCache
 *
 * A class for handling Nginx cache operations.
 *
 * @package Lagio\HandleCache
 */
final class HandleNginxCache extends AbstractHandleCache {

	use Singleton;

	public function setUrlsToRefresh(): self {
		$post_id = $this->post_id;
		if ( ! $post_id ) {
			return $this;
		}
		$this->setPermalink();
		$this->setAMPPermalink();
		$this->setHomeUrl();
		$this->setPostCategoriesUrls();

		return $this;

	}

	/**
	 * Refresh the cache by post ID.
	 *
	 * @param int $post_id The ID of the WordPress post.
	 *
	 * @return mixed|false Response body if successful, or false on error.
	 */
	public function refreshCacheByPostId( int $post_id ): mixed {
		// Get the post's permalink URL
		$post_url = get_permalink( $post_id );

		if ( $post_url ) {
			// Call RefreshCacheByUrl method to refresh cache
			return $this->refreshCacheByUrl( $post_url );
		} else {
			return false; // Return false if the post URL cannot be determined
		}
	}

	/**
	 * Refresh the cache by a custom URL.
	 *
	 * @param string $url The custom URL to refresh the cache.
	 *
	 * @return mixed|false Response body if successful, or false on error.
	 */
	public function refreshCacheByUrlWithParseUrl( string $url ): mixed {
		// Add the custom header to refresh the cache
		// get domain from url
		$host = parse_url( $url, PHP_URL_HOST );
		//get server ip
		$ip_server = $_SERVER['SERVER_ADDR'];

		$headers = array(
			'X-Refresh-Cache' => '1',
		);
		if ( $host ) {
			$headers['Host'] = $host;
			$url             = str_replace( $host, $ip_server, $url );
		}

		$args = array(
			'headers' => $headers,
		);

		add_filter( 'https_ssl_verify', '__return_false' );
		// Send a GET request to the URL with the custom header
		if ( is_wp_error( wp_remote_get( $url, $args ) ) ) {
			return false; // Return false to indicate an error
		}
		add_filter( 'https_ssl_verify', '__return_true' );

		return true; // Return true to indicate success

	}


	public function refreshCacheByUrl( string $url ): mixed {


		$headers = array(
			'X-Refresh-Cache' => '1',
		);

		$args = array(
			'headers' => $headers,
		);

		// Send a GET request to the URL with the custom header
		if ( is_wp_error( wp_remote_get( $url, $args ) ) ) {
			return false; // Return false to indicate an error
		}

		return true; // Return true to indicate success

	}

	public function Refresh(): void {
		if ( empty( $this->urls ) ) {
			return;
		}
		// Remove duplicate URLs
		$this->urls = array_unique( $this->urls );

		foreach ( $this->urls as $url ) {
			$this->refreshCacheByUrl( $url );
		}
	}
}
