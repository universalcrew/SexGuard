<?php namespace sex\guard\data;


/**
 *  _    _       _                          _  ____
 * | |  | |_ __ (_)_    _____ _ ______ __ _| |/ ___\_ _______      __
 * | |  | | '_ \| | \  / / _ \ '_/ __// _' | | /   | '_/ _ \ \    / /
 * | |__| | | | | |\ \/ /  __/ | \__ \ (_) | | \___| ||  __/\ \/\/ /
 *  \____/|_| |_|_| \__/ \___|_| /___/\__,_|_|\____/_| \___/ \_/\_/
 *
 * @author sex_KAMAZ
 * @link   http://universalcrew.ru
 *
 */
use sex\guard\Manager;

use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\Player;


class Region extends Manager
{
	/**
	 * @var Manager
	 */
	private $api;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @todo convert $property.
	 *
	 * @var  mixed[]
	 */
	private $property = [];


	/**
	 * @todo  construct without array.
	 *
	 * @param Manager $api
	 * @param string  $name
	 * @param mixed[] $data
	 */
	function __construct( Manager $api, string $name, array $data )
	{
		$this->api      = $api;
		$this->name     = $name;
		$this->property = $data;
	}


	/**
	 *                _
	 *  _ _____  __ _(_) ___  _ __
	 * | '_/ _ \/ _' | |/ _ \| '_ \
	 * | ||  __/ (_) | | (_) | | | |
	 * |_| \___\\__, |_|\___/|_| |_|
	 *          /___/
	 *
	 * @param string $nick
	 */
	function addMember( string $nick )
	{
		$this->property['member'][] = strtolower($nick);
		
		$this->save();
	}


	/**
	 * @param string $nick
	 */
	function removeMember( string $nick )
	{
		$key = array_search(strtolower($nick), $this->property['member']);
		
		unset($this->property['member'][$key]);
		$this->save();
	}


	/**
	 * @param string $nick
	 */
	function setOwner( string $nick )
	{
		$this->property['owner'] = strtolower($nick);
		
		$this->save();
	}


	/**
	 * @param  string $flag
	 * @param  bool   $value
	 */
	function setFlag( string $flag, bool $value )
	{
		$flag = strtolower($flag);
		
		if( isset($this->property['flag'][$flag]) )
		{
			$this->property['flag'][$flag] = $value;
			
			$this->save();
		}
	}


	/**
	 * @return string
	 */
	function getRegionName( ): string
	{
		return strtolower($this->name);
	}


	/**
	 * @return string
	 */
	function getOwner( ): string
	{
		return strtolower($this->property['owner']);
	}


	/**
	 * @return string[]
	 */
	function getMemberList( ): array
	{
		return $this->property['member'];
	}


	/**
	 * @param  string $coord
	 *
	 * @return int
	 */
	function getMin( string $coord ): int
	{
		return $this->property['min'][strtolower($coord)] ?? 0;
	}


	/**
	 * @param  string $coord
	 *
	 * @return int
	 */
	function getMax( string $coord ): int
	{
		return $this->property['max'][strtolower($coord)] ?? 0;
	}


	/**
	 * @return Level | NULL
	 */
	function getLevel( )
	{
		return $this->api->getServer()->getLevelByName($this->property['level']);
	}


	/**
	 * @return string
	 */
	function getLevelName( ): string
	{
		return $this->property['level'] ?? 'undefined';
	}


	/**
	 * @param  string $flag
	 *
	 * @return bool
	 */
	function getFlagValue( string $flag ): bool
	{
		$flag = strtolower($flag);

		return $this->property['flag'][$flag] ?? Manager::DEFAULT_FLAG[$flag] ?? FALSE;
	}


	/**
	 * @todo remove save() method.
	 */
	private function save( )
	{
		$this->api->saveData($this->name, $this->property);
	}
}