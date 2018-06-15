<?php namespace sex\guard\event\region;


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
use sex\guard\event\RegionEvent;

use pocketmine\event\Cancellable;


class RegionOwnerChangeEvent extends RegionEvent implements Cancellable
{
	static $handlerList = null;


	/**
	 * @var string
	 */
	private $old_owner;

	/**
	 * @var string
	 */
	private $new_owner;


	/**
	 *                        _
	 *   _____    _____ _ __ | |__
	 *  / _ \ \  / / _ \ '_ \|  _/
	 * |  __/\ \/ /  __/ | | | |_
	 *  \___/ \__/ \___|_| |_|\__\
	 *
	 *
	 * @param Manager $main
	 * @param Region  $region
	 * @param string  $old
	 * @param string  $new
	 */
	function __construct( Manager $main, Region $region, string $old, string $new )
	{
		parent::__construct($main, $region);

		$this->old_owner = strtolower($old);
		$this->new_owner = strtolower($new);

		echo "RegionOwnerChangeEvent". PHP_EOL;
	}


	/**
	 * @return string
	 */
	function getOldOwner( )
	{
		return $this->old_owner;
	}


	/**
	 * @return string
	 */
	function getNewOwner( )
	{
		return $this->new_owner;
	}
}