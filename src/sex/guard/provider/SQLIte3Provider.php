<?php


namespace sex\guard\provider;


use pocketmine\block\Block;
use pocketmine\Player;
use sex\guard\data\Region;
use sex\guard\Manager;

class SQLIte3Provider
{

    private $api;

    /** @var \SQLite3 */
    private $sign, $region;


    public function __construct(Manager $manager)
    {
        $this->api = $manager;

        $folder = $manager->getDataFolder();
        $this->sign = new \SQLite3($folder . 'signs.db');
        $this->sign->exec("PRAGMA synchronous = OFF;");
        $this->sign->exec("CREATE TABLE IF NOT EXISTS signs (region text not null, x int not null, y int not null, z int not null, world text not null, price int not null)");
        $this->sign->exec("CREATE UNIQUE INDEX IF NOT EXISTS name_unq ON signs (region);");
        $this->sign->exec("CREATE UNIQUE INDEX IF NOT EXISTS xyz ON signs (x, y, z, world);");

        $this->region = new \SQLite3($folder . "regions.db");
        $this->region->exec("PRAGMA synchronous = OFF;");
        $this->region->exec("CREATE TABLE IF NOT EXISTS regions (region text not null, minx int not null, miny int not null, minz int not null, maxx int not null, maxy int not null, maxz int not null, world text not null, owner text not null, createdat int not null default 0)");

        $kek = $this->region->query("SELECT * FROM regions LIMIT 1");
        if ($kek && ($r = $kek->fetchArray(SQLITE3_ASSOC)) !== false) {
            if (!isset($r['createdat'])) {
                $this->region->exec("ALTER TABLE regions ADD COLUMN createdat integer default 0;");
            }
        } else {
            $this->region->exec("DROP TABLE regions;");
            $this->region->exec("CREATE TABLE IF NOT EXISTS regions (region text not null, minx int not null, miny int not null, minz int not null, maxx int not null, maxy int not null, maxz int not null, world text not null, owner text not null, createdat int not null default 0)");
        }

        $this->region->exec("CREATE TABLE IF NOT EXISTS flags (region text not null, flag text not null, value int not null)");
        $this->region->exec("CREATE TABLE IF NOT EXISTS members (region text not null, member text not null)");
        $this->region->exec("CREATE UNIQUE INDEX IF NOT EXISTS regions_idx ON regions (region);");
        $this->region->exec("CREATE UNIQUE INDEX IF NOT EXISTS members_idx ON members (region, member);");
        $this->region->exec("CREATE UNIQUE INDEX IF NOT EXISTS flags_idx ON flags (region, flag);");
    }

    public function close()
    {
        $this->sign->close();
        $this->region->close();
    }

    public function migrateSigns(array $signs)
    {
        $stmt = $this->sign->prepare("INSERT INTO signs (region, x, y, z, world, price) VALUES (:region, :x, :y, :z, :world, :price)");
        foreach ($signs as $region => $data) {
            $stmt->bindValue(":region", $region, SQLITE3_TEXT);
            $stmt->bindValue(":x", $data['pos'][0], SQLITE3_INTEGER);
            $stmt->bindValue(":y", $data['pos'][1], SQLITE3_INTEGER);
            $stmt->bindValue(":z", $data['pos'][2], SQLITE3_INTEGER);
            $stmt->bindValue(":world", $data['level'], SQLITE3_TEXT);
            $stmt->bindValue(":price", (int)$data['price'], SQLITE3_INTEGER);

            $stmt->execute();
        }
    }

    public function buyRegionAt(Block $block, Player $player)
    {
        $stmt = $this->sign->prepare("SELECT * FROM signs WHERE x = :x AND y = :y AND z = :z AND world = :world;");
        $stmt->bindValue(":x", $block->x, SQLITE3_INTEGER);
        $stmt->bindValue(":y", $block->y, SQLITE3_INTEGER);
        $stmt->bindValue(":z", $block->z, SQLITE3_INTEGER);
        $stmt->bindValue(":world", $block->getLevel()->getName(), SQLITE3_TEXT);

        $res = $stmt->execute();

        if ($res && ($rs = $res->fetchArray(SQLITE3_ASSOC)) !== false) {
            if (isset($this->api->extension['economyapi'])) {
                $economy = $this->api->extension['economyapi'];
                $money = $economy->myMoney($player->getName());
            }

            if (isset($this->api->extension['universalmoney'])) {
                $economy = $this->api->extension['universalmoney'];
                $money = $economy->getMoney($player->getName());
            }

            if (!isset($economy, $money)) {
                return false;
            }


            $price = (int)$rs['price'];
            $region = $this->api->getRegionByName((string)$rs['region']);
            if ($region === null) {
                return false;
            }
            $nick = strtolower($player->getName());
            if ($nick === $region->getOwner()) {
                $this->api->sendWarning($player, $this->api->getValue('player_already_owner'));
                return false;
            }
            $val = $this->api->getGroupValue($player);

            if (count($this->api->getRegionList($nick)) > $val['max_count']) {
                $this->api->sendWarning($player, str_replace('{max_count}', $val['max_count'], $this->api->getValue('rg_overcount')));
                return false;
            }

            if ($money < $price) {
                $this->api->sendWarning($player, str_replace('{price}', $price, $this->api->getValue('player_have_not_money')));
                return false;
            }

            $economy->reduceMoney($nick, $price);
            $economy->addMoney($region->getOwner(), $price);

            $region->setOwner($nick);
            /** @noinspection NullPointerExceptionInspection */
            $block->getLevel()->setBlock($block, Block::get(Block::AIR));

            $stmt = $this->sign->prepare("DELETE FROM signs WHERE region = :region;");
            $stmt->bindValue(":region", $region->getRegionName(), SQLITE3_TEXT);
            $stmt->execute();

            $this->api->sendWarning($player, str_replace('{region}', $region->getRegionName(), $this->api->getValue('player_buy_rg')));
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function tryPlaceSign(string $rname, Block $block, int $price)
    {
        $stmt = $this->sign->prepare("SELECT * FROM signs WHERE region = :region;");
        $stmt->bindValue(":region", $rname, SQLITE3_TEXT);
        $res = $stmt->execute();

        if ($res && ($sign = $res->fetchArray(SQLITE3_ASSOC)) !== false) {
            var_dump($sign);
            if ($sign['x'] != $block->getFloorX() || $sign['y'] != $block->getFloorY() || $sign['z'] != $block->getFloorZ()) {
                return false;
            }
            $stmt->close();

            $this->breakSignOn($block);
        }
        $stmt = $this->sign->prepare("INSERT INTO signs (region, x, y, z, world, price) VALUES (:region, :x, :y, :z, :world, :price)");
        $stmt->bindValue(":region", $rname, SQLITE3_TEXT);
        $stmt->bindValue(":x", $block->x, SQLITE3_INTEGER);
        $stmt->bindValue(":y", $block->y, SQLITE3_INTEGER);
        $stmt->bindValue(":z", $block->z, SQLITE3_INTEGER);
        $stmt->bindValue(":world", $block->getLevel()->getName(), SQLITE3_TEXT);
        $stmt->bindValue(":price", $price, SQLITE3_INTEGER);
        $stmt->execute();
        return true;
    }

    public function breakSignOn(Block $block)
    {
        $stmt = $this->sign->prepare("DELETE FROM signs WHERE x = :x AND y = :y AND z = :z AND world = :world;");
        $stmt->bindValue(":x", $block->x, SQLITE3_INTEGER);
        $stmt->bindValue(":y", $block->y, SQLITE3_INTEGER);
        $stmt->bindValue(":z", $block->z, SQLITE3_INTEGER);
        $stmt->bindValue(":world", $block->getLevel()->getName(), SQLITE3_TEXT);
        $stmt->execute();
    }

    public function migrateRegions(array $regions)
    {
        $insertStmt = $this->region->prepare("INSERT INTO regions (region, minx, miny, minz, maxx, maxy, maxz, world, owner, createdat) VALUES (:region, :minx, :miny, :minz, :maxx, :maxy, :maxz, :world, :owner, strftime('%s', 'now'))");
        $flagStmt = $this->region->prepare("INSERT INTO flags (region, flag, value) VALUES (:region, :flag, :value)");
        $membersStmt = $this->region->prepare("INSERT INTO members (region, member) VALUES (:region, :member)");
        foreach ($regions as $region => $data) {
            $this->region->exec("BEGIN TRANSACTION;");

            $insertStmt->bindValue(":region", $region, SQLITE3_TEXT);
            $insertStmt->bindValue(":minx", $data['min']['x'], SQLITE3_INTEGER);
            $insertStmt->bindValue(":miny", $data['min']['y'], SQLITE3_INTEGER);
            $insertStmt->bindValue(":minz", $data['min']['z'], SQLITE3_INTEGER);
            $insertStmt->bindValue(":maxx", $data['max']['x'], SQLITE3_INTEGER);
            $insertStmt->bindValue(":maxy", $data['max']['y'], SQLITE3_INTEGER);
            $insertStmt->bindValue(":maxz", $data['max']['z'], SQLITE3_INTEGER);
            $insertStmt->bindValue(":world", $data['level'], SQLITE3_TEXT);
            $insertStmt->bindValue(":owner", $data['owner'], SQLITE3_TEXT);
            $insertStmt->execute();

            foreach ($data['flag'] as $flag => $value) {
                $flagStmt->bindValue(":region", $region, SQLITE3_TEXT);
                $flagStmt->bindValue(":flag", $flag, SQLITE3_TEXT);
                $flagStmt->bindValue(":value", $value ? 1 : 0, SQLITE3_INTEGER);
                $flagStmt->execute();
            }

            foreach ($data['member'] as $member) {
                $membersStmt->bindValue(":region", $region, SQLITE3_TEXT);
                $membersStmt->bindValue(":member", $member, SQLITE3_TEXT);
                $membersStmt->execute();
            }


            $this->region->exec("COMMIT;");
        }
    }

    public function saveRegion(Region $region)
    {
        $insertStmt = $this->region->prepare("REPLACE INTO regions (region, minx, miny, minz, maxx, maxy, maxz, world, owner, createdat) VALUES (:region, :minx, :miny, :minz, :maxx, :maxy, :maxz, :world, :owner, :createdat)");
        $flagStmt = $this->region->prepare("REPLACE INTO flags (region, flag, value) VALUES (:region, :flag, :value)");
        $membersStmt = $this->region->prepare("INSERT INTO members (region, member) VALUES (:region, :member)");
        $this->region->exec("BEGIN TRANSACTION;");
        $stmt = $this->region->prepare("DELETE FROM members WHERE region = :region;");
        $stmt->bindValue(":region", $region->getRegionName(), SQLITE3_TEXT);
        $stmt->execute();

        $insertStmt->bindValue(":region", $region->getRegionName(), SQLITE3_TEXT);
        $insertStmt->bindValue(":minx", $region->getMin('x'), SQLITE3_INTEGER);
        $insertStmt->bindValue(":miny", $region->getMin('y'), SQLITE3_INTEGER);
        $insertStmt->bindValue(":minz", $region->getMin('z'), SQLITE3_INTEGER);
        $insertStmt->bindValue(":maxx", $region->getMax('x'), SQLITE3_INTEGER);
        $insertStmt->bindValue(":maxy", $region->getMax('y'), SQLITE3_INTEGER);
        $insertStmt->bindValue(":maxz", $region->getMax('z'), SQLITE3_INTEGER);
        $insertStmt->bindValue(":world", $region->getLevelName(), SQLITE3_TEXT);
        $insertStmt->bindValue(":owner", $region->getOwner(), SQLITE3_TEXT);
        $insertStmt->bindValue(":createdat", $region->toData()['createdat'], SQLITE3_INTEGER);
        $insertStmt->execute();

        foreach ($region->toData()['flag'] as $flag => $value) {
            $flagStmt->bindValue(":region", $region->getRegionName(), SQLITE3_TEXT);
            $flagStmt->bindValue(":flag", $flag, SQLITE3_TEXT);
            $flagStmt->bindValue(":value", $value ? 1 : 0, SQLITE3_INTEGER);
            $flagStmt->execute();
        }

        foreach ($region->getMemberList() as $member) {
            $membersStmt->bindValue(":region", $region->getRegionName(), SQLITE3_TEXT);
            $membersStmt->bindValue(":member", $member, SQLITE3_TEXT);
            $membersStmt->execute();
        }

        $this->region->exec("COMMIT;");
    }

    public function getAllRegions()
    {
        $res = $this->region->query("SELECT * FROM regions ORDER BY createdat ASC;");
        $regions = [];
        while ($rs = $res->fetchArray(SQLITE3_ASSOC)) {
            $flagsStmt = $this->region->prepare("SELECT flag, value FROM flags WHERE region = :region;");
            $flagsStmt->bindValue(":region", $rs['region'], SQLITE3_TEXT);
            $flagsRs = $flagsStmt->execute();

            $flags = [];
            while ($flag = $flagsRs->fetchArray(SQLITE3_ASSOC)) {
                $flags[$flag['flag']] = (int)$flag['value'] === 1;
            }

            $membersStmt = $this->region->prepare("SELECT member FROM members WHERE region = :region;");
            $membersStmt->bindValue(":region", $rs['region'], SQLITE3_TEXT);
            $membersRs = $membersStmt->execute();
            $members = [];
            while ($member = $membersRs->fetchArray(SQLITE3_ASSOC)) {
                $members[] = $member['member'];
            }

            $regions[] = new Region($rs['region'], [
                'owner' => $rs['owner'],
                'level' => $rs['world'],
                'min' => ['x' => $rs['minx'], 'y' => $rs['miny'], 'z' => $rs['minz']],
                'max' => ['x' => $rs['maxx'], 'y' => $rs['maxy'], 'z' => $rs['maxz']],
                'flag' => $flags,
                'member' => $members,
                'createdat' => $rs['createdat']
            ]);
        }

        return $regions;
    }

    public function removeRegion($region)
    {
        $tables = ['regions', 'flags', 'members'];
        foreach ($tables as $table) {
            $stmt = $this->region->prepare("DELETE FROM $table WHERE region = :region;");
            $stmt->bindValue(":region", $region, SQLITE3_TEXT);
            $stmt->execute();
        }
    }
}