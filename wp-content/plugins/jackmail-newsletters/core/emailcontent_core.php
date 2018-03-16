<?php


class Jackmail_Emailcontent_Core extends Jackmail_Emailcontent_Common_Core {

	protected function get_images() {
		$wp_paths          = wp_upload_dir();
		$baseurl           = $wp_paths['baseurl'] . '/';
		$query_images_args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => - 1,
		);
		$query_images      = new WP_Query( $query_images_args );
		$json              = array(
			'libraryLabel' => '',
			'filename'     => '',
			'libraries'    => array(),
			'images'       => array()
		);
		$years             = array();
		$months            = array();
		foreach ( $query_images->posts as $image ) {
			
			$dir      = str_replace( $baseurl, '', wp_get_attachment_image_src( $image->ID, 'large' )[0] );
			$year     = substr( $dir, 0, 4 );
			$month    = substr( $dir, 5, 2 );
			$filename = substr( $dir, 8 );
			if ( ! in_array( $year, $years ) ) {
				$years[]             = $year;
				$json['libraries'][] = array(
					'libraryLabel' => $year,
					'filename'     => $year,
					'libraries'    => array(),
					'images'       => array()
				);
			}
			$year_key  = - 1;
			$month_key = - 1;
			foreach ( $json['libraries'] as $key => $library ) {
				if ( $library['libraryLabel'] === $year ) {
					$year_key = $key;
					break;
				}
			}
			if ( ! in_array( $month, $months ) ) {
				$months[]                                      = $month;
				$json['libraries'][ $year_key ]['libraries'][] = array(
					'libraryLabel' => $month,
					'filename'     => $month,
					'libraries'    => array(),
					'images'       => array()
				);
			}
			foreach ( $json['libraries'][ $year_key ]['libraries'] as $key => $library ) {
				if ( $library['libraryLabel'] === $month ) {
					$month_key = $key;
					break;
				}
			}
			if ( $year_key !== - 1 && $month_key !== - 1 ) {
				$json['libraries'][ $year_key ]['libraries'][ $month_key ]['images'][] = array(
					'name'     => $filename,
					'filename' => $filename
				);
			}
		}
		if ( count( $json['libraries'] ) === 0 ) {
			$year                = date( 'Y' );
			$month               = date( 'm' );
			$json['libraries'][] = array(
				'libraryLabel' => $year,
				'filename'     => $year,
				'libraries'    => array(
					array(
						'libraryLabel' => $month,
						'filename'     => $month,
						'libraries'    => array(),
						'images'       => array()
					)
				),
				'images'       => array()
			);
		}
		return $json;
	}

	protected function get_post_categories() {
		$json       = array();
		$args       = array(
			'hide_empty' => false
		);
		$categories = get_categories( $args );
		foreach ( $categories as $category ) {
			if ( isset( $category->term_id, $category->name ) ) {
				$json[] = array(
					'id'    => strval( $category->term_id ),
					'label' => $category->name
				);
			}
		}
		return $json;
	}

	protected function get_posts( $title, Array $categories_array ) {
		$args = array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_password' => '',
			's'             => $title,
			'category'      => implode( ',', $categories_array ),
			'numberposts'   => 50
		);
		return $this->get_posts_or_pages_or_custom_posts( $args );
	}

	protected function get_post_or_page_or_custom_post_full_content( $post_id ) {
		$json = array(
			'id'          => '',
			'title'       => '',
			'description' => '',
			'link'        => '',
			'imageUrl'    => ''
		);
		$post = get_post( $post_id );
		if ( isset( $post->ID, $post->post_type, $post->post_title,
			$post->post_excerpt, $post->post_content, $post->guid ) ) {
			$json = $this->get_post_or_page_or_custom_post_data(
				$post->ID, $post->post_type, $post->post_title,
				$post->post_excerpt, $post->post_content, true, $post->guid
			);
		}
		return $json;
	}

	protected function get_pages( $title ) {
		$args = array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_password' => '',
			's'             => $title,
			'numberposts'   => 50
		);
		return $this->get_posts_or_pages_or_custom_posts( $args );
	}

	private function get_posts_or_pages_or_custom_posts( $args ) {
		$json  = array();
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			if ( isset( $post->ID, $post->post_type, $post->post_title, $post->post_excerpt, $post->post_content, $post->guid ) ) {
				$json[] = $this->get_post_or_page_or_custom_post_data(
					$post->ID, $post->post_type, $post->post_title, do_shortcode( $post->post_excerpt ),
					do_shortcode( $post->post_content ), false, $post->guid
				);
			}
		}
		return $json;
	}

	protected function get_woocommerce_product_categories() {
		$json = array();
		if ( $this->core->get_woocommerce_actived() ) {
			$args       = array(
				'hide_empty' => false,
				'taxonomy'   => 'product_cat'
			);
			$categories = get_categories( $args );
			foreach ( $categories as $category ) {
				if ( isset( $category->term_id, $category->name ) ) {
					$json[] = array(
						'id'    => strval( $category->term_id ),
						'label' => $category->name
					);
				}
			}
		}
		return $json;
	}

	protected function get_products_woocommerce( $title, $categories ) {
		$json = array();
		if ( $this->core->get_woocommerce_actived() ) {
			$products = $this->get_woocommerce_products_selection( $categories, 50, $title, '', '' );
			foreach ( $products as $product ) {
				if ( isset( $product['id'], $product['post_title'], $product['post_content'], $product['post_price'] ) ) {
					$json[] = $this->get_woocommerce_product_data(
						$product['id'], $product['post_title'], $product['post_content'],
						$product['post_price'], false
					);
				}
			}
		}
		return $json;
	}

	protected function get_woocommerce_product_full_content( $post_id ) {
		$json = array();
		if ( $this->core->get_woocommerce_actived() ) {
			$args     = array(
				'status'  => array( 'publish' ),
				'type'    => array( 'simple' ),
				'orderby' => 'name',
				'order'   => 'ASC',
				'limit'   => 1,
				'include' => array( $post_id )
			);
			$products = $this->get_woocommerce_products( $args );
			foreach ( $products as $product ) {
				if ( isset( $product['id'], $product['post_title'], $product['post_content'], $product['post_price'] ) ) {
					$json = $this->get_woocommerce_product_data(
						$product['id'], $product['post_title'], $product['post_content'],
						$product['post_price'], true
					);
				}
			}

		}
		return $json;
	}

	protected function get_custom_posts_categories() {
		return $this->core->get_custom_posts_categories_json();
	}

	protected function get_custom_posts( $title, $post_type ) {
		$post_status = 'publish';
		if ( $post_type === 'attachment' ) {
			$post_status = 'inherit';
		}
		$args = array(
			'post_type'     => $post_type,
			'post_status'   => $post_status,
			'post_password' => '',
			's'             => $title,
			'numberposts'   => 50
		);
		return $this->get_posts_or_pages_or_custom_posts( $args );
	}

}