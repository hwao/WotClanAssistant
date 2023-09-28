<?php

include_once __DIR__ . '/vendor/autoload.php';

$config = require_once __DIR__ . '/config.php';

$log = new \Monolog\Logger('v-clan');
$log->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/var/run_recruit_approve.log', \Monolog\Level::Info));

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

    $applicationList = [];
    try {
        $applicationList = $wotClanWebCrawler->getClanApplicationList($clanMemberSessionId);

        var_dump(count($applicationList));

        foreach ($applicationList as $application) {
            $wotClanWebCrawler->sendClanApplicationAccept(
                $application,
                $clanMemberSessionId
            );

            var_dump($application->account->name);
            var_dump( 'https://pl.wot-life.com/eu/player/player-'.$application->account->id.'/');
        }

        return true;
    } catch (\RuntimeException) {
        $cache->delete('clan_member_session_id');

        $log->error('Main: [' . $i . '] cant get recrut list - session problem?');
    }
}

return;