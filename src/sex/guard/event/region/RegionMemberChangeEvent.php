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


class RegionMemberChangeEvent extends RegionEvent implements Cancellable
{
	const TYPE_ADD    = 0;
	const TYPE_REMOVE = 1;


	static $handlerList = null;


	/**
	 * @var string
	 */
	private $member;

	/**
	 * @var int
	 */
	private $type;


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
	 * @param string  $member
	 * @param int     $type
	 */
	function __construct( Manager $main, Region $region, string $member, int $type )
	{
		parent::__construct($main, $region);

		$this->member = strtolower($member);
		$this->type   = $type == self::TYPE_ADD ? self::TYPE_ADD : self::TYPE_REMOVE;

		echo "RegionMemberChangeEvent:$type". PHP_EOL;
	}


	/**
	 * @return string
	 */
	function getMember( )
	{
		return $this->member;
	}


	/**
	 * @return int
	 */
	function getType( )
	{
		return $this->type;
	}
}