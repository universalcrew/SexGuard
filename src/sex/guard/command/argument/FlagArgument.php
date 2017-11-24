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


/**
 * @todo nothing.
 */
class FlagArgument extends Manager
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
		$list = $api->getAllowedFlag();
		
		if( count($args) < 2 )
		{
			$sender->sendMessage(str_replace('{flag_list}', implode(' ', $list), $api->getValue('flag_help')));
			return FALSE;
		}

		$region = $api->getRegionByName($args[0]);

		if( !isset($region) )
		{
			$sender->sendMessage($api->getValue('rg_not_exist'));
			return FALSE;
		}
		
		if( $region->getOwner() != $nick and !$sender->hasPermission('sexguard.all') )
		{
			$sender->sendMessage($api->getValue('player_not_owner'));
			return FALSE;
		}
		
		$flag = $args[1];
		
		if( !in_array($flag, $list) )
		{
			$sender->sendMessage($api->getValue('flag_not_exist'));
			return FALSE;
		}
		
		if( $region->getFlagValue($flag) )
		{
			$region->setFlag($flag, FALSE);
			$sender->sendMessage(str_replace('{flag}', $flag, $api->getValue('flag_off')));
		}

		else
		{
			$region->setFlag($flag, TRUE);
			$sender->sendMessage(str_replace('{flag}', $flag, $api->getValue('flag_on')));
		}
		
		return TRUE;
	}
}