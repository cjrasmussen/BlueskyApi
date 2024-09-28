<?php

namespace cjrasmussen\BlueskyApi;

use JsonException;
use RuntimeException;

/**
 * Class for interacting with the Bluesky API/AT protocol
 */
class BlueskyApi
{
	private ?string $accountDid = null;
	private ?string $apiKey = null;
	private ?string $refreshToken = null;
	private string $apiUri;
	private ?string $lastResponseHeader = null;

	public function __construct(string $api_uri = 'https://bsky.social/xrpc/')
	{
		$this->apiUri = $api_uri;
	}

	/**
	 * Authorize a user
	 *
	 * If handle and password are provided, a new session will be created. If a refresh token is provided, the session
	 * will be refreshed.
	 *
	 * @param string $handleOrToken
	 * @param string|null $app_password
	 * @return bool
	 * @throws RuntimeException|JsonException
	 */
	public function auth(string $handleOrToken, ?string $app_password = null): bool
	{
		if (($handleOrToken) && ($app_password)) {
			$data = $this->startNewSession($handleOrToken, $app_password);
		} else {
			$data = $this->refreshSession($handleOrToken);
		}

		if ($data) {
			$this->accountDid = $data->did;
			$this->apiKey = $data->accessJwt;
			$this->refreshToken = $data->refreshJwt;

			return (bool)$data->did;
		}

		return false;
	}

	/**
	 * Check to see if the current session is active
	 *
	 * @return bool
	 * @throws JsonException
	 */
	public function isSessionActive(): bool
	{
		$data = $this->request('GET', 'com.atproto.server.getSession');
		return (($data !== null) && empty($data->error));
	}

	/**
	 * Get the current account DID
	 *
	 * @return string
	 */
	public function getAccountDid(): ?string
	{
		return $this->accountDid;
	}

	/**
	 * Get the refresh token
	 *
	 * @return string
	 */
	public function getRefreshToken(): ?string
	{
		return $this->refreshToken;
	}

	/**
	 * Get the response header from the most recent API request
	 *
	 * @return ?string
	 */
	public function getLastResponseHeader(): ?string
	{
		return $this->lastResponseHeader;
	}

	/**
	 * Make a request to the Bluesky API
	 *
	 * @param string $type
	 * @param string $request
	 * @param array $args
	 * @param string|null $body
	 * @param string|null $content_type
	 * @return ?object
	 * @throws JsonException
	 */
	public function request(string $type, string $request, array $args = [], ?string $body = null, string $content_type = null): ?object
	{
		$url = $this->apiUri . $request;

		if (($type === 'GET') && (count($args))) {
			$url .= '?' . http_build_query($args);
		} elseif (($type === 'POST') && (!$content_type)) {
			$content_type = 'application/json';
		}

		$headers = [];
		if ($this->apiKey) {
			$headers[] = 'Authorization: Bearer ' . $this->apiKey;
		}

		if ($content_type) {
			$headers[] = 'Content-Type: ' . $content_type;

			if (($content_type === 'application/json') && (count($args))) {
				$body = json_encode($args, JSON_THROW_ON_ERROR);
				$args = [];
			}
		}

		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);

		if (count($headers)) {
			curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
		}

		switch ($type) {
			case 'POST':
				curl_setopt($c, CURLOPT_POST, 1);
				break;
			case 'GET':
				curl_setopt($c, CURLOPT_HTTPGET, 1);
				break;
			default:
				curl_setopt($c, CURLOPT_CUSTOMREQUEST, $type);
		}

		if ($body) {
			curl_setopt($c, CURLOPT_POSTFIELDS, $body);
		} elseif (($type !== 'GET') && (count($args))) {
			curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($args, JSON_THROW_ON_ERROR));
		} elseif ($type === 'POST') {
			curl_setopt($c, CURLOPT_POSTFIELDS, null);
		}

		curl_setopt($c, CURLOPT_HEADER, 1);
		curl_setopt($c, CURLOPT_VERBOSE, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_ENCODING, '');
		curl_setopt($c, CURLOPT_MAXREDIRS, 10);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 1);
		$response = curl_exec($c);
		$header_length = curl_getinfo($c, CURLINFO_HEADER_SIZE);
		curl_close($c);

		if (!$response) {
			return null;
		}

		$this->lastResponseHeader = substr($response, 0, $header_length);
		$body = substr($response, $header_length);

		return json_decode($body, false, 512, JSON_THROW_ON_ERROR);
	}

	/**
	 * Start a new user session using handle and app password
	 *
	 * @param string $handle
	 * @param string $app_password
	 * @return ?object
	 * @throws RuntimeException|JsonException
	 */
	private function startNewSession(string $handle, string $app_password): ?object
	{
		$this->apiKey = null;

		$args = [
			'identifier' => $handle,
			'password' => $app_password,
		];
		$data = $this->request('POST', 'com.atproto.server.createSession', $args);

		if (($data !== null) && (!empty($data->error))) {
			throw new RuntimeException($data->message);
		}

		return $data;
	}

	/**
	 * Refresh a user session using a refresh token
	 *
	 * @param string $refresh_token
	 * @return ?object
	 * @throws RuntimeException|JsonException
	 */
	private function refreshSession(string $refresh_token): ?object
	{
		$this->apiKey = $refresh_token;
		$data = $this->request('POST', 'com.atproto.server.refreshSession');
		$this->apiKey = null;

		if (($data !== null) && (!empty($data->error))) {
			throw new RuntimeException($data->message);
		}

		return $data;
	}
}
