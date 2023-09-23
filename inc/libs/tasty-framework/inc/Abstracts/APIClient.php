<?php
/**
 * License API Client class.
 *
 * @package Tasty/Framework
 */

namespace Tasty\Framework\Abstracts;

use WP_Error;

/**
 * License API Client class.
 */
abstract class APIClient {

	/**
	 * API URL.
	 */
	const API_URL = 'https://www.wptasty.com/';

	/**
	 * Response Code.
	 *
	 * @var int
	 */
	protected $response_code = 200;

	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $error_message = '';

	/**
	 * Response Body.
	 *
	 * @var array
	 */
	protected $response_body;

	/**
	 * Handle the request.
	 *
	 * @param string $request_path Request path after tasty domain.
	 * @param array  $args Passed arguments.
	 * @param string $type GET or POST.
	 *
	 * @return bool
	 */
	private function handle_request( $request_path, $args = array(), $type = 'post' ) {
		$args['method'] = strtoupper( $type );

		$response = wp_remote_request(
			self::API_URL . $request_path,
			$args
		);

		return $this->check_response( $response );
	}

	/**
	 * Handle remote POST.
	 *
	 * @param string $request_path Request path after tasty domain.
	 * @param array  $args Array with options sent to API.
	 *
	 * @return bool WP Remote request status.
	 */
	protected function handle_post( $request_path, $args = array() ) {
		return $this->handle_request( $request_path, $args );
	}

	/**
	 * Handle remote GET.
	 *
	 * @param string $request_path Request path after tasty domain.
	 * @param array  $args Array with options sent to API.
	 *
	 * @return bool WP Remote request status.
	 */
	protected function handle_get( $request_path, $args = array() ) {
		return $this->handle_request( $request_path, $args, 'get' );
	}

	/**
	 * Handle SaaS request error.
	 *
	 * @param array|WP_Error $response WP Remote request.
	 *
	 * @return bool
	 */
	private function check_response( $response ) {
		$this->response_code = is_array( $response )
			? wp_remote_retrieve_response_code( $response )
			: $response->get_error_code();

		if ( 200 !== $this->response_code ) {
			$this->error_message = is_array( $response )
				? wp_remote_retrieve_response_message( $response )
				: $response->get_error_message();

			return false;
		}

		$this->response_body = (array) json_decode( wp_remote_retrieve_body( $response ), true );

		return true;
	}
}
