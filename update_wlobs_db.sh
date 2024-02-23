#!/bin/bash

cd /home/<user>/wlobs
sudo -u <user> git pull

mysql -e "source sql/export.sql"
mv /var/lib/mysql/kasse/artikel_lm.txt .
mv /var/lib/mysql/kasse/artikel_khw.txt .

ncftpput -R -v -u <username> -p <password> ftp.example.org /wlobs/ artikel_lm.txt artikel_khw.txt

curl https://my.domain.org/api/artikel/update_from_txt_files.php?token=abc