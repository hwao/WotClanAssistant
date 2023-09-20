<?php

namespace hwao\WotClanTools\Command\GetWotClanRecruitRecommendedAccountsCommand;

class Account
{
    public int $id; // 34423432121
    public string $name; // "Kislev_1"
    public array $languages = []; // ["de", "en"]
//    public int $last_battle_time;
//    public array $primetime = []; // {to: 75600, from: 61200}

    public static function createFromArray(array $input): self
    {
        $account = new self();
        $account->id = $input['id'];
        $account->name = $input['name'];
        $account->languages = $input['languages'];

        return $account;
    }
}