<?php namespace sex\guard\listener;


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
use sex\guard\event\flag\FlagIgnoreEvent;
use sex\guard\event\flag\FlagCheckByEntityEvent;

use pocketmine\Player;
use pocketmine\entity\Entity;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityCombustEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;


/**
 * @todo good listener should listen only one event.
 *       rewrite explode listener for more safety.
 */
class EntityGuard implements Listener
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
	 *  _ _      _
	 * | (_)____| |_____ _ __   ___ _ __
	 * | | / __/   _/ _ \ '_ \ / _ \ '_/
	 * | | \__ \| ||  __/ | | |  __/ |
	 * |_|_|___/|___\___|_| |_|\___|_|
	 *
	 *
	 * @internal pvp    flag.
	 *           mob    flag.
	 *           damage flag.
	 *
	 * @param    EntityDamageEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onDamage( EntityDamageEvent $event )
	{
		if( $event->isCancelled() )
		{
			return;
		}
		
		$entity = $event->getEntity();
		
		if( $event instanceof EntityDamageByEntityEvent )
		{
			$damager = $event->getDamager();
			$flag    = 'mob';

			if( $entity instanceof Player and $damager instanceof Player )
			{
				$flag = 'pvp';
			}

			if( $this->isFlagDenied($damager, $flag, $entity) )
			{
				$event->setCancelled();
			}

			return;
		}

		if( $this->isFlagDenied($entity, 'damage') )
		{
			$event->setCancelled();
		}
	}


	/**
	 * @internal mob flag.
	 *
	 * @param    ProjectileHitEntityEvent $event
	 *
	 * @priority NORMAL
	 */
	function onProjectileHit( ProjectileHitEntityEvent $event )
	{
		$entity     = $event->getEntityHit();
		$projectile = $event->getEntity();
		$damager    = $projectile->getOwningEntity() ?? $projectile;

		$flag = 'mob';

		if( $entity instanceof Player and $damager instanceof Player )
		{
			$flag = 'pvp';
		}

		if( $this->isFlagDenied($damager, $flag, $entity) )
		{
			$event->getEntity()->setPunchKnockback(0.00);
		}
	}


	/**
	 * @internal combust flag.
	 *
	 * @param    EntityCombustEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onCombust( EntityCombustEvent $event )
	{
		if( $event->isCancelled() )
		{
			return;
		}
		
		$entity = $event->getEntity();
		
		if( $this->isFlagDenied($entity, 'combust') )
		{
			$event->setCancelled();
		}
	}


	/**
	 * @internal explode flag.
	 *
	 * @param    EntityExplodeEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onExplode( EntityExplodeEvent $event )
	{
		if( $event->isCancelled() )
		{
			return;
		}
		
		$entity = $event->getEntity();
		
		if( $this->isFlagDenied($entity, 'explode') )
		{
			$event->setBlockList([]);
		}
	}


	/**
	 * @internal teleport flag.
	 *
	 * @param    EntityTeleportEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onTeleport( EntityTeleportEvent $event )
	{
		if( $event->isCancelled() )
		{
			return;
		}
		
		$entity = $event->getEntity();
		
		if( $this->isFlagDenied($entity, 'teleport') )
		{
			$event->setCancelled();
		}
	}


	/**
	 * @internal change flag.
	 *
	 * @param    EntityBlockChangeEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onBlockChange( EntityBlockChangeEvent $event )
	{
		if( $event->isCancelled() )
		{
			return;
		}
		
		$entity = $event->getEntity();
		
		if( $this->isFlagDenied($entity, 'change') )
		{
			$event->setCancelled();
		}
	}


	/**
	 * @param  Entity $entity
	 * @param  string $flag
	 * @param  Entity $target
	 *
	 * @return bool
	 */
	private function isFlagDenied( Entity $entity, string $flag, Entity $target = NULL ): bool
	{
		$api    = $this->api;
		$result = FALSE;

		if( isset($target) )
		{
			$region = $api->getRegion($target);
			
			if( isset($region) and !$region->getFlagValue($flag) )
			{
				$result = TRUE;
			}
		}

		$region = $api->getRegion($entity);

		if( !isset($region) )
		{
			return $result;
		}

		if( ($entity instanceof Player) )
		{
			$val = $api->getGroupValue($entity);
			
			if( in_array($flag, $val['ignored_flag']) )
			{
				if( !in_array($region->getRegionName(), $val['ignored_region']) )
				{
					$event = new FlagIgnoreEvent($api, $region, $flag, $entity);

					$api->getServer()->getPluginManager()->callEvent($event);

					if( $event->isCancelled() )
					{
						return $event->isMainEventCancelled();
					}

					return FALSE;
				}
			}
		}
		
		if( !$region->getFlagValue($flag) )
		{
			$event = new FlagCheckByEntityEvent($api, $region, $flag, $entity, $target);

			$api->getServer()->getPluginManager()->callEvent($event);

			if( $event->isCancelled() )
			{
				return $event->isMainEventCancelled();
			}

			if( ($entity instanceof Player) )
			{
				$api->sendWarning($entity, $api->getValue('warn_flag_'.$flag));
			}
			
			return TRUE;
		}

		return $result;
	}
}