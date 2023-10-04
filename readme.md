# WOT Clan Assistant

Tool to help you automate boring, but important stuff
- collect information about who is online

## Requiremnt
- PHP8.1
- ChromeDriver - WebDriver for Chrome
  - local working Chromedriver + Chrome browser (compatible with each other)
  - docker + selenium-hub + selenium/node-chrome
- PostgreSQL

## Install

Set timezone:

```bash
sudo timedatectl set-timezone Europe/Warsaw
```

create directory/files

```
mkdir var/
mkdir var/cache/
touch var/players_online.log 
touch var/recruit.log
touch var/run_recruit_approve.log
```


crontab

```bash
crontab -e

*/15 * * * * php /home/hwao/Documents/php/WotClanPlayerStatus/run_players_online.php
*/20 * * * * php /home/hwao/Documents/php/WotClanPlayerStatus/run_recruit.php
*/10 * * * * php /home/hwao/Documents/php/WotClanPlayerStatus/run_recruit_approve.php

```

## Chrome drive

```dockerfile

version: "3"
services:
  chrome:
    image: selenium/node-chrome:4.13.0-20231004
    shm_size: 2gb
    depends_on:
      - selenium-hub
    environment:
      - SE_EVENT_BUS_HOST=selenium-hub
      - SE_EVENT_BUS_PUBLISH_PORT=4442
      - SE_EVENT_BUS_SUBSCRIBE_PORT=4443

#  edge:
#    image: selenium/node-edge:4.13.0-20231004
#    shm_size: 2gb
#    depends_on:
#      - selenium-hub
#    environment:
#      - SE_EVENT_BUS_HOST=selenium-hub
#      - SE_EVENT_BUS_PUBLISH_PORT=4442
#      - SE_EVENT_BUS_SUBSCRIBE_PORT=4443
#
#  firefox:
#    image: selenium/node-firefox:4.13.0-20231004
#    shm_size: 2gb
#    depends_on:
#      - selenium-hub
#    environment:
#      - SE_EVENT_BUS_HOST=selenium-hub
#      - SE_EVENT_BUS_PUBLISH_PORT=4442
#      - SE_EVENT_BUS_SUBSCRIBE_PORT=4443

  selenium-hub:
    image: selenium/hub:4.13.0-20231004
    container_name: selenium-hub
    ports:
      - "4442:4442"
      - "4443:4443"
      - "4444:4444"
      
      
```