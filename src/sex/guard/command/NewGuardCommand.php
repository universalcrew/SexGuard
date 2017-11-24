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

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

/**
 * @todo rewrite arguments and allow ConsoleCommandSender.
 */
class NewGuardCommand extends Command
{
	/**
	 * @var Manager
	 */
	private $api;

	/**
	 * @var Argument[]
	 */
	private $argument = [];


	/**
	 * @param Manager    $api
	 * @param Argument[] $argument
	 */
	function __construct( Manager $api, array $argument )
	{
		$this->api      = $api;
		$this->argument = $argument;
		
		parent::__construct('rg');
		$this->setPermission('sexguard.command.rg');
	}


	/**
	 *                                             _
	 *   ___  ___  _ __ _  _ __ _   __ _ _ __   __| |
	 *  / __\/ _ \| '  ' \| '  ' \ / _' | '_ \ / _' |
	 * | (__| (_) | || || | || || | (_) | | | | (_) |
	 *  \___/\___/|_||_||_|_||_||_|\__._|_| |_|\__._|
	 *
	 *
	 * @param CommandSender $sender
	 * @param string        $label
	 * @param string[]      $args
	 */
	function execute( CommandSender $sender, string $label, array $args ): bool
	{
		$api = $this->api;
		
		if( !($sender instanceof Player) )
		{
			$sender->sendMessage($api->getValue('no_console'));
			return FALSE;
		}
		
		if( !$this->testPermissionSilent($sender) )
		{
			$sender->sendMessage($api->getValue('no_permission'));
			return FALSE;
		}
		
		if( count($args) < 1 )
		{
			$sender->sendMessage($api->getValue('rg_help'));
			return FALSE;
		}
		
		$args = array_map('mb_strtolower', $args);
		$name = array_shift($args);
		
		if( !in_array($name, array_keys($this->argument)) )
		{
			$sender->sendMessage($api->getValue('rg_help'));
			return FALSE;
		}
		
		return $this->argument[$name]->execute($sender, $args);
	}
}