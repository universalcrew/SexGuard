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


use pocketmine\item\Item;
use pocketmine\Player;


class ListArgument extends Argument
{
	const NAME = 'list';


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
		$main = $this->getManager();
		$list = $main->getRegionList($sender->getName());

		if( count($list) < 1 )
		{
			$sender->sendMessage($main->getValue('list_empty'));
			return TRUE;
		}

		$name = [];

		foreach( $list as $region )
		{
			$name[] = $region->getRegionName();
		}

		$message = $main->getValue('list_success');
		$message = str_replace('{list}', implode(', ', $name), $message);

		$sender->sendMessage($message);
		return TRUE;
	}
}