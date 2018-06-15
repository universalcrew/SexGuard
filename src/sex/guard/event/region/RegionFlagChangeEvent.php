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


class RegionFlagChangeEvent extends RegionEvent implements Cancellable
{
	static $handlerList = null;


	/**
	 * @var string
	 */
	private $owner;

	/**
	 * @var string
	 */
	private $flag;

	/**
	 * @var bool
	 */
	private $new_value;


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
	 * @param string  $flag
	 * @param bool    $value
	 */
	function __construct( Manager $main, Region $region, string $flag, bool $value )
	{
		parent::__construct($main, $region);

		$this->flag      = strtolower($flag);
		$this->new_value = $value;

		echo "RegionFlagChangeEvent". PHP_EOL;
	}


	/**
	 * @return string
	 */
	function getFlag( )
	{
		return $this->flag;
	}


	/**
	 * @return bool
	 */
	function getOldValue( )
	{
		return !$this->getNewValue();
	}


	/**
	 * @return bool
	 */
	function getNewValue( )
	{
		return $this->new_value;
	}
}