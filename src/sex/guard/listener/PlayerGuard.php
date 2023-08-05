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
use sex\guard\event\flag\FlagCheckByPlayerEvent;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\level\Position;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerBucketEmptyEvent;


/**
 * @todo good listener should listen only one event.
 */
class PlayerGuard implements Listener
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
	 * @param PlayerQuitEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onQuit( PlayerQuitEvent $event )
	{
		$nick = strtolower($event->getPlayer()->getName());
		$api  = $this->api;
		
		if( isset($api->position[0][$nick]) )
		{
			unset($api->position[0][$nick]);
		}
		
		if( isset($api->position[1][$nick]) )
		{
			unset($api->position[1][$nick]);
		}
	}


	/**
	 * @internal chat flag.
	 *
	 * @param    PlayerChatEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled TRUE
	 */
	function onChat( PlayerChatEvent $event )
	{
		$player = $event->getPlayer();
		
		if( $this->isFlagDenied($player, 'chat') )
		{
			$event->setCancelled();
		}
	}


	/**
	 * @internal interact flag.
	 *
	 * @param    PlayerInteractEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
    function onTouch( PlayerInteractEvent $event )
    {
        if( $event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK )
        {
            return; // thx Yexeed.
        }

        if( $event->isCancelled() )
        {
            return;
        }

        $player = $event->getPlayer();
        $block  = $event->getBlock();
        $nick   = strtolower($player->getName());
        $api    = $this->api;

        if( $block->getId() == Block::SIGN_POST or $block->getId() == Block::WALL_SIGN )
        {
            if ($api->getValue('allow_sell', 'config') === FALSE )
            {
                return;
            }

            $api->getProvider()->buyRegionAt($block, $player);
            return;
        }

        $item = $event->getItem();

        if( $item->getId() == Item::STICK && $player->getLevel()->getName() === "world" )
        {
            $event->setCancelled();

            $region = $api->getRegion($block);

            if( !isset($region) )
            {
                $api->sendWarning($player, $api->getValue('rg_not_exist'));
                return;
            }

            $msg = str_replace('{region}', $region->getRegionName(), $api->getValue('rg_info'));
            $msg = str_replace('{owner}',  $region->getOwner(), $msg);
            $msg = str_replace('{member}', implode(' ', $region->getMemberList()), $msg);

            $api->sendWarning($player, "§fРЕГИОН: §c" .$region->getRegionName() . "§f | §fВЛАДЕЛЕЦ: §c" . $region->getOwner());
            if(implode(' ', $region->getMemberList()) !== "") {
                $player->sendMessage("\n" . $msg . "\n");
            } else $player->sendMessage("\n" . str_replace('{region}', $region->getRegionName(), $api->getValue('rg_infonotmember')) . "\n");
            return;
        }

        if( $item->getId() == Item::WOODEN_AXE && $player->getLevel()->getName() === "world" )
        {
            $event->setCancelled();

            $region = $api->getRegion($block);
            //регион в регионе
            if( $region !== NULL and !$player->hasPermission('sexguard.all') )
            {
                if( $region->getOwner() != $nick )
                {
                    $api->sendWarning($player, $api->getValue('rg_override'));
                    return;
                }
                // Region within a region
                /*
                 * else {
                    $api->sendWarning($player, $api->getValue('rg_overrideyour'));
                    return;
                }
                */
            }

            if( !isset($api->position[0][$nick]) )
            {
                $api->position[0][$nick] = $block;

                $api->sendWarning($player, $api->getValue('pos_1_setw'));
                return;
            }

            if( !isset($api->position[1][$nick]) )
            {
                	if( $api->position[0][$nick]->getLevel()->getName() != $block->getLevel()->getName())
                		{
                			unset($api->position[0][$nick]);
                				$api->sendWarning($player, $api->getValue('pos_another_world'));
                				return;
                			}

                $val  = $api->getGroupValue($player);
                $size = $api->calculateSize($api->position[0][$nick], $block);

                if( $size > $val['max_size'] and !$player->hasPermission('sexguard.all') )
                {
                    $msg = str_replace('{max_size}', $val['max_size'], $api->getValue('rg_oversize'));

                    $api->sendWarning($player, $msg);
                    return;
                }

                $api->position[1][$nick] = $block;

                $api->sendWarning($player, $api->getValue('pos_2_setw'));
                return;
            }

            if( isset($api->position[0][$nick]) and isset($api->position[1][$nick]) )
            {
                $api->position[0][$nick] = $block;

                unset($api->position[1][$nick]);
                $api->sendWarning($player, $api->getValue('pos_1_set'));
                return;
            }
        }

        $flag = 'interact';

        if( $block->getId() == Block::CHEST or $block->getId() == Block::TRAPPED_CHEST or $block->getId() == Block::FURNACE)
        {
            $flag = 'chest';
        }

        if( $block->getId() == Block::ITEM_FRAME_BLOCK )
        {
            $flag = 'frame';
        }

        if( $block->getId() == Block::GRASS )
        {
            if($event->getItem()->isShovel() or $event->getItem()->isHoe()  or $event->getItem()->getId() === 351 and $event->getItem()->getDamage() === 0x0F){
                $flag = 'break';
            }




        }

        if($block->getId() === 96)
        {
            $flag = 'break';
        }

        if( $this->isFlagDenied($player, $flag, $block) )
        {
            $event->setCancelled();
        }
    }


	/**
	 * @internal drop flag.
	 *
	 * @param    PlayerDropItemEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onDrop( PlayerDropItemEvent $event )
	{
		if( $event->isCancelled() )
		{
			return;
		}
		
		$player = $event->getPlayer();
		
		if( $this->isFlagDenied($player, 'drop') )
		{
			$event->setCancelled();
		}
	}


	/**
	 * @todo     check sleep flag for conflicts with interact.
	 *
	 * @internal sleep flag.
	 *
	 * @param    PlayerBedEnterEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onSleep( PlayerBedEnterEvent $event )
	{
		if( $event->isCancelled() )
		{
			return;
		}
		
		$player = $event->getPlayer();
		
		if( $this->isFlagDenied($player, 'sleep') )
		{
			$event->setCancelled();
		}
	}


	/**
	 * @internal bucket flag.
	 *
	 * @param    PlayerBucketFillEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onBucketFill( PlayerBucketFillEvent $event )
	{
		if( $event->isCancelled() )
		{
			return;
		}
		
		$player = $event->getPlayer();
		$block  = $event->getBlockClicked();
		
		if( $this->isFlagDenied($player, 'bucket', $block) )
		{
			$event->setCancelled();
		}
	}


	/**
	 * @internal bucket flag.
	 *
	 * @param    PlayerBucketEmptyEvent $event
	 *
	 * @priority        NORMAL
	 * @ignoreCancelled FALSE
	 */
	function onBucketEmpty( PlayerBucketEmptyEvent $event )
	{
		if( $event->isCancelled() )
		{
			return;
		}
		
		$player = $event->getPlayer();
		$block  = $event->getBlockClicked();
		
		if( $this->isFlagDenied($player, 'bucket', $block) )
		{
			$event->setCancelled();
		}
	}


	/**
	 * @param  Player $player
	 * @param  string $flag
	 *
	 * @return bool
	 */
	private function isFlagDenied( Player $player, string $flag, Block $block = NULL ): bool
	{
		if( $player->hasPermission('sexguard.noflag') )
		{
			return FALSE;
		}

		$api    = $this->api;
		$region = $api->getRegion($block ?? $player);
		
		if( !isset($region) )
		{
			return FALSE;
		}

		if( $region->getFlagValue($flag) )
		{
			return FALSE;
		}

		$val = $api->getGroupValue($player);
		
		if( in_array($flag, $val['ignored_flag']) )
		{
			if( !in_array($region->getRegionName(), $val['ignored_region']) )
			{
				$event = new FlagIgnoreEvent($api, $region, $flag, $player);

				$api->getServer()->getPluginManager()->callEvent($event);

				if( $event->isCancelled() )
				{
					return $event->isMainEventCancelled();
				}

				return FALSE;
			}
		}
		
		$nick = strtolower($player->getName());
		
		if( $nick != $region->getOwner() )
		{
			if( !in_array($nick, $region->getMemberList()) )
			{
				$event = new FlagCheckByPlayerEvent($api, $region, $flag, $player, $block);

				$api->getServer()->getPluginManager()->callEvent($event);

				if( $event->isCancelled() )
				{
					return $event->isMainEventCancelled();
				}

				$api->sendWarning($player, $api->getValue('warn_flag_'.$flag));
				return TRUE;
			}
		}
		
		return FALSE;
	}
}