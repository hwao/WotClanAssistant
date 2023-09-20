<?php

namespace hwao\WotClanTools\Command;

use hwao\WotClanTools\Command\GetWotClanRecruitRecommendedAccountsCommand\Account;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SendWotClanInviteCommand implements iCommand
{
    public function __construct(
        private Account             $account,
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
        $url = 'https://eu.wargaming.net/clans/wot/recruitstation/api/invites/';

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                "X-Requested-With" => "XMLHttpRequest",
                'Cookie' => new \Symfony\Component\HttpFoundation\Cookie('sessionid', $this->clanMemberSessionId, strtotime('+1 day')),
            ],
            'json' => [
                'comment' => $this->getComment($this->account->languages),
                'recipient_id' => $this->account->id,
                'source' => "clans_portal.recruitstation"
            ],
        ]);

        $response_json = json_decode($response->getContent(), true);

        if ($response_json['invitation']['status'] == 'active') {
            $this->log->info('Successful sent invitate to ' . $this->account->id);

            return true;
        }

        $this->log->error('invite error: ' . json_encode($response_json));
        return false;

    }

    private function getComment(array $languages): string
    {
        if (in_array('uk', $languages)) {
            return 'Ласкаво просимо до клану V. Ми щодня випускаємо резерв бойових платежів 10 рівня';
        }

        if (in_array('ru', $languages)) {
            return 'Добро пожаловать в клан V. Мы выпускаем резервы боевых выплат 10 уровня каждый день';
        }

        if (in_array('de', $languages)) {
            return 'Willkommen im V-Clan. Wir geben täglich Kampfzahlungsreserven der Stufe 10 frei';
        }

        if (in_array('pl', $languages)) {
            return 'Witamy w klanie V. Codziennie uruchamiamy rezerwy płatności bojowych poziomu 10';
        }

        return 'Welcome to the V Clan. We release battle payment reserves level 10 every day';
    }
}