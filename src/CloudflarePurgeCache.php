<?php

namespace Lagio\HandleCache;

/**
 * Class CloudflarePurgeCache
 * This class handles cache purging for Cloudflare.
 */
class CloudflarePurgeCache extends AbstractHandleCache {
	use Singleton;

	private string $cloudflare_email = "";
	private string $cloudflare_api_key = "";
	private string $cloudflare_zone_id = "";


	private function __construct() {
		$this->setCloudflareCredentials();

	}

	private function setCloudflareCredentials(): void {
		defined( 'CLOUDFLARE_EMAIL' ) && ( $this->cloudflare_email = CLOUDFLARE_EMAIL );
		defined( 'CLOUDFLARE_API_KEY' ) && ( $this->cloudflare_api_key = CLOUDFLARE_API_KEY );
		defined( 'CLOUDFLARE_ZONE_ID' ) && ( $this->cloudflare_zone_id = CLOUDFLARE_ZONE_ID );
	}


	public function setUrlsToRefresh(): self {
		$post_id = $this->post_id;
		if ( ! $post_id ) {
			return $this;
		}
		$this->setPermalink();
		$this->setAMPPermalink();
		$this->setHomeUrl();
		$this->setPostCategoriesUrls();
		$this->setPostTagsUrls();

		return $this;

	}


	function purgeRequest(): void {
		$transient_name = 'lagio_cloudflare_purge_urls';
		$transient      = get_transient( $transient_name );
		if ( $transient ) {
			return;
		}

		if ( empty( $this->urls ) ) {
			return;
		}
		// Remove duplicate URLs
		$this->urls = array_unique( $this->urls );

		$api_key = $this->cloudflare_api_key;
		$zone_id = $this->cloudflare_zone_id;

		if ( ! $api_key || ! $zone_id ) {
			return;
		}

		$endpoint = "https://api.cloudflare.com/client/v4/zones/{$zone_id}/purge_cache";

		// Chunk the URLs into batches of 30
		$url_batches = array_chunk( $this->urls, 30 );

		foreach ( $url_batches as $batch ) {
			$headers = array(
				'Content-Type'  => 'application/json',
				'Authorization' => "Bearer " . $api_key,
			);

			$data = array(
				'files' => $batch,
			);

			$remote = wp_safe_remote_request( $endpoint, array(
				'method'  => 'DELETE',
				'headers' => $headers,
				'body'    => json_encode( $data ),
				'timeout' => 30, // Adjust as needed
			) );

			$status_code = wp_remote_retrieve_response_code( $remote );

			if ( $status_code != 200 ) {
				set_transient( $transient_name, $status_code, 60 * 60 * 1 );
			}
			
		}

	}

}
