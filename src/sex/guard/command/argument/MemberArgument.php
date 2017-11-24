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
class MemberArgument extends Manager
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
		
		if( count($args) < 3 or !in_array($args[0], ['add', 'remove']) )
		{
			$sender->sendMessage($api->getValue('member_help'));
			return FALSE;
		}
		
		$region = $api->getRegionByName($args[1]);

		if( !isset($region) )
		{
			$sender->sendMessage($api->getValue('rg_not_exist'));
			return FALSE;
		}
		
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
		
		$member = $args[2];
		
		if( $args[0] == 'add' )
		{
			if( in_array($member, $region->getMemberList()) )
			{
				$sender->sendMessage($api->getValue('player_already_member'));
				return FALSE;
			}
			
			$region->addMember($member);
			$sender->sendMessage(str_replace('{player}', $member, $api->getValue('member_add')));
		}

		else
		{
			
			if( !in_array($member, $region->getMemberList()) )
			{
				$sender->sendMessage($api->getValue('player_not_exist'));
				return FALSE;
			}
			
			$region->removeMember($member);
			$sender->sendMessage(str_replace('{player}', $member, $api->getValue('member_remove')));
		}
		
		return TRUE;
	}
}