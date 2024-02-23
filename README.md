# Weltladen Online-Bestell-System (wlobs)

## On live production server running the Weltladenkasse DB `kasse`

### Update existing wlobs DB

#### Clone the git repo (once)

```
cd ~
git clone https://github.com/sgrubsmyon/wlobs
```

#### Update git repo

```
cd ~/wlobs
git pull
```

#### Export current data from Weltladenkasse DB `kasse`

```
cd ~/wlobs
sudo mysql -e "source sql/export.sql"
sudo mv -i /var/lib/mysql/kasse/artikel_lm.txt .
sudo mv -i /var/lib/mysql/kasse/artikel_khw.txt .
```

#### Upload txt files to the server with `ncftp`

```
ncftpput -R -v -u <username> -p <password> ftp.example.org /wlobs/ artikel_lm.txt artikel_khw.txt
```

#### Run php script on wlobs server to update the wlobs database in place

```
curl https://my.domain.org/api/artikel/update_from_txt_files.php?token=abc
```

#### Do all this in one step automatically

Run the shell script (as root)

```
/root/update_wlobs_db.sh
```

You can create a cronjob for it to be run regularly.






## Arch Linux/Manjaro

### First deployment

#### Export data from Weltladenkasse DB `kasse` on Arch Linux/Manjaro

```
sudo mysql -e "source sql/exports/export_XXX.sql"
sudo mv -i /var/lib/mysql/kasse/artikel_lm.txt .
sudo mv -i /var/lib/mysql/kasse/artikel_khw.txt .
```

#### Import data into new DB for wlobs on Arch Linux/Manjaro

```
sudo mysql -e "source sql/create_db_full_local.sql"
```

### Update of article DB in running system

#### Load recent dump from Weltladenkasse DB `kasse`

Load a recent dump of the DB running in the Weltladen into your local
development DB server.

```
mysql --local-infile -hlocalhost -ukassenadmin -p -e "source DB_Dump_kasse_XX.sql" kasse
```

#### Export data from Weltladenkasse DB `kasse`

```
sudo mysql -e "source sql/export.sql"
sudo mv -i /var/lib/mysql/kasse/artikel_lm.txt .
sudo mv -i /var/lib/mysql/kasse/artikel_khw.txt .
```

#### Put wlobs site into maintenance mode

Log into FTP server with Filezilla. Rename `index.html` to `index.production.html`
and `index.maintenance.html` to `index.html`.

Rename directory `api` to `api.deactivated`.

#### Download current version of the wlobs DB

Log into your hoster's DB management system and export the current DB as SQL file `d_old.sql`.
(Important: Click 'export' on start page of phpmyadmin, don't click on the DB first, otherwise
only the tables are exported and the `CREATE DATABASE` statement is missing.)

#### Import the downloaded DB into localhost

```
sudo mysql
> DROP DATABASE d;
> Ctrl-D
sudo mysql -e "source d_old.sql"
```

#### Update wlobs DB with new data locally

```
sudo mysql -e "source sql/update_article_table.sql"
```

#### Create dump of updated wlobs DB

```
sudo mysqldump --databases d --add-drop-database -r d_new.sql
```

#### Upload new version of the wlobs DB

Log into your hoster's DB management system and import the new DB SQL file `d_new.sql`.

#### Put wlobs site back into production mode

Log into FTP server with Filezilla. Rename `index.html` back to `index.maintenance.html`
and `index.production.html` back to `index.html`.

Rename directory `api.deactivated` back to `api`.














## Outdated: Ubuntu 16.04

### First deployment

#### Outdated: Export data from Weltladenkasse DB `kasse` on Ubuntu 16.04

```
mysql -hlocalhost -uroot -p -e "source sql/exports/export_XXX.sql"
sudo mv -i /var/lib/mysql-files/artikel_lm.txt .
sudo mv -i /var/lib/mysql-files/artikel_khw.txt .
```

#### Outdated: Import data into new DB for wlobs on Ubuntu 16.04

```
mysql -hlocalhost -uroot -p -e "source sql/create_db_full_local.sql"
```

### Outdated: Update of article DB in running system

#### Outdated: Export data from Weltladenkasse DB `kasse`

```
sudo rm /var/lib/mysql-files/artikel_lm.txt
sudo rm /var/lib/mysql-files/artikel_khw.txt
mysql -hlocalhost -uroot -p -e "source sql/exports/export_XXX.sql"
sudo cp -i /var/lib/mysql-files/artikel_lm.txt .
sudo cp -i /var/lib/mysql-files/artikel_khw.txt .
```

#### Outdated: Update wlobs DB with new data

```
mysql -hlocalhost -uroot -p -e "source sql/update_article_table.sql"
```








## Deploy locally for testing

```
sudo rsync -rtlPvi --delete --exclude=.*.sw* deploy/* /var/www/html/wlobs/ && sudo chown -R http:http /var/www/html/wlobs/
```

Need to start the `nginx` and `php-fpm` system services before visiting http://localhost/wlobs.
