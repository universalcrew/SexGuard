<?php namespace sex\guard\event;


/**
 *  _    _       _                          _  ____
 * | |  | |_ __ (_)_    _____ _ ______ __ _| |/ ___\_ _______      __
 * | |  | | '_ \| | \  / / _ \ '_/ __// _' | / /   | '_/ _ \ \    / /
 * | |__| | | | | |\ \/ /  __/ | \__ \ (_) | \ \___| ||  __/\ \/\/ /
 *  \____/|_| |_|_| \__/ \___|_| /___/\__,_|_|\____/_| \___/ \_/\_/
 *
 * @author sex_KAMAZ
 * @link   https://vk.com/infernopage
 *
 */
use sex\guard\Manager;
use sex\guard\data\Region;

use pocketmine\event\plugin\PluginEvent;


class RegionEvent extends PluginEvent
{
	/**
	 * @var Region
	 */
	private $region;


	/**
	 * @param Manager $main
	 * @param Region  $region
	 */
	function __construct( Manager $main, Region $region )
	{
		parent::__construct($main);

		$this->region = $region;
	}


	/**
	 *                        _
	 *   _____    _____ _ __ | |__
	 *  / _ \ \  / / _ \ '_ \|  _/
	 * |  __/\ \/ /  __/ | | | |_
	 *  \___/ \__/ \___|_| |_|\__\
	 *
	 *
	 * @return Region
	 */
	function getRegion( )
	{
		return $this->region;
	}
}