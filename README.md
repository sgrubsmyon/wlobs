# Weltladen Online-Bestell-System (wlobs)

## First deployment

### Export data from Weltladenkasse DB `kasse` on Arch Linux/Manjaro

```
sudo mysql -e "source sql/exports/export_XXX.sql"
sudo mv -i /var/lib/mysql/kasse/artikel.txt .
```

### Import data into new DB for wlobs

```
mysql -hlocalhost -uroot -p -e "source sql/create_db_full_local.sql"
```

### Outdated: Export data from Weltladenkasse DB `kasse` on Ubuntu 16.04

```
mysql -hlocalhost -uroot -p -e "source sql/exports/export_XXX.sql"
sudo mv -i /var/lib/mysql-files/artikel.txt .
```

## Update of article DB in running system

### Export data from Weltladenkasse DB `kasse`

```
sudo rm /var/lib/mysql-files/artikel.txt
mysql -hlocalhost -uroot -p -e "source sql/exports/export_XXX.sql"
sudo cp -i /var/lib/mysql-files/artikel.txt .
```

### Update wlobs DB with new data

```
mysql -hlocalhost -uroot -p -e "source sql/update_article_table.sql"
```

## Deploy locally for testing

```
sudo rsync -rtlPvin --delete --exclude=.*.sw* deploy/* /var/www/html/wlobs/; sudo chown -R www-data:www-data /var/www/html/wlobs/
```

