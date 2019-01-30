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
use sex\guard\command\argument\Argument;


use pocketmine\level\Position;
use pocketmine\Player;


class CreateArgument extends Argument
{
	const NAME = 'create';


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
		$main = $this->getManager();

		if( count($args) < 1 )
		{
			$sender->sendMessage($main->getValue('create_help'));
			return FALSE;
		}

		if( !isset($main->position[0][$nick]) or !isset($main->position[1][$nick]) )
		{
			$sender->sendMessage($main->getValue('pos_help'));
			return FALSE;
		}

		$name = $args[0];

		if( strlen($name) < 4 )
		{
			$sender->sendMessage($main->getValue('short_name'));
			return FALSE;
		}

		if( strlen($name) > 12 )
		{
			$sender->sendMessage($main->getValue('long_name'));
			return FALSE;
		}

		if( preg_match('#[^\s\da-z]#is', $name) )
		{
			$sender->sendMessage($main->getValue('bad_name'));
			return FALSE;
		}

		if( $main->getRegionByName($name) !== NULL )
		{
			$sender->sendMessage($main->getValue('rg_exist'));
			return FALSE;
		}

		$val = $main->getGroupValue($sender);

		if( count($main->getRegionList($nick)) >= $val['max_count'] )
		{
			if( !$sender->hasPermission('sexguard.all') )
			{
				$sender->sendMessage(str_replace('{max_count}', $val['max_count'], $main->getValue('rg_overcount')));
				return FALSE;
			}
		}

		$pos1 = $main->position[0][$nick];
		$pos2 = $main->position[1][$nick];

		if( $main->calculateSize($pos1, $pos2) > $val['max_size'] and !$sender->hasPermission('sexguard.all') )
		{
			$sender->sendMessage(str_replace('{max_size}', $val['max_size'], $main->getValue('rg_oversize')));
			return FALSE;
		}

		$x = [ min($pos1->getX(), $pos2->getX()), max($pos1->getX(), $pos2->getX()) ];
		$y = [ min($pos1->getY(), $pos2->getY()), max($pos1->getY(), $pos2->getY()) ];
		$z = [ min($pos1->getZ(), $pos2->getZ()), max($pos1->getZ(), $pos2->getZ()) ];

		if( $main->getValue('full_height', 'config') === TRUE )
		{
			$y = [ 0, 256 ];
		}

		$min = new Position($x[0], $y[0], $z[0], $pos1->getLevel());
		$max = new Position($x[1], $y[1], $z[1], $pos2->getLevel());

		$override = $main->getOverride($min, $max);

		if( count($override) > 0 and !$sender->hasPermission('sexguard.all') )
		{
			foreach( $override as $rg )
			{
				if( $rg->getOwner() != $nick )
				{
					$sender->sendMessage($main->getValue('rg_override'));
					return FALSE;
				}
			}
		}

		if( $main->getValue('pay_for_region', 'config') === TRUE )
		{
			if( isset($main->extension['economyapi']) )
			{
				$economy = $main->extension['economyapi'];
				$money   = $economy->myMoney($nick);
			}

			if( isset($main->extension['universalmoney']) )
			{
				$economy = $main->extension['universalmoney'];
				$money   = $economy->getMoney($nick);
			}

			if( isset($economy) )
			{
				if( !$sender->hasPermission('sexguard.all') )
				{
					$price = intval($main->getValue('price', 'config'));

					if( $money >= $price )
					{
						$economy->reduceMoney($nick, $price);
					}

					else
					{
						$sender->sendMessage(str_replace('{price}', $price, $main->getValue('player_have_not_money')));
						return FALSE;
					}
				}
			}
		}

		$main->createRegion($nick, $name, $min, $max);
		$sender->sendMessage(str_replace('{region}', $name, $main->getValue('rg_create')));
		return TRUE;
	}
}