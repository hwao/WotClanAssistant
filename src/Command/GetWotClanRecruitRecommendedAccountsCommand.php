<?php

namespace hwao\WotClanTools\Command;

use hwao\WotClanTools\Command\GetWotClanRecruitRecommendedAccountsCommand\Account;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetWotClanRecruitRecommendedAccountsCommand implements iCommand
{
    const REQUIRED_BATTLE_LIMIT_FROM_28_DAYS = 10;

    private array $recruitList = [];

    public function __construct(
        private string              $clanMemberSessionId,
        private HttpClientInterface $httpClient,
        private LoggerInterface     $log
    )
    {
    }

    public function execute()
    {
        $last = 0;


        for ($compliance_level = 0; $compliance_level <= 4; $compliance_level++) {
            $recruitList = $this->webRequestRecruitList($compliance_level);

            foreach ($recruitList as $recruit) {
                if ($recruit['statistics']['btl'] >= self::REQUIRED_BATTLE_LIMIT_FROM_28_DAYS) {
                    $account = Account::createFromArray($recruit['account']);
                    $this->recruitList[$account->id] = $account;
                }
            }

            $count = count($this->recruitList);
            $find = $count - $last;
            $last = $count;

            $this->log->debug(sprintf(
                'Find recruit on compliance_level %d: %d (from: %d)',
                $compliance_level,
                $find,
                $count
            ));
        }
    }

    public function getRecruitList(): ?array
    {
        return $this->recruitList;
    }

    /**
     * @param int $compliance_level api przyjmuje od 0 do 4 - im wyzsza liczba "tym mniej spelnia wymagania"
     */
    private function webRequestRecruitList(int $compliance_level = 0): ?array
    {
        // period=28
        $url = sprintf(
            'https://eu.wargaming.net/clans/wot/recruitstation/api/recommended_accounts/?offset=0&limit=100&period=28&battle_type=default&compliance_level=%d',
            $compliance_level
        );

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                "X-Requested-With" => "XMLHttpRequest",
                'Cookie' => new \Symfony\Component\HttpFoundation\Cookie('sessionid', $this->clanMemberSessionId, strtotime('+1 day')),
            ]
        ]);

        $response_json = json_decode($response->getContent(), true);

//        $response->getStatusCode()

        $this->log->debug('Successful get recruit list offset: ' . $compliance_level);
//        $this->log->debug('Test: ' . print_r($response_json, true));

        $result = [];
        foreach ($response_json['accounts'] as $account) {
            $result[] = $account;
        }

        return $result;
    }
}