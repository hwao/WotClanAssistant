<?php

include_once __DIR__ . '/vendor/autoload.php';

$config = require_once __DIR__ . '/config.php';

$log = new \Monolog\Logger('v-clan');
$log->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/var/players_online.log', \Monolog\Level::Info));

$cache = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('', 0, __DIR__ . '/var/cache');

$wotClanWebCrawler = new \hwao\WotClanTools\WotClanWebCrawler(
    $config['clanId'],
    $config['clanMemberLogin'],
    $config['clanMemberPassword'],
    $log
);

for ($i = 0; $i < 3; $i++) {

    $clanMemberSessionId = $cache->get('clan_member_session_id', function () use ($wotClanWebCrawler) {
        return $wotClanWebCrawler->getClanMemberSessionId();
    });

    /** @var \hwao\WotClanTools\Command\GetWotClanGetPlayersOnlineListCommand\PlayerOnlineInfo[] $playersOnlineList */
    $playersOnlineList = [];
    try {
        $playersOnlineList = $wotClanWebCrawler->getClanPlayersOnlineList($clanMemberSessionId);

        break;
    } catch (\RuntimeException) {
        $cache->delete('clan_member_session_id');

        $log->error('Main: [' . $i . '] cant get players online status - session problem?');
    }
}

// save :)

$pdo = \hwao\WotClanTools\PgSQLPDOFactory::create($config['postgres_host'], $config['postgres_database'], $config['postgres_user'], $config['postgres_password']);

$pdo->beginTransaction();
$timeNow = date('Y-m-d H:i:s');

foreach ($playersOnlineList as $player) {
    $statement = $pdo->prepare('INSERT INTO public.player_online  (player_id, player_name, timestamp) VALUES (:player_id, :player_name, :timestamp)');

    $statement->execute([
        'player_id' => $player->id,
        'player_name' => $player->name,
        'timestamp' => $timeNow,
    ]);

}

$pdo->commit();

var_dump($playersOnlineList);
