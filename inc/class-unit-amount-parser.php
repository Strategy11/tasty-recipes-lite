<?php
/**
 * Parses units and amounts in ingredient strings.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite;

/**
 * Parses units and amounts in ingredient strings.
 */
class Unit_Amount_Parser {

	/**
	 * Regex used to match amounts.
	 *
	 * @var string
	 */
	private static $amounts_regex = '(?<amount>((\d+/\d+)|(\d+⁄\d+)|(one half of an?|half of an?|half an?|one|two|three|four|five|six|seven|eight|nine|ten)|(¼|½|¾|⅐|⅑|⅒|⅓|⅔|⅕|⅖|⅗|⅘|⅙|⅚|⅛|⅜|⅝|⅞)|[\d]+[\.\d]*((\s\d+/\d+)|\s?(¼|½|¾|⅐|⅑|⅒|⅓|⅔|⅕|⅖|⅗|⅘|⅙|⅚|⅛|⅜|⅝|⅞))?))';

	/**
	 * Regex to ignore matches inside of HTML.
	 *
	 * @var string
	 */
	private static $ignore_html_regex = '(?!([^<]+)?>)';

	/**
	 * All potential units to be matched.
	 *
	 * @var array
	 */
	private static $match_units = array(
		'cups',
		'cup',
		'ounces',
		'ounce',
		'oz',
		'tablespoons',
		'tablespoon',
		'tbsp',
		'teaspoons',
		'teaspoon',
		'tsp',
		'pints',
		'pint',
		'pt',
		'quarts',
		'quart',
		'qt',
		'gallons',
		'gallon',
		'gal',
		'grams',
		'gram',
		'g',
	);

	/**
	 * Regex to match the first two words in a string.
	 *
	 * @var string
	 */
	private static $word_first_regex = '(?<first_word>[a-zA-Z]+[\s])?(?<second_word>[a-zA-Z]+[\s])?';

	/**
	 * Annotates a string with its units and amounts.
	 *
	 * @param string $string Existing ingredient string.
	 * @return string
	 */
	public static function annotate_string_with_spans( $string ) {

		if ( false !== stripos( $string, 'nutrifox-quantity' ) ) {
			return $string;
		}

		$string = str_replace( '&nbsp;', ' ', $string );
		// Empty HTML element.
		$string = preg_replace( '#<[a-z]+></[a-z]+>#i', '', $string );
		$start  = '';
		// String starts with some HTML element.
		if ( preg_match( '#^<(strong|span|em|b|i|p)[^>]*>#', $string, $matches ) ) {
			$start  = $matches[0];
			$string = mb_substr( $string, mb_strlen( $start, 'UTF-8' ), null, 'UTF-8' );
		}

		$new_string = self::process_primary_amount( $string );
		// When a primary ingredient was found, it's worth looking for a secondary ingredient.
		// e.g. "4 garlic cloves, minced or 2 tbsp of minced garlic".
		if ( $new_string !== $string ) {
			$new_string = self::process_secondary_amount( $new_string );
		}

		return $start . $new_string;
	}

	/**
	 * Whether or not a string has a non-numeric amount.
	 *
	 * @param string $string Existing ingredient string.
	 * @return boolean
	 */
	public static function string_has_non_numeric_amount( $string ) {
		$amounts = array(
			'sprinkle',
			'pinch',
			'dash',
			'squeeze',
			'handful',
			'sprig',
			'clove',
			'knob',
			'spoonful',
			'drizzle',
			'swish',
			'shake',
		);
		$regex   = '#^(a\s)?([a-zA-Z]+[\s])?(' . implode( '|', $amounts ) . ')s?\sof#';
		if ( preg_match( $regex, $string ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Annotates the primary amount in a string.
	 *
	 * The primary amount is always at the beginning of the string.
	 *
	 * @param string $string Existing string.
	 * @return string
	 */
	private static function process_primary_amount( $string ) {
		// Prioritize an ingredient with explicit amount and unit.
		// e.g. "1 tbsp olive oil".
		$match_regex = '#^' . self::$word_first_regex . '(?<original>(' . self::$amounts_regex . ')\s?(?<unit>' . implode( '|', self::$match_units ) . '))(?!\w)#i';
		$string      = preg_replace_callback(
			$match_regex,
			function( $matches ) {
				return $matches['first_word'] . $matches['second_word'] . '<span data-amount="' . Utils::process_amount_into_float( $matches['amount'] ) . '" data-unit="' . strtolower( Utils::make_singular( $matches['unit'] ) ) . '">' . $matches['original'] . '</span>';
			},
			$string,
			-1,
			$count
		);
		if ( $count ) {
			return $string;
		}

		// Only found an amount at the beginning of the ingredient.
		// e.g. "2 stalks celery, finely chopped".
		// e.g. "1-2 carrots, diced".
		$second_amounts_regex = str_replace( '?<amount>', '?<second_amount>', self::$amounts_regex );
		$match_regex          = '#^' . self::$word_first_regex . '(' . self::$amounts_regex . ')((?<separator>\s?(\-|\–|to)\s?)(' . $second_amounts_regex . '))?(?<trailing>\s)?(?<remaining>.+)#';
		$string               = preg_replace_callback(
			$match_regex,
			function( $matches ) {
				// Ignore when the matched quantity is a percent.
				// e.g. 'Chosen Foods 100% Pure Avocado Oil, for drizzling'.
				if ( isset( $matches['remaining'] ) && '%' === $matches['remaining'][0] ) {
					return $matches[0];
				}
				$match = $matches['first_word'] . $matches['second_word'] . '<span data-amount="' . Utils::process_amount_into_float( $matches['amount'] ) . '">' . $matches['amount'] . '</span>';
				// If a second amount was found too.
				if ( isset( $matches['second_amount'] ) && '' !== $matches['second_amount'] ) {
					$match .= $matches['separator'] . '<span data-amount="' . Utils::process_amount_into_float( $matches['second_amount'] ) . '">' . $matches['second_amount'] . '</span>';
				}
				if ( isset( $matches['trailing'] ) ) {
					$match .= $matches['trailing'];
				}
				if ( isset( $matches['remaining'] ) ) {
					$match .= $matches['remaining'];
				}
				return $match;
			},
			$string,
			-1,
			$count
		);
		if ( $count ) {
			return $string;
		}

		// Pure number.
		$string = preg_replace_callback(
			'#^(?<amount>[\d]+)$#',
			function( $matches ) {
				return '<span data-amount="' . Utils::process_amount_into_float( $matches['amount'] ) . '">' . $matches['amount'] . '</span>';
			},
			$string,
			-1,
			$count
		);
		if ( $count ) {
			return $string;
		}

		// Nothing found that we could process.
		return $string;
	}

	/**
	 * Annotates the secondary amount in a string.
	 *
	 * The secondary amount may be further in the string, only after some leading indicator.
	 *
	 * @param string $string Existing string.
	 * @return string
	 */
	private static function process_secondary_amount( $string ) {

		$amounts_regex     = self::$amounts_regex;
		$match_units       = self::$match_units;
		$ignore_html_regex = self::$ignore_html_regex;

		// Look for secondary amounts within parens.
		// Parens need to be at least three words from beginning of string.
		// Unless the first match had a unit in it.
		$minimum = false !== stripos( $string, 'data-unit=' ) ? 0 : 3;

		list( $first_string, $second_string ) = self::split_string_into_min_substrings( $string, $minimum );
		if ( $second_string ) {
			$between = '';
			if ( ' ' === $second_string[0] ) {
				$between       = ' ';
				$second_string = substr( $second_string, 1 );
			}
			$match_regex   = '#\(.+\)#';
			$second_string = preg_replace_callback(
				$match_regex,
				function( $matches ) use ( $amounts_regex, $match_units, $ignore_html_regex ) {
					$string               = $matches[0];
					$second_amounts_regex = str_replace( '?<amount>', '?<second_amount>', $amounts_regex );
					// e.g. "(2 to 2 2/3 cups – see recipe notes!)".
					$match_regex = '#(' . $amounts_regex . ')((?<separator>\s?(\-|\–|to)\s?)(' . $second_amounts_regex . '))(?<trailing>\s)' . $ignore_html_regex . '?#';
					$string      = preg_replace_callback(
						$match_regex,
						function( $matches ) {
							$match = '<span data-amount="' . Utils::process_amount_into_float( $matches['amount'] ) . '">' . $matches['amount'] . '</span>';
							// If a second amount was found too.
							if ( isset( $matches['second_amount'] ) && '' !== $matches['second_amount'] ) {
								$match .= $matches['separator'] . '<span data-amount="' . Utils::process_amount_into_float( $matches['second_amount'] ) . '">' . $matches['second_amount'] . '</span>';
							}
							if ( isset( $matches['trailing'] ) ) {
								$match .= $matches['trailing'];
							}
							return $match;
						},
						$string,
						-1,
						$count
					);
					if ( $count ) {
						return $string;
					}
					// e.g. "(you can add up to 1/4 cup for more dense, thicker muffins)".
					$match_regex = '#(?<!\-)(' . $amounts_regex . ')\s?(?<unit>' . implode( '|', $match_units ) . ')' . $ignore_html_regex . '(?!\w)#i';
					$string      = preg_replace_callback(
						$match_regex,
						function( $matches ) {
							return '<span data-amount="' . Utils::process_amount_into_float( $matches['amount'] ) . '" data-unit="' . strtolower( Utils::make_singular( $matches['unit'] ) ) . '">' . $matches[0] . '</span>';
						},
						$string,
						-1,
						$count
					);
					if ( $count ) {
						return $string;
					}
					// e.g. "(if you like a lot of sauce, go for 1 1/2 jars)".
					$match_regex = '#(' . $amounts_regex . ')((?<separator>\s?(\-|\–)\s?)(' . $second_amounts_regex . '))?(?<trailing>\s)' . $ignore_html_regex . '?#';
					$string      = preg_replace_callback(
						$match_regex,
						function( $matches ) {
							$match = '<span data-amount="' . Utils::process_amount_into_float( $matches['amount'] ) . '">' . $matches['amount'] . '</span>';
							// If a second amount was found too.
							if ( isset( $matches['second_amount'] ) && '' !== $matches['second_amount'] ) {
								$match .= $matches['separator'] . '<span data-amount="' . Utils::process_amount_into_float( $matches['second_amount'] ) . '">' . $matches['second_amount'] . '</span>';
							}
							if ( isset( $matches['trailing'] ) ) {
								$match .= $matches['trailing'];
							}
							return $match;
						},
						$string,
						-1,
						$count
					);
					if ( $count ) {
						return $string;
					}
					return $string;
				},
				$second_string,
				-1,
				$count
			);
			if ( $count ) {
				return $first_string . $between . $second_string;
			}
		}

		$transition_word_regex = '(?<transition_word>(about|into|or|\+)\s)';

		// An ingredient with explicit amount and unit.
		// e.g. "or 2 tbsp of minced garlic from a jar".
		$match_regex = '#' . $transition_word_regex . '(?<original>(' . self::$amounts_regex . ')\s?(?<unit>' . implode( '|', self::$match_units ) . '))#i';
		$string      = preg_replace_callback(
			$match_regex,
			function( $matches ) {
				return $matches['transition_word'] . '<span data-amount="' . Utils::process_amount_into_float( $matches['amount'] ) . '" data-unit="' . strtolower( Utils::make_singular( $matches['unit'] ) ) . '">' . $matches['original'] . '</span>';
			},
			$string,
			-1,
			$count
		);
		if ( $count ) {
			return $string;
		}

		// e.g. "- about 4-6 cups".
		$second_amounts_regex = str_replace( '?<amount>', '?<second_amount>', self::$amounts_regex );
		$match_regex          = '#' . $transition_word_regex . '(' . self::$amounts_regex . ')((?<separator>\s?(\-|\–|to)\s?)(' . $second_amounts_regex . '))?(?<trailing>\s)?#';
		$string               = preg_replace_callback(
			$match_regex,
			function( $matches ) {

				// Ignore amounts with a large numerator.
				// e.g. "50/50 mix with lean ground beef".
				if ( stripos( $matches['amount'], '/' ) >= 2 ) {
					return $matches[0];
				}

				$match = $matches['transition_word'] . '<span data-amount="' . Utils::process_amount_into_float( $matches['amount'] ) . '">' . $matches['amount'] . '</span>';
				// If a second amount was found too.
				if ( isset( $matches['second_amount'] ) && '' !== $matches['second_amount'] ) {
					$match .= $matches['separator'] . '<span data-amount="' . Utils::process_amount_into_float( $matches['second_amount'] ) . '">' . $matches['second_amount'] . '</span>';
				}
				if ( isset( $matches['trailing'] ) ) {
					$match .= $matches['trailing'];
				}
				return $match;
			},
			$string,
			-1,
			$count
		);
		if ( $count ) {
			return $string;
		}

		return $string;
	}

	/**
	 * Splits a string into two substrings, where the first string is at least a minimum words.
	 *
	 * @param string  $string Existing string to split.
	 * @param integer $words  Number of words to split on.
	 * @return array
	 */
	private static function split_string_into_min_substrings( $string, $words = 3 ) {
		$bits          = explode( '</span>', $string );
		$second_string = array_pop( $bits );
		$first_string  = implode( '</span>', $bits ) . '</span>';
		// At least three words in the initial match.
		$word_count = str_word_count( wp_strip_all_tags( $first_string ) );
		if ( $word_count >= $words ) {
			return array(
				$first_string,
				$second_string,
			);
		}
		$bits      = explode( ' ', $second_string );
		$remaining = $words - $word_count;
		if ( count( $bits ) < $remaining ) {
			return array(
				$first_string . $second_string,
				false,
			);
		}
		$first_string .= implode( ' ', array_slice( $bits, 0, $remaining ) );
		$second_string = implode( ' ', array_slice( $bits, $remaining ) );
		return array(
			$first_string . ' ',
			$second_string,
		);
	}

}
