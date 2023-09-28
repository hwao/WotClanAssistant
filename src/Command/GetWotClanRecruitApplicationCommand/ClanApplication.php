<?php

namespace hwao\WotClanTools\Command\GetWotClanRecruitApplicationCommand;

use hwao\WotClanTools\Command\GetWotClanRecruitRecommendedAccountsCommand\Account;

class ClanApplication
{
    public string $status = ''; // active
    public ?string $comment = ''; //
    public Account $account;
    public string $created_at = ''; // 2023-09-27T02:59:42.895
    public string $updated_at = ''; // 2023-09-27T02:59:42.895
    public string $expires_at = ''; // 2023-09-30T02:59:42.895
    public string $game = ''; // wot
    public int $id; // 1258327

    public static function createFromArray(array $input): self
    {
        $clanApplication = new self();

        $clanApplication->status = $input['status'];
        $clanApplication->comment = $input['comment'];
        $clanApplication->account = Account::createFromArray($input['account']);
        $clanApplication->created_at = $input['created_at'];
        $clanApplication->updated_at = $input['updated_at'];
        $clanApplication->expires_at = $input['expires_at'];
        $clanApplication->game = $input['game'];
        $clanApplication->id = (int)$input['id'];

        return $clanApplication;
    }
}