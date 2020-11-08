# Weltladen Online-Bestell-System (wlobs)

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

#### Export data from Weltladenkasse DB `kasse`

```
sudo mysql -e "source sql/exports/export_XXX.sql"
sudo mv -i /var/lib/mysql/kasse/artikel_lm.txt .
sudo mv -i /var/lib/mysql/kasse/artikel_khw.txt .
```

#### Update wlobs DB with new data

```
sudo mysql -e "source sql/update_article_table.sql"
```

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
