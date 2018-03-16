<?php


class Jackmail_Emailcontent_Common_Core {

	protected function get_woocommerce_products_selection( $categories, $nb_posts, $title, $minimal_date_gmt, $maximal_date_gmt ) {
		$products_data = array();
		if ( function_exists( 'wc_get_products' ) ) {
			$data = $this->get_woocommerce_products_selection_posts_data( $categories, $nb_posts, $title, $minimal_date_gmt, $maximal_date_gmt, '' );
			if ( isset( $data['posts_ids'], $data['min_date'], $data['max_date'], $data['categories_slug'] ) ) {
				$posts_ids       = $data['posts_ids'];
				$min_date        = $data['min_date'];
				$max_date        = $data['max_date'];
				$categories_slug = $data['categories_slug'];
				if ( count( $posts_ids ) > 0 ) {
					$args = array(
						'status'   => array( 'publish' ),
						'type'     => array( 'simple' ),
						'orderby'  => 'post_date_gmt',
						'order'    => 'DESC',
						'category' => $categories_slug,
						'include'  => $posts_ids
					);
					if ( $nb_posts !== '' ) {
						$args['limit'] = $nb_posts;
					}
					if ( $min_date || $max_date ) {
						$args['date_query'] = array(
							'column'    => 'post_date_gmt',
							'inclusive' => true
						);
						if ( $min_date ) {
							$args['date_query']['after'] = $minimal_date_gmt;
						}
						if ( $max_date ) {
							$args['date_query']['before'] = $maximal_date_gmt;
						}
					}
					$products_data = $this->get_woocommerce_products( $args );
				}
			}
		}
		return $products_data;
	}

	protected function get_woocommerce_products_selection_posts_data( $categories, $nb_posts, $title, $minimal_date_gmt, $maximal_date_gmt, $post_id ) {
		$args             = array(
			'post_type'     => 'product',
			'post_status'   => 'publish',
			'post_password' => '',
			'numberposts'   => 10000,
			'tax_query'     => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'simple'
				),
			)
		);
		$categories_slug  = array();
		$categories_array = $this->core->explode_data( $categories );
		if ( count( $categories_array ) > 0 ) {
			$args_categories = array();
			foreach ( $categories_array as $category ) {
				$args_categories[] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $category
				);
			}
			if ( count( $args_categories ) > 0 ) {
				$args['tax_query']['relation'] = 'AND';
				$args['tax_query'][]           = $args_categories;
				if ( count( $args_categories ) > 1 ) {
					$args['tax_query'][1]['relation'] = 'OR';
				}
			}
			$categories_args = array(
				'hide_empty' => false,
				'taxonomy'   => 'product_cat',
				'include'    => $categories_array
			);
			$categories_data = get_categories( $categories_args );
			foreach ( $categories_data as $category ) {
				$categories_slug[] = $category->slug;
			}
		}
		if ( $title !== '' ) {
			$args['s'] = $title;
		}
		if ( $nb_posts !== '' ) {
			$args['numberposts'] = $nb_posts;
		}
		$min_date = false;
		if ( $minimal_date_gmt !== '' && $this->core->str_len( $minimal_date_gmt ) === 19 ) {
			$min_date = true;
		}
		$max_date = true;
		if ( $maximal_date_gmt !== '' && $this->core->str_len( $maximal_date_gmt ) === 19 ) {
			$max_date = true;
		}
		if ( $min_date || $max_date ) {
			$args['date_query'] = array(
				'column'    => 'post_date_gmt',
				'inclusive' => true
			);
			if ( $min_date ) {
				$args['date_query']['after'] = $minimal_date_gmt;
			}
			if ( $max_date ) {
				if ( $min_date ) {
					$maximal_date_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $maximal_date_gmt ) - 1 );
				}
				$args['date_query']['before'] = $maximal_date_gmt;
			}
		}
		if ( $post_id !== '' ) {
			$args['numberposts'] = 1;
			$args['include']     = $post_id;
		}
		$posts     = get_posts( $args );
		$posts_ids = array();
		foreach ( $posts as $post ) {
			$posts_ids[] = $post->ID;
		}
		return array(
			'posts_ids'       => $posts_ids,
			'min_date'        => $min_date,
			'max_date'        => $max_date,
			'categories_slug' => $categories_slug
		);
	}

	protected function get_woocommerce_products( $args ) {
		$products_data = array();
		if ( function_exists( 'wc_get_products' ) ) {
			$products = wc_get_products( $args );
			foreach ( $products as $product ) {
				if ( method_exists( $product, 'get_id' ) && method_exists( $product, 'get_type' ) && method_exists( $product, 'get_name' ) && method_exists( $product, 'get_description' ) && method_exists( $product, 'get_price' ) && method_exists( $product, 'get_regular_price' ) ) {
					$post_price = '';
					if ( $product->get_type() === 'simple' ) {
						$price         = $product->get_price();
						$regular_price = $product->get_regular_price();
						if ( $regular_price !== '' && $price !== '' ) {
							if ( function_exists( 'wc_price' ) ) {
								if ( $price === (string) floatval( $price ) && $regular_price === (string) floatval( $regular_price ) ) {
									if ( $price !== $regular_price ) {
										$display_price = '<s>' . strip_tags( wc_price( $regular_price ) ) . '</s> ' . strip_tags( wc_price( $price ) );
									} else {
										$display_price = strip_tags( wc_price( $price ) );
									}
									$post_price = '<b>' . $display_price . '</b>';
								}
							}
						}
					}
					$products_data[] = array(
						'id'           => $product->get_id(),
						'post_title'   => $product->get_name(),
						'post_content' => $product->get_description(),
						'post_price'   => $post_price
					);
				}
			}
		}
		return $products_data;
	}

	protected function get_woocommerce_product_data( $id, $title, $content, $price, $full_description ) {
		$data = $this->get_post_or_page_or_custom_post_data( $id, 'product', $title, '', $content, $full_description, '' );
		if ( $price !== '' ) {
			$data['description'] .= '<br/>' . $price;
		}
		return $data;
	}

	private function get_attachment_post_data( $id, $title, $imageUrl ) {
		return array(
			'id'          => strval( $id ),
			'title'       => $this->core->htmlentitiesencode( $title ),
			'description' => '',
			'link'        => $imageUrl,
			'imageUrl'    => $imageUrl
		);
	}

	protected function get_post_or_page_or_custom_post_data( $id, $type, $title, $excerpt, $content, $full_description, $guid ) {
		if ( $type === 'attachment' ) {
			return $this->get_attachment_post_data( $id, $title, $guid );
		}
		if ( $excerpt !== '' ) {
			$description = $excerpt;
		} else {
			$description = $content;
			$description = str_replace( '</p>', ' </p>', $description );
			$description = str_replace( '</div>', ' </div>', $description );
			$description = preg_replace( '/\s\s+/', ' ', $description );
			$description = wp_trim_words( $description, 15, '...' );
		}
		if ( $full_description ) {
			$description = $content;
			$description = str_replace( '</p>', ' </p>', $description );
			$description = str_replace( '</div>', ' </div>', $description );
			$description = preg_replace( '/\s\s+/', ' ', $description );
			$description = wp_trim_words( $description, 250, '...' );
			if ( class_exists( 'DOMDocument' ) ) {
				$description = strip_shortcodes( $content );
				$html        = new DOMDocument();
				@$html->loadHTML( ( '<?xml encoding="utf-8"?>' . $description ) );
				$removeTags = array(
					'img',
					'script',
					'link',
					'audio',
					'video',
					'canvas',
					'input',
					'textarea',
					'select',
					'table',
					'thead',
					'tbody',
					'tfoot',
					'tr',
					'th',
					'td',
					'caption',
					'form',
					'fieldset'
				);
				foreach ( $removeTags as $tag ) {
					$elements = $html->getElementsByTagName( $tag );
					for ( $i = $elements->length - 1; $i >= 0; $i -- ) {
						$node = $elements->item( $i );
						$node->parentNode->removeChild( $node );
					}
				}
				$remove_attributes = array( 'style' );
				foreach ( $remove_attributes as $attribute ) {
					foreach ( $html->getElementsByTagName( '*' ) as $element ) {
						if ( $element->getAttribute( $attribute ) ) {
							$element->removeAttribute( $attribute );
						}
					}
				}
				foreach ( $html->getElementsByTagName( '*' ) as $element ) {
					$element_attributes = $element->attributes;
					while ( $element_attributes->length ) {
						$element->removeAttribute( $element_attributes->item( 0 )->name );
					}
				}
				$replace_attributes = array(
					array(
						'from' => array( 'abbr', 'blockquote', 'cite', 'q', 'dfn', 'dl', 'dt', 'dd', 'legend' ),
						'to'   => 'i'
					),
					array(
						'from' => array( 'hr' ),
						'to'   => 'br'
					),
					array(
						'from' => array( 'div', 'figure', 'figcaption', 'source', 'address', 'kbd', 'pre', 'time' ),
						'to'   => 'p'
					),
					array(
						'from' => array( 'sup', 'sub' ),
						'to'   => 'p'
					),
					array(
						'from' => array(
							'font',
							'base',
							'center',
							's',
							'u',
							'align',
							'size',
							'color',
							'border',
							'background',
							'bgcolor',
							'border',
							'face',
							'target'
						),
						'to'   => 'p'
					),
					array(
						'from' => array( 'label', 'nav', 'section', 'header', 'footer', 'article', 'aside' ),
						'to'   => 'p'
					),
					array(
						'from' => array( 'strong' ),
						'to'   => 'b'
					),
					array(
						'from' => array( 'small' ),
						'to'   => 'span'
					),
					array(
						'from' => array( 'del' ),
						'to'   => 'stroke'
					)
					
				);
				foreach ( $replace_attributes as $attributes ) {
					foreach ( $attributes['from'] as $attribute ) {
						$elements = $html->getElementsByTagName( $attribute );
						for ( $i = $elements->length - 1; $i >= 0; $i -- ) {
							$nodePre = $elements->item( $i );
							$nodeDiv = $html->createElement( $attributes['to'], $nodePre->nodeValue );
							$nodePre->parentNode->replaceChild( $nodeDiv, $nodePre );
						}
					}
				}
				$removeEmptyTags = array( 'p' );
				foreach ( $removeEmptyTags as $tag ) {
					$elements = $html->getElementsByTagName( $tag );
					for ( $i = $elements->length - 1; $i >= 0; $i -- ) {
						$node = $elements->item( $i );
						if ( $node->textContent === '' ) {
							$node->parentNode->removeChild( $node );
						}
					}
				}
				$description = preg_replace( '~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', ( $html->saveHtml() ) );
				$description = str_replace( '<?xml encoding="utf-8"?>', '', $description );
				$description = str_replace( "\r", '', $description );
				$description = str_replace( "\n", '<br/>', $description );
			}
		}
		$image = get_the_post_thumbnail( $id );
		if ( $image !== '' ) {
			$begin  = strpos( $image, 'src="' ) + 5;
			$substr = substr( $image, $begin );
			$end    = strpos( $substr, '"' );
			$image  = substr( $image, $begin, $end );
		}
		return array(
			'id'          => strval( $id ),
			'title'       => $this->core->htmlentitiesencode( $title ),
			'description' => $this->core->htmlentitiesencode( $description ),
			'link'        => get_permalink( $id ),
			'imageUrl'    => $image 
		);
	}

}