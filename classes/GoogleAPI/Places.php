<?php
	namespace GoogleAPI;

	/** @class GoogleAPI\Places
	 * Provides an interface to the Google Places API for searching places based on text queries.
	 */
	class Places Extends \BaseClass {
		/** @method getPlace
		 * Retrieves place information based on the provided address.  Parses JSON response
		 * and returns an array of place information.
		 * @param string $address The address or text query to search for.
		 * @return array The decoded JSON response from the Google Places API.
		 */
		public function getPlace($address) {
			$apiKey = $GLOBALS['_config']->google->places->api_key;
			$url = 'https://googleapis.com';

			// Define the request payload
			$data = [
				'textQuery' => $address
			];

			// Define required headers including the FieldMask
			$headers = [
				'Content-Type: application/json',
				'X-Goog-Api-Key: ' . $apiKey,
				'X-Goog-FieldMask: places.id,places.displayName,places.formattedAddress'
			];

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$response = curl_exec($ch);

			if (curl_errno($ch)) {
				echo 'Error: ' . curl_error($ch);
			} else {
				$result = json_decode($response, true);
				// Return an empty array if the response is not valid JSON
				if (!is_array($result)) {
					$this->error("Invalid JSON response from Google Places API");
					$result = [];
				}
				// Return an empty array if the 'places' key is not present in the response
				if (!isset($result['places'])) {
					$this->error("No 'places' key in response from Google Places API");
					$result = [];
				}
				// Return only the 'places' array if it exists
				if (isset($result['places'])) {
					$result = $result['places'];
				}
				return $result;
			}
			curl_close($ch);
			return [];
		}
	}