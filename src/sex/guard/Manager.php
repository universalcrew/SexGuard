<?php namespace sex\guard;


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
use sex\guard\data\Region;

use sex\guard\listener\BlockGuard;
use sex\guard\listener\EntityGuard;
use sex\guard\listener\PlayerGuard;

use sex\guard\command\OldGuardCommand;
use sex\guard\command\NewGuardCommand;

use sex\guard\event\region\RegionCreateEvent;
use sex\guard\event\region\RegionRemoveEvent;

use pocketmine\permission\Permission;
use pocketmine\plugin\PluginBase;
use pocketmine\level\Position;
use pocketmine\utils\Config;
use pocketmine\Player;

/**
 * @todo throw exceptions in production isn't a good practice.
 *       add config autoupdater.
 */
use Exception;
use sex\guard\provider\SQLIte3Provider;


/**
 * @todo implement 'manager -> provider' pattern.
 *       convert arrays to containers.
 */
class Manager extends PluginBase
{
    const CONFIGURATION_SIGN = '5bfa52e5eed0d57dee1f33cd435eb988';

    const DEFAULT_FLAG = [
        'interact' => TRUE,
        'teleport' => TRUE,
        'combust'  => FALSE,
        'explode'  => FALSE,
        'change'   => FALSE,
        'bucket'   => FALSE,
        'damage'   => TRUE,
        'chest'    => FALSE,
        'frame'    => FALSE,
        'place'    => FALSE,
        'break'    => FALSE,
        'sleep'    => FALSE,
        'decay'    => TRUE,
        'drop'     => TRUE,
        'chat'     => TRUE,
        'pvp'      => FALSE,
        'mob'      => TRUE
    ];


    /**
     * @var Manager
     */
    private static $instance = null;


    /**
     * @return Manager
     */
    static function getInstance( ): Manager
    {
        return self::$instance;
    }


    /**
     * @var Config
     */
    private $message, $config, $group;

    /**
     * @todo better store $data in provider.
     *
     * @var Region[][]
     */
    private $data = [];

    /**
     * @var Position[]
     */
    public $position = [];

    /**
     * @var PluginBase[]
     */
    public $extension = [];

    /** @var SQLIte3Provider */
    private $provider;

    function onLoad( )
    {
        $this->loadInstance();
    }


    function onEnable( )
    {
        $this->initPermission();
        $this->initProvider();

        if( $this->getValue('sign', 'config') !== self::CONFIGURATION_SIGN )
        {
            throw new Exception("Configuration error: использование старой версии конфига. Пожалуйста, удалите старый конфиг (/plugins/sexGuard/config.yml) и перезагрузите сервер.");
        }

        $this->initListener();
        $this->initCommand();
        $this->initExtension();
    }


    /**
     * @todo data stores can be deleted by user.
     */
    function onDisable( )
    {
        $this->provider->close();
    }


    /**
     *              _
     *   __ _ ____ (_)
     *  / _' |  _ \| |
     * | (_) | (_) | |
     *  \__,_|  __/|_|
     *       |_|
     *
     *
     * @param  Position $min
     * @param  Position $max
     *
     * @return Region[]
     */
    function getOverride( Position $min, Position $max ): array
    {
        $level = $min->getLevel()->getName();

        if( $level != $max->getLevel()->getName() )
        {
            return [];
        }

        if( !isset($this->data[$level]) )
        {
            return [];
        }

        $data = $this->data[$level];

        if( count($data) == 0 )
        {
            unset($data); return [];
        }

        $arr = [];

        foreach( $data as $rg )
        {
            if(
                $rg->getMin('x') <= $max->getX() and $min->getX() <= $rg->getMax('x') and
                $rg->getMin('y') <= $max->getY() and $min->getY() <= $rg->getMax('y') and
                $rg->getMin('z') <= $max->getZ() and $min->getZ() <= $rg->getMax('z')
            ) {
                $arr[] = $rg;
            }
        }

        unset($data); return $arr;
    }


    /**
     * @param  Position $pos
     *
     * @return Region | NULL
     */
    function getRegion( Position $pos )
    {
        $level = $pos->getLevel()->getName();

        if( !isset($this->data[$level]) )
        {
            return NULL;
        }

        $data = $this->data[$level];

        if( count($data) == 0 )
        {
            unset($data); return NULL;
        }

        $x = $pos->getFloorX();
        $y = $pos->getFloorY();
        $z = $pos->getFloorZ();

        /** @var Region $region */
        $region = null;
        for($i = count($data) - 1; $i >= 0; $i-- ) // for sqlite
        {
            if(!isset($data[$i])) continue;


            $rg = $data[$i];

            if(
                $rg->getMin('x') <= $x and $x <= $rg->getMax('x') and
                $rg->getMin('y') <= $y and $y <= $rg->getMax('y') and
                $rg->getMin('z') <= $z and $z <= $rg->getMax('z') and
                ($region === null || $region->toData()['createdat'] < $rg->toData()['createdat'])
            ) {
                $region = $rg;
            }
        }

        unset($data); return $region;
    }


    /**
     * @param  string $name
     *
     * @return Region | NULL
     */
    function getRegionByName( string $name )
    {
        $name = strtolower($name);

        foreach( $this->getServer()->getLevels() as $level )
        {
            $level = $level->getName();

            if( !isset($this->data[$level]) )
            {
                continue;
            }

            $data = $this->data[$level];

            if( count($data) == 0 )
            {
                unset($data); continue;
            }

            foreach( $data as $rg )
            {
                if( $rg->getRegionName() != $name )
                {
                    continue;
                }

                unset($data); return $rg;
            }
        }

        unset($data); return NULL;
    }


    /**
     * @param  string   $nick
     * @param  string   $name
     * @param  Position $min
     * @param  Position $max
     */
    function createRegion( string $nick, string $name, Position $min, Position $max )
    {
        $level = $min->getLevel()->getName();
        $nick  = strtolower($nick);
        $name  = strtolower($name);

        if( $this->getValue('full_height', 'config') === TRUE )
        {
            $min_y = 0;
            $max_y = 256;
        }

        $data = [
            'owner'  => $nick,
            'member' => [],
            'level'  => $level,
            'min'    => [
                'x' => $min->getX(),
                'y' => $min_y ?? $min->getY(),
                'z' => $min->getZ()
            ],
            'max'    => [
                'x' => $max->getX(),
                'y' => $max_y ?? $max->getY(),
                'z' => $max->getZ()
            ],
            'flag'   => $this->getValue('allowed_flag', 'config'),
            'createdat' => time()
        ];

        $region = new Region($name, $data);
        $event  = new RegionCreateEvent($this, $region);

        $this->getServer()->getPluginManager()->callEvent($event);

        if( $event->isCancelled() )
        {
            return;
        }

        $this->data[$level][] = $region;

        unset($this->position[0][$nick]);
        unset($this->position[1][$nick]);
        $this->saveRegion($region);
    }


    /**
     * @param  string $name
     *
     * @return bool
     */
    function removeRegion( string $name ): bool
    {
        $name = strtolower($name);

        foreach( $this->getServer()->getLevels() as $level )
        {
            $level = $level->getName();

            if( !isset($this->data[$level]) )
            {
                continue;
            }

            $data = $this->data[$level];

            if( count($data) == 0 )
            {
                unset($data); continue;
            }

            foreach( $data as $key => $rg )
            {
                if( $rg->getRegionName() != $name )
                {
                    continue;
                }

                $event = new RegionRemoveEvent($this, $rg);

                $this->getServer()->getPluginManager()->callEvent($event);

                if( $event->isCancelled() )
                {
                    return FALSE;
                }

                unset($this->data[$level][$key]);

                $this->data[$level] = array_values($this->data[$level]);

                $this->provider->removeRegion($name);

                unset($data);
                return TRUE;
            }
        }

        unset($data); return FALSE;
    }


    /**
     * @param  string $nick
     * @param  bool   $include_member
     *
     * @return Region[]
     */
    function getRegionList( string $nick, bool $include_member = false ): array
    {
        $nick = strtolower($nick);
        $arr  = [];

        foreach( $this->getServer()->getLevels() as $level )
        {
            $level = $level->getName();

            if( !isset($this->data[$level]) )
            {
                continue;
            }

            $data = $this->data[$level];

            if( count($data) == 0 )
            {
                unset($data); continue;
            }

            foreach( $data as $rg )
            {
                if( $rg->getOwner() == $nick )
                {
                    $arr[] = $rg;

                    continue;
                }

                if( !$include_member )
                {
                    continue;
                }

                if( !in_array($nick, $rg->getMemberList()) )
                {
                    continue;
                }

                $arr[] = $rg;
            }
        }

        unset($data); return $arr;
    }


    /**
     * @todo   rewrite this piece of shit.
     *
     * @param  string $type
     * @param  string $key
     *
     * @return string | int | NULL
     */
    function getValue( string $key, string $type = 'message' )
    {
        $type  = strtolower($type);
        $key   = mb_strtolower($key);
        $error = "Configuration error: пункт '$key' не найден в $type.yml. Пожалуйста, удалите старый конфиг (/plugins/sexGuard/$type.yml) и перезагрузите сервер.";

        if( $type == 'config' )
        {
            $value = $this->config->get($key, 'жопа');

            if( $value === 'жопа' )
            {
                throw new Exception($error);
            }
        }

        elseif( $type == 'group' )
        {
            $value = $this->group->get($key);
            $value = !$value ? $this->group->get('default') : $value;

            if( !$value )
            {
                $this->getLogger()->error($error);

                $value = [
                    'max_size'       => 5000,
                    'max_count'      => 4,
                    'ignored_flag'   => [],
                    'ignored_region' => []
                ];
            }
        }

        else
        {
            $value = $this->message->get($key);

            if( $value === FALSE )
            {
                $this->getLogger()->error($error);

                $value = "§l§c- §fGUARD §c- Произошла внутренняя ошибка§r";
            }
        }

        return $value;
    }


    /**
     * @param Region $region
     */
    function saveRegion( Region $region )
    {
        $this->provider->saveRegion($region);
    }


    /**
     * @param  Position $pos1
     * @param  Position $pos2
     *
     * @return int
     */
    function calculateSize( Position $pos1, Position $pos2 ): int
    {
        $x = [ min($pos1->getX(), $pos2->getX()), max($pos1->getX(), $pos2->getX()) ];
        $y = [ min($pos1->getY(), $pos2->getY()), max($pos1->getY(), $pos2->getY()) ];
        $z = [ min($pos1->getZ(), $pos2->getZ()), max($pos1->getZ(), $pos2->getZ()) ];

        if( $this->getValue('full_height', 'config') === TRUE )
        {
            $y = [ 0, 1 ];
        }

        return ($x[1] - $x[0]) * ($y[1] - $y[0]) * ($z[1] - $z[0]);
    }


    /**
     * @return string[]
     */
    function getAllowedFlag( ): array
    {
        $flag = array_map('strtolower', array_keys($this->getValue('allowed_flag', 'config')));

        foreach( $flag as $key )
        {
            if( isset(self::DEFAULT_FLAG[$key]) )
            {
                continue;
            }

            unset($flag[$key]);
        }

        return $flag;
    }


    /**
     * @param Player $player
     * @param string $message
     */
    function sendWarning( Player $player, string $message )
    {
        if( empty($message) )
        {
            return;
        }

        switch( $this->getValue('warn_type', 'config') )
        {
            case 0:  $player->sendPopup($message);   break;
            case 1:  $player->sendMessage($message); break;
            default: $player->sendTip($message);     break;
        }
    }


    /**
     * @todo   better manage permissions for size control.
     *
     * @param  Player $player
     *
     * @return int[]
     */
    function getGroupValue( Player $player ): array
    {
        if( isset($this->extension['pureperms']) )
        {
            $group = $this->extension['pureperms']->getUserDataMgr()->getGroup($player)->getName();
        }

        elseif( isset($this->extension['universalgroup']) )
        {
            $group = $this->extension['universalgroup']->getGroup($player->getName())->getId();
        }

        elseif( isset($this->extension['sexgroup']) )
        {
            $group = $this->extension['sexgroup']->getPlayerGroup($player->getName())->getId();
        }

        return $this->getValue($group ?? 'default', 'group');
    }


    /**
     *  _                 _
     * | | ___   __ _  __| |
     * | |/ _ \ / _' |/ _' |
     * | | (_) | (_) | (_) |
     * |_|\___/ \__,_|\__,_|
     *
     */
    private function loadInstance( )
    {
        self::$instance = $this;
    }


    private function initPermission( )
    {
        $list = [
            new Permission('sexguard.noflag', 'Игнорирование флагов внутри регионов', Permission::DEFAULT_OP),
            new Permission('sexguard.all', 'Доступ ко всем функциям sexGuard', Permission::DEFAULT_OP)
        ];

        foreach( $list as $permission )
        {
            $this->getServer()->getPluginManager()->addPermission($permission);
        }
    }



    public function getProvider() {
        return $this->provider;
    }

    private function initProvider( )
    {
        $folder = $this->getDataFolder();

        if (!is_dir($folder)) {
            @mkdir($folder);
        }

        $this->saveResource('group.yml');
        $this->saveResource('config.yml');
        $this->saveResource('message.yml');

        $this->group = new Config($folder . 'group.yml');
        $this->config = new Config($folder . 'config.yml');
        $this->message = new Config($folder . 'message.yml');

        $this->provider = new SQLIte3Provider($this);

        // json migration
        if (file_exists($folder . 'sign.json')) {
            $signs = json_decode(file_get_contents($folder . 'sign.json'), true);
            $this->provider->migrateSigns($signs);
            rename($folder . 'sign.json', $folder . 'sign.old.json');
        }

        if (file_exists($folder . 'region.json')) {
            $regions = json_decode(file_get_contents($folder . 'region.json'), true);
            $this->provider->migrateRegions($regions);
            rename($folder . 'region.json', $folder . 'region.old.json');
        }


        foreach ($this->provider->getAllRegions() as $rg) {
            $level = $rg->getLevelName();

            $this->data[$level][] = $rg;
        }
    }


    private function initListener( )
    {
        $listener = [
            new PlayerGuard($this),
            new BlockGuard($this),
            new EntityGuard($this)
        ];

        foreach( $listener as $class )
        {
            $this->getServer()->getPluginManager()->registerEvents($class, $this);
        }
    }


    private function initCommand( )
    {
        try
        {
            $command = new OldGuardCommand($this);
        }

        catch( Exception $exception )
        {
            $command = new NewGuardCommand($this);
        }

        $map     = $this->getServer()->getCommandMap();
        $replace = $map->getCommand($command->getName());

        if( isset($replace) )
        {
            $replace->setLabel('');
            $replace->unregister($map);
        }

        $map->register($this->getName(), $command);
    }


    private function initExtension( )
    {
        $list = [
            'PurePerms',
            'EconomyAPI',
            'UniversalGroup',
            'UniversalMoney',
            'SexGroup'
        ];

        foreach( $list as $extension )
        {
            if( $this->getValue('allow_'. strtolower($extension), 'config') === TRUE )
            {
                $plugin = $this->getServer()->getPluginManager()->getPlugin($extension);

                if( isset($plugin) )
                {
                    $this->extension[strtolower($extension)] = $plugin;
                }
            }
        }
    }
}