<?php

namespace Lagio\HandleCache;

/**
 * Class FacebookDebug
 *
 * This class is responsible for handling Facebook sharing debug requests.
 */
class FacebookDebug extends AbstractHandleCache {
	use Singleton;

	/** @var string Facebook App ID */
	private string $app_id = '';

	/** @var string Facebook App Secret */
	private string $app_secret = '';

	/** @var string Facebook Access Token */
	private string $access_token;

	/**
	 * FacebookDebug constructor.
	 */
	private function __construct() {
		$this->setFacebookCredentials();
	}

	/**
	 * Set Facebook App credentials based on constants FACEBOOK_APP_ID and FACEBOOK_APP_SECRET.
	 */
	private function setFacebookCredentials(): void {
		defined( 'FACEBOOK_APP_ID' ) && ( $this->app_id = FACEBOOK_APP_ID );
		defined( 'FACEBOOK_APP_SECRET' ) && ( $this->app_secret = FACEBOOK_APP_SECRET );
		if ( $this->app_id && $this->app_secret ) {
			$this->access_token = $this->app_id . '|' . $this->app_secret;
		}
	}

	/**
	 * Set URLs to refresh and debug.
	 *
	 * @return $this
	 */
	public function setUrlsToRefresh(): self {
		$this->setPermalink();

		return $this;
	}

	/**
	 * Debug all the specified URLs.
	 */
	public function debugUrls(): void {
		if ( empty( $this->urls ) ) {
			return;
		}
		$this->urls = array_unique( $this->urls );

		foreach ( $this->urls as $url ) {
			$this->debugUrl( $url );
		}
	}

	/**
	 * Debug a single URL.
	 *
	 * @param string $link The URL to debug.
	 */
	public function debugUrl( $link ): void {
		$transient_name = 'lagio_facebook_debug';

		if ( get_transient( $transient_name ) ) {
			return;
		}

		if ( ! $this->access_token ) {
			return;
		}

		$url         = 'https://graph.facebook.com/?id=' . $link . '&scrape=true&access_token=' . $this->access_token;
		$remote      = wp_remote_post( $url );
		$status_code = wp_remote_retrieve_response_code( $remote );

		if ( $status_code != 200 ) {
			set_transient( $transient_name, $status_code, 60 * 60 * 6 );
		}
	}
}
