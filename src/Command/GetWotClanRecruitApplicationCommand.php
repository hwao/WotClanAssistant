<?php

namespace hwao\WotClanTools\Command;

use hwao\WotClanTools\Command\GetWotClanRecruitApplicationCommand\ClanApplication;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetWotClanRecruitApplicationCommand implements iCommand
{
    private array $applicationList = [];

    public function __construct(
        private string              $clanMemberSessionId,
        private HttpClientInterface $httpClient,
        private LoggerInterface     $log
    )
    {
    }

    public function execute()
    {
        $applicationList = $this->webRequestClanApplicationList();
        foreach ($applicationList as $application) {

            $clanApplication = ClanApplication::createFromArray($application);
            $this->applicationList[] = $clanApplication;

        }

        $count = count($this->applicationList);

        $this->log->debug(sprintf(
            'Find Clan Application: %d',
            $count
        ));
    }

    public function getApplicationList(): array
    {
        return $this->applicationList;
    }

    private function webRequestClanApplicationList(): ?array
    {
        $url = 'https://eu.wargaming.net/clans/wot/recruitstation/api/active_clan_applications/?offset=0&limit=10&period=all&battle_type=default&order=-rr';

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                "X-Requested-With" => "XMLHttpRequest",
                'Cookie' => new \Symfony\Component\HttpFoundation\Cookie('sessionid', $this->clanMemberSessionId, strtotime('+1 day')),
            ]
        ]);

        $response_json = json_decode($response->getContent(), true);

//        $response->getStatusCode()

        $this->log->debug('Successful get clan application list');
//        $this->log->debug('Test: ' . print_r($response_json, true));

        $result = [];
        foreach ($response_json['applications'] as $application) {
            $result[] = $application;
        }

        return $result;
    }
}