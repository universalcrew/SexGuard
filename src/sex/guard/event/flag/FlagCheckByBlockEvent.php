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
use pocketmine\block\Block;
use pocketmine\Player;


class FlagCheckByBlockEvent extends FlagCheckEvent implements Cancellable
{
	static $handlerList = null;


	/**
	 * @var Block
	 */
	private $block;

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
	 * @param Block   $block
	 * @param Player  $player
	 */
	function __construct( Manager $main, Region $region, string $flag, Block $block, Player $player = NULL )
	{
		parent::__construct($main, $region, $flag);

		$this->block  = $block;
		$this->player = $player;

		echo "FlagCheckByBlockEvent". PHP_EOL;
	}


	/**
	 * @return Block
	 */
	function getBlock( )
	{
		return $this->block;
	}


	/**
	 * @return Player
	 */
	function getPlayer( )
	{
		return $this->player;
	}
}