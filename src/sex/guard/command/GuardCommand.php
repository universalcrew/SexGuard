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

use sex\guard\command\argument\Argument;
use sex\guard\command\argument\FlagArgument;
use sex\guard\command\argument\ListArgument;
use sex\guard\command\argument\WandArgument;
use sex\guard\command\argument\OwnerArgument;
use sex\guard\command\argument\CreateArgument;
use sex\guard\command\argument\MemberArgument;
use sex\guard\command\argument\RemoveArgument;
use sex\guard\command\argument\PositionArgument;


use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\permission\Permission;
use pocketmine\Player;


/**
 * @todo rewrite arguments and allow ConsoleCommandSender.
 */
abstract class GuardCommand extends Command
{
	/**
	 * @todo this values should be configurable.
	 */
	const NAME        = 'rg';
	const DESCRIPTION = 'Показывает помощь или список команд управления регионами';
	const PERMISSION  = 'sexguard.command.rg';


	/**
	 * @var Manager
	 */
	private $main;

	/**
	 * @var Argument[]
	 */
	private $argument_list = [];


	/**
	 * @param Manager $main
	 */
	function __construct( Manager $main )
	{
		parent::__construct(self::NAME, self::DESCRIPTION);

		$this->main          = $main;
		$this->argument_list = [
			new PositionArgument($main),
			new CreateArgument($main),
			new MemberArgument($main),
			new RemoveArgument($main),
			new OwnerArgument($main),
			new FlagArgument($main),
			new ListArgument($main),
			new WandArgument($main)
		];

		$permission = new Permission(self::PERMISSION, self::DESCRIPTION, Permission::DEFAULT_TRUE);

		$main->getServer()->getPluginManager()->addPermission($permission);
		$this->setPermission($permission->getName());
	}


	/**
	 * @var Manager
	 */
	private function getManager( )
	{
		return $this->main;
	}


	/**
	 * @var Argument|null
	 */
	private function getArgument( string $name )
	{
		$name = strtolower($name);

		foreach( $this->argument_list as $argument )
		{
			if( $argument->getName() != $name )
			{
				continue;
			}

			return $argument;
		}

		return NULL;
	}


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
	protected function executeSafe( CommandSender $sender, string $label, array $args ): bool
	{
		$main = $this->getManager();

		if( !($sender instanceof Player) )
		{
			$sender->sendMessage($main->getValue('no_console'));
			return FALSE;
		}

		if( !$this->testPermissionSilent($sender) )
		{
			$sender->sendMessage($main->getValue('no_permission'));
			return FALSE;
		}

		if( count($args) < 1 )
		{
			$sender->sendMessage($main->getValue('rg_help'));
			return FALSE;
		}

		$argument = $this->getArgument(array_shift($args));

		if( !isset($argument) )
		{
			$sender->sendMessage($main->getValue('rg_help'));
			return FALSE;
		}

		return $argument->execute($sender, array_map('strtolower', $args));
	}
}