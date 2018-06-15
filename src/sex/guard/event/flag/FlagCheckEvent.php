<?php namespace sex\guard\event\flag;


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

use pocketmine\event\plugin\PluginEvent;


class FlagCheckEvent extends RegionEvent
{
	/**
	 * @var string
	 */
	private $flag;

	/**
	 * @var bool
	 */
	private $need_cancel = FALSE;


	/**
	 * @param Manager $main
	 * @param Region  $region
	 * @param string  $flag
	 */
	function __construct( Manager $main, Region $region, string $flag )
	{
		parent::__construct($main, $region);

		$this->flag = strtolower($flag);
	}


	/**
	 *                        _
	 *   _____    _____ _ __ | |__
	 *  / _ \ \  / / _ \ '_ \|  _/
	 * |  __/\ \/ /  __/ | | | |_
	 *  \___/ \__/ \___|_| |_|\__\
	 *
	 *
	 * @return string
	 */
	function getFlag( )
	{
		return $this->flag;
	}


	/**
	 * @return bool
	 */
	function isMainEventCancelled( )
	{
		return $need_cancel;
	}


	/**
	 * @param bool $value
	 */
	function setMainEventCancelled( bool $value = TRUE )
	{
		$need_cancel = $value;
	}
}