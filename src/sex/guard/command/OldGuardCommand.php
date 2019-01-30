<?php namespace sex\guard\command;


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
use sex\guard\command\GuardCommand;


use pocketmine\command\CommandSender;


/**
 * @todo rewrite arguments and allow ConsoleCommandSender.
 */
class OldGuardCommand extends GuardCommand
{
	/**
	 *                                             _
	 *   ___  ___  _ __ _  _ __ _   __ _ _ __   __| |
	 *  / __\/ _ \| '  ' \| '  ' \ / _' | '_ \ / _' |
	 * | (__| (_) | || || | || || | (_) | | | | (_) |
	 *  \___/\___/|_||_||_|_||_||_|\__._|_| |_|\__._|
	 *
	 *
	 * @param  CommandSender $sender
	 * @param  string        $label
	 * @param  string[]      $args
	 *
	 * @return bool
	 */
	function execute( CommandSender $sender, $label, array $args )
	{
		return $this->executeSafe($sender, $label, $args);
	}
}