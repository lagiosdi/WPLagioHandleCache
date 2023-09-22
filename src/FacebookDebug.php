<?php

namespace Lagio\HandleCache;

/**
 * Class FacebookSharingDebug
 *
 * This class is responsible for handling Facebook sharing debug requests.
 */
class FacebookDebug extends AbstractHandleCache {
	use Singleton;

	private string $app_id = '';
	private string $app_secret = '';

	private function __construct() {
		$this->setFacebookCredentials();
	}

	private function setFacebookCredentials(): void {
		defined( 'FACEBOOK_APP_ID' ) && ( $this->app_id = FACEBOOK_APP_ID );
		defined( 'FACEBOOK_APP_SECRET' ) && ( $this->app_secret = FACEBOOK_APP_SECRET );
	}

	public function setUrlsToRefresh(): self {
		$this->setPermalink();

		return $this;
	}

	public function debugUrls(): void {
		if ( empty( $this->urls ) ) {
			return;
		}
		$this->urls = array_unique( $this->urls );


		// Set the URL for the Facebook Graph API endpoint
		$url = 'https://graph.facebook.com/v14.0/';

		foreach ( $this->urls as $url ) {
			$this->debugUrl( $url );
		}

	}


	public function debugUrl( $url ): void {
		$app_id     = $this->app_id;
		$app_secret = $this->app_secret;

		$access_token = $app_id . '|' . $app_secret;
		$data         = array(
			'scrape'       => 'true',
			'id'           => $url,
			'access_token' => $access_token,
		);

		// Set the request arguments
		$args = array(
			'body' => http_build_query( $data ),
		);

		// Make the remote POST request
		wp_remote_post( $url, $args );
	}


}
