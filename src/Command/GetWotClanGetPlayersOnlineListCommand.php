<?php

namespace hwao\WotClanTools\Command;

use hwao\WotClanTools\Command\GetWotClanGetPlayersOnlineListCommand\PlayerOnlineInfo;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class GetWotClanGetPlayersOnlineListCommand implements iCommand
{
    private array $playersOnlineList = [];

    public function __construct(
        private int                 $clanId,
        private string              $clanMemberSessionId,
        private HttpClientInterface $httpClient,
        private LoggerInterface     $log
    )
    {

    }

    public function execute()
    {
        $result = $this->getOnlinePlayersList();

        if ($result === null) {
            $this->log->warning('There was no player "status online" information, propably wrong user session ');
            throw new \RuntimeException('Brak informacji czy online - prawdopodobnie brak dobrej sessionId');
        }

        $this->playersOnlineList = $result;
    }

    private function getOnlinePlayersList(): ?array
    {
        $url = sprintf('https://eu.wargaming.net/clans/wot/%d/api/players/?offset=0&limit=25&order=-role&timeframe=all&battle_type=default',
            $this->clanId
        );

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                "X-Requested-With" => "XMLHttpRequest",
                'Cookie' => new \Symfony\Component\HttpFoundation\Cookie('sessionid', $this->clanMemberSessionId, strtotime('+1 day')),
            ]
        ]);

        $response_json = json_decode($response->getContent(), true);

        $this->log->info('Successful get clan players list');

        $onlinePlayerList = [];
        foreach ($response_json['items'] as $player) {
            if ($player['online_status'] === null)
                return null;

            if ($player['online_status'] === true) {
                $playerOnline = new PlayerOnlineInfo();
                $playerOnline->id = $player['id'];
                $playerOnline->name = $player['name'];

                $onlinePlayerList[] = $playerOnline;

            }

        }

        return $onlinePlayerList;
    }

    public function getPlayersOnlineList(): array
    {
        return $this->playersOnlineList;
    }
}