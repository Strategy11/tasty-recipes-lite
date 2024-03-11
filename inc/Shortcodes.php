<?php
/**
 * Registers shortcodes used by the plugin.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite;

use Tasty\Recipes\Lite\Admin\Settings\Design\Template;
use Tasty\Recipes\Lite\Admin\Settings\Settings\ShareAsale;
use Tasty\Recipes\Lite\Admin\Templates;
use TastyRecipesLiteMain;
use Tasty\Recipes\Lite\Objects\Recipe;
use WP_Post;

/**
 * Registers shortcodes used by the plugin.
 */
class Shortcodes {

	/**
	 * Quick Links shortcode name.
	 *
	 * @var string
	 */
	const QUICK_LINKS_SHORTCODE = 'tasty-recipes-quick-links';

	/**
	 * Recipe shortcode name.
	 *
	 * @var string
	 */
	const RECIPE_SHORTCODE = 'tasty-recipe';

	/**
	 * Whether we're currently doing the excerpt.
	 *
	 * @var boolean
	 */
	private static $doing_excerpt = false;

	/**
	 * Template markups.
	 *
	 * @var string[]
	 */
	private static $template_markups = array(
		'inside_ingredients' => '<!--INSIDE_INGREDIENTS-->',
	);

	/**
	 * Registers shortcodes with their callbacks.
	 */
	public static function action_init_register_shortcode() {
		add_shortcode( self::RECIPE_SHORTCODE, array( __CLASS__, 'render_tasty_recipe_shortcode' ) );
		add_shortcode( self::QUICK_LINKS_SHORTCODE, array( __CLASS__, 'render_quick_links_shortcode' ) );
	}

	/**
	 * Keeps track of when 'get_the_excerpt' is running.
	 *
	 * @param string $excerpt Existing excerpt.
	 * @return string
	 */
	public static function filter_get_the_excerpt_early( $excerpt ) {
		self::$doing_excerpt = true;
		return $excerpt;
	}

	/**
	 * Keeps track of when 'get_the_excerpt' is running.
	 *
	 * @param string $excerpt Existing excerpt.
	 * @return string
	 */
	public static function filter_get_the_excerpt_late( $excerpt ) {
		self::$doing_excerpt = false;
		return $excerpt;
	}

	/**
	 * Add "Jump to Recipe" and "Print Recipe" buttons when the post has a recipe.
	 *
	 * @param string $content Existing post content.
	 * @return string
	 */
	public static function filter_the_content_late( $content ) {

		if ( self::$doing_excerpt || ! is_singular() || is_front_page() ) {
			return $content;
		}

		$post = get_post();
		if ( ! $post ) {
			return $content;
		}

		$quick_links = self::prepended_quick_links( $post, $content );

		if ( ! empty( $quick_links ) ) {
			$html    = do_shortcode( '[' . self::QUICK_LINKS_SHORTCODE . ' links="' . $quick_links . '"]' );
			$content = $html . PHP_EOL . PHP_EOL . $content;
		}

		return $content;
	}

	/**
	 * Which quick links will be prepended to the post content.
	 *
	 * @param WP_Post $post Post object.
	 * @param string  $content Post content.
	 *
	 * @return string
	 */
	private static function prepended_quick_links( $post, $content ) {
		$recipe_ids = TastyRecipesLiteMain::get_recipe_ids_for_post( $post->ID );

		// Check if the content includes a single or double quote.
		$shown_quick_links = preg_match( '/["\']' . self::QUICK_LINKS_SHORTCODE . '/', $content ) !== 0;

		switch ( true ) {
			case empty( $recipe_ids ):
			case $shown_quick_links:
				$quick_links = array();
				break;
			default:
				$quick_links = get_option( TastyRecipesLiteMain::QUICK_LINKS_OPTION, array( 'jump', 'print' ) );
				break;
		}

		/**
		 * Filter to allow for more granular control over whether 'Jump to'
		 * should appear.
		 *
		 * @param boolean $quick_links Whether to prepend.
		 * @param integer $post_id     ID for the recipe's post.
		 * @param array   $recipe_ids  Recipe IDs within the post.
		 */
		$quick_links = apply_filters( 'tasty_recipes_should_prepend_jump_to', $quick_links, $post->ID, $recipe_ids );

		if ( empty( $quick_links ) ) {
			return '';
		}

		if ( ! Ratings::is_enabled() ) {
			$jump_key = array_search( 'rating', $quick_links, true );
			if ( false !== $jump_key ) {
				unset( $quick_links[ $jump_key ] );
			}
		}

		return implode( ',', (array) $quick_links );
	}

	/**
	 * Filters the recipe card output to enhance with any styling customizations.
	 *
	 * @param string $output  Existing card output.
	 * @param string $context Where the shortcode is being rendered.
	 * @return string
	 */
	public static function filter_tasty_recipes_recipe_card_output( $output, $context = 'frontend' ) {
		if ( false === stripos( $output, 'data-tasty-recipes-customization' ) ) {
			return $output;
		}
		$settings = TastyRecipesLiteMain::get_filtered_customization_settings();
		$output   = preg_replace_callback(
			'#<[a-z]+[^>]+data-tasty-recipes-customization=[\'"](?<options>[^\'"]+)[\'"][^>]*>#U',
			function( $matches ) use ( $settings ) {
				$element = $matches[0];
				$styles  = '';

				$important = tasty_recipes_is_print() ? '' : ' !important';

				foreach ( explode( ' ', $matches['options'] ) as $option ) {
					if ( false !== stripos( $option, '.' ) ) {
						list( $key, $css_property ) = explode( '.', $option );
						if ( in_array( $css_property, array( 'innerHTML', 'innerText' ), true ) ) {
							continue;
						}
						$key = str_replace( '-', '_', $key );
						if ( ! empty( $settings[ $key ] ) ) {
							$styles .= $css_property . ': ' . $settings[ $key ] . $important . '; ';
						}
					}
				}
				if ( empty( $styles ) ) {
					return $element;
				}

				$styles = trim( $styles );
				if ( preg_match( '#style=[\'"](?<existing>[^\'"]+)[\'"]#', $element, $inline_style ) ) {
					$element = str_replace(
						$inline_style[0],
						str_replace(
							$inline_style['existing'],
							rtrim( $inline_style['existing'], '; ' ) . '; ' . $styles,
							$inline_style[0]
						),
						$element
					);
				} else {
					$element = str_replace(
						' data-tasty-recipes-customization',
						' style="' . esc_attr( $styles ) . '" data-tasty-recipes-customization',
						$element
					);
				}
				return $element;
			},
			$output
		);

		if ( 'frontend' === $context ) {
			foreach ( array( 'footer-heading', 'footer-description' ) as $field ) {
				$output = preg_replace(
					'#<[a-z1-9]+[^>]+data-tasty-recipes-customization=[\'"][^\'"]*' . $field . '\.(innerHTML|innerText)[^\'"]*[\'"][^>]*><\/[a-z1-9]+>#Us',
					'',
					$output
				);
			}
			// Clean up containers as necessary, from inside to outside.
			foreach ( array( 'tasty-recipes-footer-copy', 'tasty-recipes-footer-content', 'tasty-recipes-entry-footer' ) as $container ) {
				$output = preg_replace(
					'#<(div|footer)[^>]+class=[\'"][^\'"]*' . $container . '[^\'"]*[\'"][^>]*>[\s\t]+</(div|footer)>#Us',
					'',
					$output
				);
			}
		}

		return $output;
	}

	/**
	 * Renders the Tasty Recipes shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function render_tasty_recipe_shortcode( $atts ) {

		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$recipe = Recipe::get_by_id( (int) $atts['id'] );
		if ( ! $recipe ) {
			return '';
		}

		// Ensures the shortcode preview has access to the full recipe data.
		$recipe_json = $recipe->to_json();

		TastyRecipesLiteMain::get_instance()->recipe_json = $recipe_json;

		// There are some dependencies on the parent post.
		$current_post = get_post();

		$wrapper_classes = array(
			'tasty-recipes',
			'tasty-recipes-' . $recipe->get_id(),
		);
		if ( tasty_recipes_is_print() ) {
			$wrapper_classes[] = 'tasty-recipes-print';
		} else {
			$wrapper_classes[] = 'tasty-recipes-display';
		}

		$image_size = 'thumbnail';
		if ( 'snap' === get_option( TastyRecipesLiteMain::TEMPLATE_OPTION, '' ) ) {
			$image_size = 'medium_large';
		}
		/**
		 * Image size used for the recipe card.
		 *
		 * @var string $image_size Image size defaults to 'thumbnail'.
		 */
		$image_size = apply_filters( 'tasty_recipes_card_image_size', $image_size );

		if ( ! empty( $recipe_json['image_sizes'][ $image_size ] ) ) {
			$wrapper_classes[] = 'tasty-recipes-has-image';
		} else {
			$wrapper_classes[] = 'tasty-recipes-no-image';
		}

		// Added by block editor 'Additional Classes' feature.
		if ( ! empty( $atts['className'] ) ) {
			$wrapper_classes = array_merge( $wrapper_classes, explode( ' ', $atts['className'] ) );
		}

		/**
		 * Modify template used in rendering recipe.
		 *
		 * @param string $template
		 */
		$template = apply_filters( 'tasty_recipes_recipe_template', 'tasty-recipes' );
		if ( ! in_array( $template, array( 'tasty-recipes', 'easyrecipe' ), true ) ) {
			return '';
		}

		$custom_design = false;
		if ( file_exists( get_stylesheet_directory() . '/tasty-recipes.php' ) ) {
			$template = get_stylesheet_directory() . '/tasty-recipes.php';
		} else {
			$custom_design   = Template::instance()->get_option_value( '' );
			$template_object = Templates::instance()->get_object_by_name( $custom_design );
			$custom_path     = $template_object->get_template_path();
			if ( $custom_design && file_exists( $custom_path ) ) {
				$template = $custom_path;
			} else {
				$template = 'recipe/' . $template;
			}
		}

		if ( 'recipe/easyrecipe' === $template ) {
			$wrapper_classes[] = 'easyrecipe';
		}

		$shareasale = ShareAsale::instance()->get_option_value();
		if ( $shareasale ) {
			$wrapper_classes[] = 'tasty-recipes-has-plug';
		}

		$before_recipe = '';
		$error         = get_post_meta( $recipe->get_id(), 'nutrifox_error', true );
		if ( $error && self::is_error_message_showable() ) {
			$before_recipe .= '<div style="border:4px solid #dc3232;padding:10px 12px;margin-top:10px;margin-bottom:10px;"><p>Nutrifox API integration failed.</p>';
			$before_recipe .= '<pre>' . wp_kses_post( $error->get_error_message() ) . '</pre>';
			$before_recipe .= '<p>Try saving the recipe again. Contact Tasty Recipes support if the error persists.</p>';
			$before_recipe .= '</div>' . PHP_EOL;
		}
		if ( $current_post ) {
			$before_recipe .= '<a class="button tasty-recipes-print-button tasty-recipes-no-print tasty-recipes-print-above-card"';
			if ( tasty_recipes_is_print() ) {
				$before_recipe .= ' onclick="window.print();event.preventDefault();"';
			}
			$before_recipe .= ' href="' . esc_url( tasty_recipes_get_print_url( $current_post->ID, $recipe->get_id() ) ) . '">' . esc_html__( 'Print', 'tasty-recipes-lite' ) . '</a>';
		}

		/**
		 * Modify output rendered before the recipe.
		 *
		 * @param string $before_recipe  Prepared output to display.
		 * @param Recipe $recipe
		 */
		$before_recipe = apply_filters( 'tasty_recipes_before_recipe', $before_recipe, $recipe );

		// Begin the recipe output.
		$ret            = $before_recipe;
		$ret           .= '<span class="tasty-recipes-jump-target" id="tasty-recipes-' . $recipe->get_id() . '-jump-target" style="display:block;padding-top:2px;margin-top:-2px;"></span>';
		$ret           .= '<div data-tr-id="' . $recipe->get_id() . '" id="tasty-recipes-' . $recipe->get_id() . '" class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '"';
		$customizations = self::get_card_container_customizations( $custom_design );
		if ( $customizations ) {
			$ret .= ' data-tasty-recipes-customization="' . esc_attr( $customizations ) . '"';
		}
		$ret .= '>' . PHP_EOL;

		$embed_data = $recipe->get_video_url_response();

		$orig_ingredients = $recipe_json['ingredients'];
		$ingredients      = $orig_ingredients;

		$ingredients = self::process_ingredients_annotate_with_spans( $ingredients );

		// Strip out this <span> so Tasty Links can apply to ingredient names.
		if ( false !== stripos( $ingredients, '<span class="nutrifox-name">' ) ) {
			$ingredients = preg_replace( '#<span class="nutrifox-name">(.+)</span>#U', '$1', $ingredients );
		}

		/** Build the button to copy ingredients to clipboard if `copy to clipboard` is turned on. */
		if ( ! is_feed() && get_option( TastyRecipesLiteMain::COPY_TO_CLIPBOARD_OPTION ) ) {
			$copy_ingredients = '<button aria-label="' . __( 'Copy ingredients to clipboard', 'tasty-recipes-lite' ) . '" class="tasty-recipes-copy-button" data-text="' . __( 'Copy ingredients', 'tasty-recipes-lite' ) . '" data-success="' . __( 'Copied!', 'tasty-recipes-lite' ) . '">' . Utils::get_contents( dirname( dirname( __FILE__ ) ) . '/assets/images/icons/icon-copy-to-clipboard.svg' ) . '</button>';
		} else {
			$copy_ingredients = false;
		}

		$recipe_author_name = '';
		if ( ! empty( $recipe_json['author_name'] ) ) {
			$link = ! empty( $atts['author_link'] ) ? $atts['author_link'] : get_option( TastyRecipesLiteMain::DEFAULT_AUTHOR_LINK_OPTION, '' );
			if ( $link ) {
				$recipe_author_name = '<a data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-author-name" href="' . esc_url( $link ) . '">' . $recipe_json['author_name'] . '</a>';
			} else {
				$recipe_author_name = '<span data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-author-name">' . $recipe_json['author_name'] . '</span>';
			}
		}

		$settings = TastyRecipesLiteMain::get_customization_settings();

		$template_vars = array(
			'recipe'                        => $recipe,
			'recipe_styles'                 => '',
			'recipe_scripts'                => '',
			'recipe_json'                   => $recipe_json,
			'recipe_title'                  => $recipe_json['title_rendered'],
			'recipe_image'                  => ! empty( $recipe_json['image_sizes'][ $image_size ] ) ? $recipe_json['image_sizes'][ $image_size ]['html'] : '',
			'recipe_rating_icons'           => '',
			'recipe_rating_label'           => '',
			'recipe_author_name'            => $recipe_author_name,
			'recipe_details'                => array(),
			'recipe_description'            => self::do_caption_shortcode( $recipe_json['description_rendered'] ),
			'recipe_ingredients'            => apply_filters( 'tasty_recipes_the_content', self::do_caption_shortcode( $ingredients ), 'ingredients' ),
			'recipe_instructions_has_video' => false,
			'recipe_convertable'            => false,
			'recipe_scalable'               => '',
			'recipe_instructions'           => DistributionMetadata::apply_instruction_step_numbers( apply_filters( 'tasty_recipes_the_content', self::do_caption_shortcode( $recipe_json['instructions'] ), 'instructions' ) ),
			'recipe_keywords'               => $recipe_json['keywords'],
			'recipe_notes'                  => $recipe_json['notes_rendered'],
			'recipe_nutrifox_id'            => ! empty( $recipe_json['nutrifox_id'] ) ? $recipe_json['nutrifox_id'] : '',
			'recipe_nutrifox_embed'         => '',
			'recipe_video_embed'            => '',
			'recipe_nutrition'              => array(),
			'recipe_hidden_nutrition'       => array(),
			'copy_ingredients'              => $copy_ingredients,
			'first_button'                  => self::get_card_button( $recipe, 'first' ),
			'second_button'                 => self::get_card_button( $recipe, 'second' ),
			'instagram_handle'              => get_option( TastyRecipesLiteMain::INSTAGRAM_HANDLE_OPTION ),
			'instagram_hashtag'             => get_option( TastyRecipesLiteMain::INSTAGRAM_HASHTAG_OPTION ),
			'footer_social_platform'        => $settings['footer_social_platform'],
			'footer_heading'                => $settings['footer_heading'],
			'footer_description'            => $settings['footer_description'],
			'atts'                          => $atts,
		);

		/**
		 * Enable responsive iframes by default, but permit disabling on a site.
		 *
		 * @param boolean $responsive_iframes Whether to enable responsive iframes.
		 */
		$responsive_iframes = apply_filters( 'tasty_recipes_enable_responsive_iframes', true );
		$responsive_styles  = '';

		if ( ! empty( $embed_data->html ) ) {
			$template_vars['recipe_video_embed'] = $responsive_iframes ? self::make_iframes_responsive(
				$embed_data->html,
				$responsive_styles
			) : $embed_data->html;
		} elseif ( ! empty( $embed_data->provider_url )
			&& 'www.adthrive.com' === wp_parse_url( $embed_data->provider_url, PHP_URL_HOST ) ) {
			// If the AdThrive plugin is active, assume the <div> will be
			// handled correctly on the frontend.
			if ( shortcode_exists( 'adthrive-in-post-video-player' ) ) {
				// Show the thumbnail as the preview in the backend.
				if ( is_admin() ) {
					$template_vars['recipe_video_embed'] = sprintf( '<img src="%s">', esc_url( $embed_data->thumbnail_url ) );
				} else {
					$template_vars['recipe_video_embed'] = sprintf( '<div class="adthrive-video-player in-post" data-video-id="%s"></div>', $embed_data->video_id );
				}
				// Fallback is to display the shortcode.
			} else {
				$template_vars['recipe_video_embed'] = $recipe->get_video_url();
			}
		}

		$oembed_fields = array(
			'recipe_description',
			'recipe_ingredients',
			'recipe_instructions',
			'recipe_notes',
		);

		/*
		 * Remove <iframe>-based videos in print.
		 */
		if ( tasty_recipes_is_print() ) {
			foreach ( $oembed_fields as $oembed_field ) {
				$template_vars[ $oembed_field ] = preg_replace(
					'#(<br[^>]*>|\n)*?<iframe[^>]*>[^<]*</iframe>(<br[^>]*>|\n)*?#',
					'',
					$template_vars[ $oembed_field ]
				);
			}
		}
		if ( ! tasty_recipes_is_print() ) {
			$template_vars['recipe_instructions_has_video'] = preg_match_all(
				'#<iframe[^>]*>[^<]*</iframe>#',
				$template_vars['recipe_instructions'],
				$matches
			);

			/*
			 * Applies video settings to each oEmbed field, as necessary.
			 */
			foreach ( $oembed_fields as $oembed_field ) {
				$field      = str_replace( 'recipe_', '', $oembed_field );
				$method     = "get_{$field}_video_settings";
				$o_settings = $recipe->{$method}();
				if ( empty( $o_settings ) ) {
					continue;
				}
				$o_settings                     = explode( ' ', $o_settings );
				$template_vars[ $oembed_field ] = preg_replace_callback(
					'#<iframe[^>]*src=["\']([^"\']+)["\'][^>]*>[^<]*</iframe>#',
					function( $matches ) use ( $o_settings ) {
						$host = wp_parse_url( $matches[1], PHP_URL_HOST );
						$url  = $matches[1];
						switch ( $host ) {
							case 'player.vimeo.com':
								$args = array(
									'autoplay'      => array(
										'autoplay'  => 1,
										'muted'     => 1,
										'autopause' => 0,
									),
									'mute'          => array( 'muted' => 1 ),
									'loop'          => array( 'loop' => 1 ),
									'hide-controls' => array( 'controls' => 0 ),
								);
								foreach ( $o_settings as $setting ) {
									if ( isset( $args[ $setting ] ) ) {
										$url = add_query_arg( $args[ $setting ], $url );
									}
								}
								$matches[0] = str_replace(
									$matches[1],
									$url,
									$matches[0]
								);
								break;
							case 'www.youtube.com':
								$args = array(
									'autoplay'      => array( 'autoplay' => 1 ),
									'mute'          => array( 'muted' => 1 ),
									'loop'          => array( 'loop' => 1 ),
									'hide-controls' => array( 'controls' => 0 ),
								);
								foreach ( $o_settings as $setting ) {
									if ( isset( $args[ $setting ] ) ) {
										$url = add_query_arg( $args[ $setting ], $url );
									}
								}
								$matches[0] = str_replace(
									$matches[1],
									$url,
									$matches[0]
								);
								break;
						}
						return $matches[0];
					},
					$template_vars[ $oembed_field ]
				);
			}
		}

		/*
		 * Make all <iframes> responsive if feature is enabled.
		 */
		if ( $responsive_iframes ) {
			foreach ( $oembed_fields as $oembed_field ) {
				if ( false === stripos( $template_vars[ $oembed_field ], '<iframe' ) ) {
					continue;
				}
				$template_vars[ $oembed_field ] = self::make_iframes_responsive(
					$template_vars[ $oembed_field ],
					$responsive_styles
				);
			}
		}

		$styles = '';
		if ( 'recipe/easyrecipe' === $template ) {
			$easyrecipe_css = Utils::get_contents( dirname( dirname( __FILE__ ) ) . '/assets/css/easyrecipe.css' );
			$easyrecipe_css = str_replace( 'assets/images/easyrecipe', plugins_url( 'assets/images/easyrecipe', dirname( __FILE__ ) ), $easyrecipe_css );
			$styles        .= PHP_EOL . PHP_EOL . $easyrecipe_css;
		}
		$styles .= self::get_styles_as_string( $custom_design );

		if ( $responsive_styles ) {
			$styles .= $responsive_styles;
		}
		$template_vars['recipe_styles'] = '<style type="text/css" style="display: none !important;">' . PHP_EOL . Utils::minify_css( $styles ) . PHP_EOL . '</style>' . PHP_EOL;

		if ( $copy_ingredients ) {
			$template_vars['recipe_scripts'] .= PHP_EOL . Utils::get_contents( dirname( dirname( __FILE__ ) ) . '/assets/js/copy-ingredients.js' );
		}

		if ( $template_vars['recipe_instructions_has_video'] ) {
			$template_vars['recipe_scripts'] .= PHP_EOL . Utils::get_contents( dirname( dirname( __FILE__ ) ) . '/assets/js/video-toggle.js' );
		}

		$total_reviews = $recipe->get_total_reviews();

		$template_vars['recipe_rating_label'] = '<span data-tasty-recipes-customization="detail-label-color.color" class="rating-label">';

		if ( $total_reviews ) {
			$average_rating                        = round( (float) $recipe->get_average_rating(), 1 );
			$template_vars['recipe_rating_label'] .= sprintf(
				// translators: Ratings from number of reviews.
				_n( '%1$s from %2$s review', '%1$s from %2$s reviews', (int) $total_reviews, 'tasty-recipes-lite' ),
				'<span class="average">' . $average_rating . '</span>',
				'<span class="count">' . (int) $total_reviews . '</span>'
			);
			$template_vars['recipe_rating_label'] .= '</span>';
			$template_vars['recipe_rating_icons']  = Ratings::get_rendered_rating( $average_rating );
		} else {
			$template_vars['recipe_rating_label'] .= __( 'No reviews', 'tasty-recipes-lite' );
			$template_vars['recipe_rating_icons']  = Ratings::get_rating_stars_html( true );
		}

		$template_vars['recipe_rating_label'] .= '</span>';

		if ( ! empty( $template_vars['recipe_author_name'] ) ) {
			$template_vars['recipe_details']['author'] = array(
				'label' => __( 'Author', 'tasty-recipes-lite' ),
				'value' => $template_vars['recipe_author_name'],
				'class' => 'author',
			);
		}

		foreach ( Recipe::get_cooking_attributes() as $attribute => $meta ) {

			if ( empty( $recipe_json[ $attribute ] ) ) {
				continue;
			}
			$value = $recipe_json[ $attribute ];

			// Use the display/localized version of the Diet.
			if ( 'diet' === $attribute ) {
				if ( isset( $meta['options'][ $value ] ) ) {
					$value = $meta['options'][ $value ];
				}
			}

			if ( 'additional_time_label' === $attribute ) {
				$label = $value;
				$value = '<span data-tasty-recipes-customization="detail-value-color.color" class="' . esc_attr( 'tasty-recipes-additional-time' ) . '">' . $recipe_json['additional_time_value'] . '</span>';
				$template_vars['recipe_details']['additional_time'] = array(
					'label' => $label,
					'value' => $value,
					'class' => 'additional-time',
				);
				continue;
			}

			// Handled above.
			if ( 'additional_time_value' === $attribute ) {
				continue;
			}

			$value = '<span data-tasty-recipes-customization="detail-value-color.color" class="' . esc_attr( 'tasty-recipes-' . str_replace( '_', '-', $attribute ) ) . '">' . $value . '</span>';
			$template_vars['recipe_details'][ $attribute ] = array(
				'label' => $meta['label'],
				'value' => $value,
				'class' => str_replace( '_', '-', $attribute ),
			);
		}

		/**
		 * Allow third-parties to modify the template variables prior to rendering.
		 *
		 * @param array $template_vars Template variables to be used.
		 * @param object $recipe Recipe object.
		 */
		$template_vars = apply_filters( 'tasty_recipes_recipe_template_vars', $template_vars, $recipe );

		self::minify_js( $template_vars['recipe_scripts'] );

		$ret .= TastyRecipesLiteMain::get_template_part( $template, $template_vars );
		$ret .= '</div>';

		if ( $shareasale ) {
			$ret .= '<div class="tasty-recipes-plug">';
			$ret .= esc_html__( 'Recipe Card powered by', 'tasty-recipes-lite' );
			$ret .= '<a href="' . esc_url( sprintf( 'https://shareasale.com/r.cfm?b=973044&u=%s&m=69860&urllink=&afftrack=trattr', $shareasale ) ) . '" target="_blank" rel="nofollow"><img data-pin-nopin="true" alt="Tasty Recipes" src="' . esc_url( plugins_url( 'assets/images/tasty-recipes-neutral.svg', dirname( __FILE__ ) ) ) . '" scale="0" height="20px"></a>';
			$ret .= '</div>';
		}
		return apply_filters( 'tasty_recipes_recipe_card_output', $ret, 'frontend', compact( 'recipe' ) );
	}

	/**
	 * Remove tabs and comments before echoing the javascript.
	 *
	 * @since 1.0.0
	 * @param string $scripts All the scripts to echo.
	 * @return void
	 */
	private static function minify_js( &$scripts ) {
		if ( empty( $scripts ) ) {
			return;
		}

		$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		if ( ! $debug ) {
			// Remove comments.
			$scripts = preg_replace( '/\/\*(.|\s)*?\*\//', '', $scripts );

			// Remove extra white space.
			$scripts = str_replace( array( "\t" ), '', $scripts );
		}
		$scripts = PHP_EOL . '<script>' . $scripts . '</script>';
	}

	/**
	 * Renders the Quick Links shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function render_quick_links_shortcode( $atts ) {

		$defaults = array(
			'links' => 'jump,print',
		);
		if ( ! empty( $atts['links'] ) ) {
			$atts['links'] = implode( '|', Admin::handle_quick_links_backward_compatibility( $atts['links'] ) );
		}
		$atts = array_merge( $defaults, (array) $atts );

		$should_prepend_jump_to = is_array( $atts['links'] ) ? $atts['links'] : explode( ',', $atts['links'] );

		$post = get_post();
		if ( ! $post ) {
			return '';
		}

		$recipe_ids = TastyRecipesLiteMain::get_recipe_ids_for_post( $post->ID );
		if ( empty( $recipe_ids ) ) {
			return '';
		}
		$recipe_id = array_shift( $recipe_ids );
		$open_new  = '';

		/**
		 * Filter to control whether the print link opens in a new window.
		 *
		 * @param boolean
		 */
		if ( apply_filters( 'tasty_recipes_print_link_open_new', false ) ) {
			$open_new = ' target="_blank"';
		}

		if ( ! empty( $should_prepend_jump_to ) && is_array( $should_prepend_jump_to ) ) {
			$should_prepend_jump_to = array_map( 'trim', $should_prepend_jump_to );
		}

		$links = array();
		if ( in_array( 'jump', $should_prepend_jump_to, true ) ) {
			$links['jump'] = '<a class="tasty-recipes-jump-link" href="#tasty-recipes-' . $recipe_id . '-jump-target">' . __( 'Jump to Recipe', 'tasty-recipes-lite' ) . '</a>';
		}
		if ( in_array( 'print', $should_prepend_jump_to, true ) ) {
			$links['print'] = '<a class="tasty-recipes-print-link" href="' . esc_url( tasty_recipes_get_print_url( $post->ID, $recipe_id ) ) . '"' . $open_new . '>' . __( 'Print Recipe', 'tasty-recipes-lite' ) . '</a>';
		}
		/**
		 * Filter to modify the links used in Quick Links.
		 *
		 * @param array $links Existing links.
		 */
		$links = apply_filters(
			'tasty_recipes_quick_links',
			$links,
			$post,
			$recipe_id,
			$should_prepend_jump_to
		);
		$links = implode( '<span>&middot;</span>', $links );
		$html  = <<<EOT
<style style="display: none !important;">
.tasty-recipes-quick-links { text-align:center; }
.tasty-recipes-quick-links a { padding: 0.5rem; }
</style>
<div class="tasty-recipes-quick-links">
{$links}
</div>
EOT;
		return $html;
	}

	/**
	 * Get the shortcode for a given recipe.
	 *
	 * @param Recipe $recipe Recipe instance.
	 */
	public static function get_shortcode_for_recipe( Recipe $recipe ) {
		return '[' . self::RECIPE_SHORTCODE . ' id="' . $recipe->get_id() . '"]';
	}

	/**
	 * Gets any wrapper customizations for the card.
	 *
	 * @param string $custom_design Design being rendered.
	 * @return string
	 */
	public static function get_card_container_customizations( $custom_design ) {
		switch ( $custom_design ) {
			case 'bold':
				return 'primary-color.border-color';
			case 'fresh':
				return 'primary-color.background-color';
			case 'simple':
				return 'primary-color.background-color secondary-color.border-color';
			case 'modern-compact':
				return 'primary-color.background-color secondary-color.border-color';
			case 'elegant':
				return 'primary-color.background-color primary-color.outline-color';
		}
		return '';
	}

	/**
	 * Gets a given card button.
	 *
	 * @param object $recipe   Recipe object.
	 * @param string $position Button to generate.
	 * @param string $template Name of the template.
	 * @return string
	 */
	public static function get_card_button( $recipe, $position, $template = null ) {
		$settings = TastyRecipesLiteMain::get_card_button_settings( $template );
		if ( empty( $settings[ $position ] ) ) {
			return '';
		}
		$customization = '';
		if ( null === $template ) {
			$template = get_option( TastyRecipesLiteMain::TEMPLATE_OPTION, '' );
		}
		if ( in_array( $template, array( 'bold', 'fresh' ), true ) ) {
			$customization = 'button-color.background button-text-color.color';
		} else {
			$customization = 'button-color.border-color button-color.background button-text-color.color';
		}
		return TastyRecipesLiteMain::get_template_part(
			'buttons/' . $settings[ $position ],
			array(
				'recipe'        => $recipe,
				'customization' => $customization,
			)
		);
	}

	/**
	 * Gets the styles to inject into the recipe card.
	 *
	 * @param string $custom_design Design being used.
	 * @return string
	 */
	public static function get_styles_as_string( $custom_design ) {
		$styles = Utils::get_contents( dirname( dirname( __FILE__ ) ) . '/assets/dist/recipe.css' );
		if ( $custom_design ) {
			$template_object = Templates::instance()->get_object_by_name( $custom_design );
			$custom_path     = $template_object->get_style_path();
			if ( file_exists( $custom_path ) ) {
				$styles .= PHP_EOL . PHP_EOL . Utils::get_contents( $custom_path );
			}
		}
		/**
		 * Allow third-parties to more easily inject their own styles.
		 */
		$custom_styles_path = apply_filters( 'tasty_recipes_custom_css', get_stylesheet_directory() . '/tasty-recipes.css' );
		if ( file_exists( $custom_styles_path ) ) {
			$styles .= PHP_EOL . PHP_EOL . Utils::get_contents( $custom_styles_path );
		}
		return $styles;
	}

	/**
	 * Process ingredients to annotate <li> with structured data.
	 *
	 * @param string $ingredients Existing ingredients string.
	 * @return string
	 */
	public static function process_ingredients_annotate_with_spans( $ingredients ) {
		// Prioritize list items.
		$ingredients = preg_replace_callback(
			'#(<li[^\>]*>)(.*)(<\/li>)#U',
			function( $m ) {
				if ( UnitAmountParser::string_has_non_numeric_amount( $m[2] ) ) {
					$m[1] = str_replace( '<li', '<li data-has-non-numeric-amount', $m[1] );
				}
				return $m[1] . UnitAmountParser::annotate_string_with_spans( $m[2] ) . $m[3];
			},
			$ingredients,
			-1,
			$count
		);
		if ( $count ) {
			return $ingredients;
		}
		// Fall back when there weren't list items.
		$bits = explode( PHP_EOL, $ingredients );
		foreach ( $bits as $i => $bit ) {
			if ( DistributionMetadata::get_heading( $bit ) ) {
				continue;
			}
			$start = '';
			$end   = '';
			// String starts with some HTML element.
			if ( preg_match( '#^(<p[^>]*>)(.+)(</p>)$#', $bit, $matches ) ) {
				$start = $matches[1];
				$end   = $matches[3];
				$bit   = $matches[2];
			}
			if ( UnitAmountParser::string_has_non_numeric_amount( $bit ) ) {
				$bits[ $i ] = $start . '<span data-has-non-numeric-amount>' . $bit . '</span>' . $end;
				continue;
			}
			$bits[ $i ] = $start . UnitAmountParser::annotate_string_with_spans( $bit ) . $end;
		}
		return implode( PHP_EOL, $bits );
	}

	/**
	 * Do the caption shortcode when applying content filters.
	 *
	 * @param string $content Content to render.
	 * @return string
	 */
	private static function do_caption_shortcode( $content ) {
		global $shortcode_tags;
		if ( ! has_shortcode( $content, 'caption' )
			&& ! isset( $shortcode_tags['caption'] ) ) {
			return $content;
		}
		$backup_tags = $shortcode_tags;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$shortcode_tags = array(
			'caption' => $backup_tags['caption'],
		);
		$content        = do_shortcode( $content );
		$shortcode_tags = $backup_tags; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		return $content;
	}

	/**
	 * Makes all iframes within the content responsive.
	 *
	 * @param string $content Content to process.
	 * @param string $styles  Existing responsive iframe styles.
	 * @return array
	 */
	private static function make_iframes_responsive( $content, &$styles ) {
		// Responsive iframes using CSS when width and height are in pixels.
		// See https://blog.theodo.fr/2018/01/responsive-iframes-css-trick/.
		if ( preg_match_all(
			'#<iframe([^>]+)>.*</iframe>#',
			$content,
			$matches
		) ) {
			foreach ( $matches[0] as $i => $original_iframe ) {
				$iframe = $original_iframe;
				$attrs  = shortcode_parse_atts( $matches[1][ $i ] );
				if ( empty( $attrs['height'] )
					|| empty( $attrs['width'] )
					|| empty( $attrs['src'] ) ) {
					continue;
				}
				// Remove the existing width and height attributes.
				$iframe = preg_replace( '#(width|height)=[\'"][^\'"]+[\'"]#', '', $iframe );
				// First try to inject the 'fitvidsignore' class into the existing classes.
				$iframe = preg_replace( '#(<iframe[^>]+class=")([^"]+)#', '$1fitvidsignore $2', $iframe, -1, $count );
				// If no replacements were found, then the <iframe> needs a class.
				if ( ! $count ) {
					$iframe = str_replace( '<iframe ', '<iframe class="fitvidsignore" ', $iframe );
				}
				$unique_class       = 'tasty-recipe-responsive-iframe-container-' . substr( md5( $matches[1][ $i ] ), 0, 8 );
				$padding_percentage = round( ( ( $attrs['height'] / $attrs['width'] ) * 100 ), 2 );
				$iframe             = '<div class="' . esc_attr( 'tasty-recipe-responsive-iframe-container ' . $unique_class ) . '">' . $iframe . '</div>';
				$content            = str_replace(
					$original_iframe,
					$iframe,
					$content
				);
				$styles            .= PHP_EOL . '.' . $unique_class . ' { position: relative; overflow: hidden; padding-top: ' . $padding_percentage . '%; }';
				$styles            .= PHP_EOL . '.' . $unique_class . ' iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }';
			}
		};
		return $content;
	}

	/**
	 * Whether or not an error message should be shown.
	 *
	 * @return boolean
	 */
	public static function is_error_message_showable() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Add inside ingredients template markup.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Ingredients content.
	 * @param string $type Type returned from the filter.
	 *
	 * @return string
	 */
	public static function add_inside_ingredients( $content, $type = null ) {
		if ( 'ingredients' === $type ) {
			$content .= self::$template_markups['inside_ingredients'];
		}

		return $content;
	}

	/**
	 * The hook is present, so we don't need the fallback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function run_after_ingredients() {
		remove_filter( 'tasty_recipes_recipe_card_output', array( self::class, 'inside_ingredients_handler' ) );
	}

	/**
	 * Force the tasty_recipes_card_after_ingredients hook to run if
	 * it's missing from the template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Filter content.
	 * @param string $context Where the shortcode is being rendered.
	 * @param array  $atts Includes the recipe object.
	 *
	 * @return string
	 */
	public static function inside_ingredients_handler( $content, $context, $atts = array() ) {
		if ( ! stripos( $content, self::$template_markups['inside_ingredients'] ) ) {
			return $content;
		}

		$content = str_ireplace(
			self::$template_markups['inside_ingredients'],
			self::add_after_ingredients_hook( $atts ),
			$content
		);
		return $content;
	}

	/**
	 * Force the hook missing from the template.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Includes the recipe object.
	 *
	 * @return string
	 */
	private static function add_after_ingredients_hook( $atts ) {
		// This hook should only run when it comes from the template.
		remove_action( 'tasty_recipes_card_after_ingredients', array( 'Tasty_Recipes\Shortcodes', 'run_after_ingredients' ) );

		$recipe = empty( $atts['recipe'] ) ? false : $atts['recipe'];
		ob_start();
		do_action( 'tasty_recipes_card_after_ingredients', $recipe );
		return ob_get_clean();
	}

}
