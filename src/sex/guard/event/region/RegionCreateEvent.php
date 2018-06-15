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


class RegionCreateEvent extends RegionEvent implements Cancellable
{
	static $handlerList = null;


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
	 */
	function __construct( Manager $main, Region $region )
	{
		parent::__construct($main, $region);
	}
}