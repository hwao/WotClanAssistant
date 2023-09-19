# WOT Clan Assistant

Tool to help you automate boring, but important stuff
- collect information about who is online

## Requiremnt
- PHP8.1
- Working Chromedriver + Chrome browser
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
touch var/app.log
```


crontab

```bash
crontab -e

*/15 * * * * php /home/hwao/Documents/php/WotClanPlayerStatus/run.php

```