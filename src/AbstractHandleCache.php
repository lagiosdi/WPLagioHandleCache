<?php

namespace Lagio\HandleCache;

use get_post;
use trailingslashit;

abstract class AbstractHandleCache {

	protected mixed $post_id = null;
	protected array $urls = array();


	/**
	 * Sets the post ID to be used for cache purging.
	 *
	 * @param int $post_id The post ID to be set.
	 */
	public function setPostId( int $post_id ): static {
		$this->post_id = $post_id;

		return $this;
	}

	public function get_purge_permalink( int $postId ): bool|string {
		$post        = get_post( $postId );
		$post_status = $post->post_status;
		if ( $post_status == 'trash' ) {
			$post->post_status = 'publish';
			$permalink         = get_permalink( $post );
			if ( mb_substr( $permalink, - 10 ) == '__trashed/' ) {
				return trailingslashit( mb_substr( $permalink, 0, - 10 ) );
			}
			$post->post_status = 'trash';
		}

		return get_permalink( $post );
	}

	public function get_amp_purge_permalink( $postId ): string {
		$permalink = $this->get_purge_permalink( $postId );

		return untrailingslashit( $permalink ) . '/amp/';
	}

	protected function setHomeUrl(): void {
		$home_url = get_home_url();
		if ( ! $home_url ) {
			return;
		}

		$this->urls[] = $home_url;
	}


	protected function setPermalink(): void {
		$post_id = $this->post_id;
		if ( ! $post_id ) {
			return;
		}

		$permalink = $this->get_purge_permalink( $post_id );
		if ( ! $permalink ) {
			return;
		}

		$this->urls[] = $permalink;
	}

	protected function setAMPPermalink(): void {
		$post_id = $this->post_id;
		if ( ! $post_id ) {
			return;
		}

		$permalink = $this->get_amp_purge_permalink( $post_id );
		if ( ! $permalink ) {
			return;
		}

		$this->urls[] = $permalink;
	}

	protected function setPostTagsUrls(): void {
		$post_id = $this->post_id;
		if ( ! $post_id ) {
			return;
		}

		$tags = get_the_tags( $post_id );
		if ( ! $tags ) {
			return;
		}

		foreach ( $tags as $tag ) {
			$tag_link = get_tag_link( $tag->term_id );
			if ( ! $tag_link ) {
				continue;
			}

			$this->urls[] = $tag_link;
		}
	}

	protected function setPostCategoriesUrls(): void {
		$post_id = $this->post_id;
		if ( ! $post_id ) {
			return;
		}

		$categories = get_the_category( $post_id );
		if ( ! $categories ) {
			return;
		}

		foreach ( $categories as $category ) {
			$cat_link = get_category_link( $category->term_id );
			if ( ! $cat_link ) {
				continue;
			}

			$this->urls[] = $cat_link;
		}
	}

	abstract public function setUrlsToRefresh(): self;

}