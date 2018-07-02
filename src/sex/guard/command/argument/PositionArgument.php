<?php namespace sex\guard\command\argument;


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

use pocketmine\Player;
use pocketmine\level\Position;


/**
 * @todo nothing.
 */
class PositionArgument
{
	/**
	 * @var Manager
	 */
	private $api;


	/**
	 * @param Manager $api
	 */
	function __construct( Manager $api )
	{
		$this->api = $api;
	}


	/**
	 *                                          _
	 *   __ _ _ ____ _ _   _ _ __ _   ___ _ ___| |_
	 *  / _' | '_/ _' | | | | '  ' \ / _ \ '_ \   _\
	 * | (_) | || (_) | |_| | || || |  __/ | | | |_
	 *  \__,_|_| \__, |\___/|_||_||_|\___|_| |_|\__\
	 *           /___/
	 *
	 * @param  Player   $sender
	 * @param  string[] $args
	 *
	 * @return bool
	 */
	function execute( Player $sender, array $args ): bool
	{
		$nick = strtolower($sender->getName());
		$api  = $this->api;
		
		if( count($args) < 1 )
		{
			$sender->sendMessage($api->getValue('pos_help'));
			return FALSE;
		}
		
		$pos = new Position(
			$sender->getFloorX(),
			$sender->getFloorY(),
			$sender->getFloorZ(),
			$sender->getLevel()
		);

		$region = $api->getRegion($pos);
		
		if( $region !== NULL and !$sender->hasPermission('sexguard.all') )
		{
			if( $region->getOwner() != $nick )
			{
				$sender->sendMessage($api->getValue('rg_override'));
				return FALSE;
			}
		}
		
		if( $args[0] == '1' )
		{
			if( isset($api->position[1][$nick]) )
			{
				unset($api->position[1][$nick]);
			}
			
			$api->position[0][$nick] = $pos;
			
			$sender->sendMessage($api->getValue('pos_1_set'));
			return TRUE;
		}

		elseif( $args[0] == '2' )
		{
			if( !isset($api->position[0][$nick]) )
			{
				$sender->sendMessage($api->getValue('pos_help'));
				return FALSE;
			}
			
			if( $api->position[0][$nick]->getLevel()->getName() != $sender->getLevel()->getName() )
			{
				unset($api->position[0][$nick]);
				$sender->sendMessage($api->getValue('pos_another_world'));
				return FALSE;
			}
			
			$val  = $api->getGroupValue($sender);
			$size = $api->calculateSize($api->position[0][$nick], $pos);
			
			if( $size > $val['max_size'] and !$sender->hasPermission('sexguard.all') )
			{
				$sender->sendMessage(str_replace('{max_size}', $val['max_size'], $api->getValue('rg_oversize')));
				return FALSE;
			}
			
			$api->position[1][$nick] = $pos;
			
			$sender->sendMessage($api->getValue('pos_2_set'));
			return TRUE;
		}

		else
		{
			
			$sender->sendMessage($api->getValue('pos_help'));
			return FALSE;
		}
	}
}