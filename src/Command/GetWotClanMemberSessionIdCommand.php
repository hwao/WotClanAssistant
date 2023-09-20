<?php

namespace hwao\WotClanTools\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Psr\Log\LoggerInterface;

class GetWotClanMemberSessionIdCommand implements iCommand
{
    private string $sessionId;

    public function __construct(
        private int             $clanId,
        private string          $memberLogin,
        private string          $memberPassword,
        private RemoteWebDriver $webDriver,
        private LoggerInterface $log
    )
    {
    }

    public function execute()
    {
        $sessionId = $this->getSessionIdFromWeb();

        if ($sessionId === null) {
            $this->log->error('No session ID');

            throw new \RuntimeException('Cos poszlo nie tak, moze login i haslo?');
        }

        $this->sessionId = $sessionId;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    private function getSessionIdFromWeb()
    {
        $this->log->info('Open clan players as logout user');

        $this->webDriver->get(sprintf(
                'https://eu.wargaming.net/clans/wot/%d/players/#players&offset=0&limit=25&order=-role&timeframe=all&battle_type=default',
                $this->clanId)
        );

        $this->webDriver->wait(5);
        sleep(5);

// Go from clan to login
        {
            $loginButton = $this->webDriver->findElement(
                \Facebook\WebDriver\WebDriverBy::className('cm-link__register')
            );
            $loginButton->click();
            $this->log->info('Clan players list: Click login button - go to login page');
        }

        $this->webDriver->wait(1);
        sleep(1);

// Cookie Consent popup
        {
            $historyButton = $this->webDriver->findElement(
                \Facebook\WebDriver\WebDriverBy::cssSelector('#onetrust-accept-btn-handler')
            );
            $historyButton->click();
            $this->log->info('Login page: close accept cookie popup');
        }

// login
        {
            $this->webDriver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('#id_login'))
                ->sendKeys($this->memberLogin);

            $this->webDriver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('#id_password'))
                ->sendKeys($this->memberPassword)
                ->submit();

            $this->log->info('Login page: fill login form and submit');
        }

        sleep(5);

//        $this->$this->webDriver->takeScreenshot(__DIR__ . '/test.jpg');

        foreach ($this->webDriver->manage()->getCookies() as $cookie) {
            if ($cookie->getName() == 'sessionid') {
                $this->log->info('Clan page as loged: find session');
                return $cookie->getValue();
            }

//            var_dump($cookie->getName() . ' => ' . $cookie->getValue());
        }

        $this->log->warning('Clan page as logged: coundnt find session');

        $this->webDriver->quit();

        return null;
    }


}