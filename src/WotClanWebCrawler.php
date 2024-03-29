<?php

namespace hwao\WotClanTools;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use hwao\WotClanTools\Command\GetWotClanGetPlayersOnlineListCommand;
use hwao\WotClanTools\Command\GetWotClanMemberSessionIdCommand;
use hwao\WotClanTools\Command\GetWotClanRecruitApplicationCommand;
use hwao\WotClanTools\Command\GetWotClanRecruitRecommendedAccountsCommand;
use hwao\WotClanTools\Command\SendWotClanApplicationAcceptCommand;
use Psr\Log\LoggerInterface;

class WotClanWebCrawler
{
    private RemoteWebDriver $webDriver;

    public function __construct(
        private int             $clanId,
        private string          $clanMemberLogin,
        private string          $clanMemberPassword,
        private LoggerInterface $log
    )
    {
        $this->webDriver = self::createDriverChrome();
    }

    public function getClanPlayersOnlineList(string $clanMemberSessionId): array
    {
        $wotClanGetPlayersOnlineListCommand = new GetWotClanGetPlayersOnlineListCommand(
            $this->clanId,
            $clanMemberSessionId,
            \Symfony\Component\HttpClient\HttpClient::create(),
            $this->log
        );

        $wotClanGetPlayersOnlineListCommand->execute();
        return $wotClanGetPlayersOnlineListCommand->getPlayersOnlineList();
    }

    public function getClanMemberSessionId(): string
    {
        $getWotClanMemberSessionIdCommand = new GetWotClanMemberSessionIdCommand(
            $this->clanId,
            $this->clanMemberLogin,
            $this->clanMemberPassword,
            $this->webDriver,
            $this->log
        );

        $getWotClanMemberSessionIdCommand->execute();

        return $getWotClanMemberSessionIdCommand->getSessionId();
    }

    /**
     * @return GetWotClanRecruitRecommendedAccountsCommand\Account[]
     */
    public function getClanRecruitList(string $clanMemberSessionId): array
    {
        $getWotClanRecruitRecommendedAccountsCommand = new GetWotClanRecruitRecommendedAccountsCommand(
            $clanMemberSessionId,
            \Symfony\Component\HttpClient\HttpClient::create(),
            $this->log
        );

        $getWotClanRecruitRecommendedAccountsCommand->execute();

        return $getWotClanRecruitRecommendedAccountsCommand->getRecruitList();
    }

    /**
     * @return GetWotClanRecruitApplicationCommand\ClanApplication[]
     */
    public function getClanApplicationList(string $clanMemberSessionId) : array
    {
        $getWotClanRecruitApplicationCommand = new GetWotClanRecruitApplicationCommand(
            $clanMemberSessionId,
            \Symfony\Component\HttpClient\HttpClient::create(),
            $this->log
        );

        $getWotClanRecruitApplicationCommand->execute();

        return $getWotClanRecruitApplicationCommand->getApplicationList();
    }

    public function sendInvitationToClan(GetWotClanRecruitRecommendedAccountsCommand\Account $recrut, string $clanMemberSessionId)
    {
        $sendWotClanInviteCommand = new \hwao\WotClanTools\Command\SendWotClanInviteCommand(
            $recrut,
            $clanMemberSessionId,
            \Symfony\Component\HttpClient\HttpClient::create(),
            $this->log
        );
        $sendWotClanInviteCommand->execute();
    }

    public function sendClanApplicationAccept(GetWotClanRecruitApplicationCommand\ClanApplication $application, string $clanMemberSessionId)
    {
        $sendWotClanApplicationAcceptCommand = new SendWotClanApplicationAcceptCommand(
            $application,
            $clanMemberSessionId,
            \Symfony\Component\HttpClient\HttpClient::create(),
            $this->log
        );

        $sendWotClanApplicationAcceptCommand->execute();
    }

    private static function createDriverChrome()
    {
// Create an instance of ChromeOptions:
        $chromeOptions = new \Facebook\WebDriver\Chrome\ChromeOptions();
        $chromeOptions->addArguments(
            [
//        "--no-sandbox",
                "--headless",
                "--disable-gpu",
                "--start-maximized",
                "--ignore-certificate-errors",
                "--disable-popup-blocking",
                "--incognito",
                "--lang=pl",
                "--window-size=1920,1080"

            ]
        );


// Create $capabilities and add configuration from ChromeOptions
        $capabilities = \Facebook\WebDriver\Remote\DesiredCapabilities::chrome();
        $capabilities->setCapability(\Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY_W3C, $chromeOptions);

// Start the browser with $capabilities
// A) When using RemoteWebDriver::create()
        $serverUrl = 'http://10.0.0.250:4444';
        $driver = \Facebook\WebDriver\Remote\RemoteWebDriver::create($serverUrl, $capabilities);

// B) When using ChromeDriver::start to start local Chromedriver
//        $driver = \Facebook\WebDriver\Chrome\ChromeDriver::start($capabilities);

        return $driver;
    }
}