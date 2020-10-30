<?php
/**
 * These are all the functions that will be used into the bot's source code.
 * No libraries are used in this project.
 *
 * @author		Giulio Coa
 *
 * @copyright	2020- Giulio Coa
 *
 * @license		https://choosealicense.com/licenses/lgpl-3.0/ LGPL version 3
 */

/**
 * Execute an HTTP(S) request.
 *
 * @param string $url The HTTP(S) endpoint.
 *
 * @return mixed
 */
function request(string $url) {
	/**
	 * Encode the URL
	 *
	 * urlencode() Encode the URL, converting all the special character to its safe value
	 */
	$url = urlencode($url);

	/**
	 * Open the cURL session
	 *
	 * curl_init() Open the session
	 */
	$curl_session = curl_init($url);

	/**
	 * Set the cURL session
	 *
	 * curl_setopt_array() Set the options for the session
	 */
	curl_setopt_array($curl_session, [
		CURLOPT_HEADER => FALSE,
		CURLOPT_RETURNTRANSFER => TRUE
	]);

	/**
	 * Exec the request
	 *
	 * curl_exec() Execute the session
	 */
	$result = curl_exec($curl_session);

	/**
	 * Close the cURL session
	 *
	 * curl_close() Close the session
	 */
	curl_close($curl_session);
	return $result;
}
