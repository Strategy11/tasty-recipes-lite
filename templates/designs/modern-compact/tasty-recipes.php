<?php
/**
 * Modern Compact recipe template.
 *
 * @package Tasty_Recipes
 */

Tasty_Recipes::echo_template_part(
	'admin/locked-template',
	compact( 'template_object', 'recipe_styles' )
);
