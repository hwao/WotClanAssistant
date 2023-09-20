<?php

include_once __DIR__ . '/vendor/autoload.php';

$config = require_once __DIR__ . '/config.php';

$log = new \Monolog\Logger('v-clan');
$log->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/var/recruit.log', \Monolog\Level::Info));

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

    $recrutList = [];
    try {
        $recrutList = $wotClanWebCrawler->getClanRecruitList($clanMemberSessionId);

        var_dump( count( $recrutList ) );

        foreach ($recrutList as $recrut) {
            $wotClanWebCrawler->sendInvitationToClan(
                $recrut,
                $clanMemberSessionId
            );

            var_dump($recrut->name);
            var_dump( 'https://pl.wot-life.com/eu/player/player-'.$recrut->id.'/');
        }

        return true;
    } catch (\RuntimeException) {
        $cache->delete('clan_member_session_id');

        $log->error('Main: [' . $i . '] cant get recrut list - session problem?');
    }
}

return;