<?php

namespace hwao\WotClanTools\Command;

use hwao\WotClanTools\Command\GetWotClanRecruitApplicationCommand\ClanApplication;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SendWotClanApplicationAcceptCommand implements iCommand
{
    public function __construct(
        private ClanApplication     $application,
        private string              $clanMemberSessionId,
        private HttpClientInterface $httpClient,
        private LoggerInterface     $log
    )
    {
    }

    public function execute()
    {
        $this->sendWebRequestInvite();
    }

    private function sendWebRequestInvite()
    {
        $url = sprintf(
            'https://eu.wargaming.net/clans/wot/recruitstation/api/applications/%d/',
            $this->application->id
        );

        $response = $this->httpClient->request('PATCH', $url, [
            'headers' => [
                "X-Requested-With" => "XMLHttpRequest",
                'Cookie' => new \Symfony\Component\HttpFoundation\Cookie('sessionid', $this->clanMemberSessionId, strtotime('+1 day')),
            ],
            'json' => [
                'status' => 'accepted',
            ],
        ]);

        $response_json = json_decode($response->getContent(), true);

        if ($response_json['application']['status'] == 'accepted') {
            $this->log->info('Clan Application Accept - Successful added to clan ' . $this->application->account->id);

            return true;
        }

        $this->log->error('Clan Application Accept - error: ' . json_encode($response_json));
        return false;
    }
}