<?php

/*
	Some useful metadata handling stuff
*/

class Q2A_Util_Metadata
{
	const METADATA_FILE_JSON = 'metadata.json';

	/**
	 * Fetch metadata information from an addon path
	 * @param string $path Directory the addon is in (without trailing slash)
	 * @return array The metadata fetched from the JSON file, or an empty array otherwise
	 */
	public function fetchFromAddonPath($path)
	{
		$metadataFile = $path . '/' . self::METADATA_FILE_JSON;
		if (!is_file($metadataFile)) {
			return array();
		}

		$content = file_get_contents($metadataFile);
		return $this->getArrayFromJson($content);
	}

	/**
	 * Fetch metadata information from an URL
	 * @param string $url URL linking to a metadata.json file
	 * @return array The metadata fetched from the file
	 */
	public function fetchFromUrl($url, $type = 'Plugin')
	{
		$contents = qa_retrieve_url($url);
		$metadata = $this->getArrayFromJson($contents);

		// fall back to old metadata format
		if (empty($metadata)) {
			$metadata = qa_addon_metadata($contents, $type);
		}

		return $metadata;
	}

	/**
	 * Return an array from a JSON string
	 * @param mixed $json The JSON string to turn into an array
	 * @return array Always return an array containing the decoded JSON or an empty array in case the
	 * $json parameter is not a valid JSON string
	 */
	private function getArrayFromJson($json)
	{
		$result = json_decode($json, true);
		return is_array($result) ? $result : array();
	}
}
