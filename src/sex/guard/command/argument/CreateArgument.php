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
class CreateArgument extends Manager
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
			$sender->sendMessage($api->getValue('create_help'));
			return FALSE;
		}
		
		if( !isset($api->position[0][$nick]) or !isset($api->position[1][$nick]) )
		{
			$sender->sendMessage($api->getValue('pos_help'));
			return FALSE;
		}
		
		$name = $args[0];
		
		if( strlen($name) < 4 )
		{
			$sender->sendMessage($api->getValue('short_name'));
			return FALSE;
		}

		if( strlen($name) > 12 )
		{
			$sender->sendMessage($api->getValue('long_name'));
			return FALSE;
		}

		if( preg_match('#[^\s\da-z]#is', $name) )
		{
			$sender->sendMessage($api->getValue('bad_name'));
			return FALSE;
		}
		
		if( $api->getRegionByName($name) !== NULL )
		{
			$sender->sendMessage($api->getValue('rg_exist'));
			return FALSE;
		}
		
		$val = $api->getGroupValue($sender);
		
		if( count($api->getRegionList($nick)) >= $val['max_count'] )
		{
			if( !$sender->hasPermission('sexguard.all') )
			{
				$sender->sendMessage(str_replace('{max_count}', $val['max_count'], $api->getValue('rg_overcount')));
				return FALSE;
			}
		}
		
		$pos1 = $api->position[0][$nick];
		$pos2 = $api->position[1][$nick];
		
		if( $api->calculateSize($pos1, $pos2) > $val['max_size'] and !$sender->hasPermission('sexguard.all') )
		{
			$sender->sendMessage(str_replace('{max_size}', $val['max_size'], $api->getValue('rg_oversize')));
			return FALSE;
		}
		
		$x   = [ min($pos1->getX(), $pos2->getX()), max($pos1->getX(), $pos2->getX()) ];
		$y   = [ min($pos1->getY(), $pos2->getY()), max($pos1->getY(), $pos2->getY()) ];
		$z   = [ min($pos1->getZ(), $pos2->getZ()), max($pos1->getZ(), $pos2->getZ()) ];

		if( $api->getValue('full_height', 'config') === TRUE )
		{
			$y = [ 0, 256 ];
		}

		$min = new Position($x[0], $y[0], $z[0], $pos1->getLevel());
		$max = new Position($x[1], $y[1], $z[1], $pos2->getLevel());

		$override = $api->getOverride($min, $max);
		
		if( count($override) > 0 and !$sender->hasPermission('sexguard.all') )
		{
			foreach( $override as $rg )
			{
				if( $rg->getOwner() != $nick )
				{
					$sender->sendMessage($api->getValue('rg_override'));
					return FALSE;
				}
			}
		}
		
		if( $api->getValue('pay_for_region', 'config') === TRUE )
		{
			if( isset($api->extension['economyapi']) )
			{
				$economy = $api->extension['economyapi'];
				$money   = $economy->myMoney($nick);
			}

			if( isset($api->extension['universalmoney']) )
			{
				$economy = $api->extension['universalmoney'];
				$money   = $economy->getMoney($nick);
			}
			
			if( isset($economy) )
			{
				if( !$sender->hasPermission('sexguard.all') )
				{
					$price = intval($api->getValue('price', 'config'));
					
					if( $money >= $price )
					{
						$economy->reduceMoney($nick, $price);
					}
	
					else
					{
						$sender->sendMessage(str_replace('{price}', $price, $api->getValue('player_have_not_money')));
						return FALSE;
					}
				}
			}
		}
		
		$api->createRegion($nick, $name, $min, $max);
		$sender->sendMessage(str_replace('{region}', $name, $api->getValue('rg_create')));
		return TRUE;
	}
}