<?php
/**
 * Scout plugin for Craft CMS 3.x.
 *
 * Craft Scout provides a simple solution for adding full-text search to your entries. Scout will automatically keep your search indexes in sync with your entries.
 *
 * @link      https://rias.be
 *
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\scout\services;

use AlgoliaSearch\Client;
use Craft;
use craft\base\Component;
use craft\base\Element;
use rias\scout\models\AlgoliaIndex;
use rias\scout\Scout;

/**
 * @author    Rias
 *
 * @since     0.1.0
 */
class ScoutService extends Component
{
	// Public Methods
	// =========================================================================
	
	public $settings;
	
	/* @var Client */
	public $client;
	
	/* @var array */
	private $mappings = [];
	
	public function init()
	{
		$this->settings = Scout::$plugin->getSettings();
	}
	
	/**
	 * @throws \Exception
	 *
	 * @return Client
	 */
	public function getClient()
	{
		if (is_null($this->client)) {
			$this->client = new Client($this->settings->application_id, $this->settings->admin_api_key);
		}
		
		return $this->client;
	}
	
	/**
	 * Returns an array of Algolia_IndexModel instances with the unprefixed index names as keys.
	 *
	 * @return AlgoliaIndex[]
	 */
	public function getMappings()
	{
		if (!count($this->mappings)) {
			$mappingsConfig = $this->settings->mappings;
			foreach ($mappingsConfig as $mappingConfig) {
				$this->mappings[] = new AlgoliaIndex($mappingConfig);
			}
		}
		
		return $this->mappings;
	}
	
	/**
	 * Passes the supplied element to each configured index.
	 *
	 * @param $elements array
	 *
	 * @throws \AlgoliaSearch\AlgoliaException
	 * @throws \Exception
	 */
	public function deindexElements($elements)
	{
		foreach ($this->getMappings() as $algoliaIndex) {
			if (isset($algoliaIndex['criteria']['site'])) {
				foreach ($elements as $element) {
					if ($element->site->handle === $algoliaIndex['criteria']['site']) {
						$algoliaIndex->deindexElements($elements);
					}
				}
			} else {
				$algoliaIndex->deindexElements($elements);
			}
		}
	}
	
	/**
	 * Passes the supplied elements to each configured index.
	 *
	 * @param $elements array
	 *
	 * @throws \AlgoliaSearch\AlgoliaException
	 * @throws \Exception
	 */
	public function indexElements($elements)
	{
		foreach ($this->getMappings() as $algoliaIndex) {
			if (isset($algoliaIndex['criteria']['site'])) {
				foreach ($elements as $element) {
					if ($element->site->handle === $algoliaIndex['criteria']['site']) {
						$algoliaIndex->indexElements($elements);
					}
				}
			} else {
				$algoliaIndex->indexElements($elements);
			}
		}
	}
}
