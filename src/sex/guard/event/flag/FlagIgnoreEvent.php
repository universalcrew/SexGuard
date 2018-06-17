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

use pocketmine\event\Cancellable;
use pocketmine\Player;


class FlagIgnoreEvent extends FlagCheckEvent implements Cancellable
{
	static $handlerList = null;


	/**
	 * @var Player
	 */
	private $player;


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
	 * @param Player  $player
	 */
	function __construct( Manager $main, Region $region, string $flag, Player $player )
	{
		parent::__construct($main, $region, $flag);

		$this->player = $player;
	}


	/**
	 * @return Player
	 */
	function getPlayer( )
	{
		return $this->player;
	}
}