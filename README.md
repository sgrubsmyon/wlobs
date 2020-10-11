# Weltladen Online-Bestell-System (wlobs)

## First deployment

### Export data from Weltladenkasse DB `kasse`

```
sudo rm /var/lib/mysql-files/artikel.txt
mysql -hlocalhost -uroot -p -e "source sql_export.sql"
sudo cp -i /var/lib/mysql-files/artikel.txt .
```

### Import data into new DB for wlobs

```
mysql -hlocalhost -uroot -p -e "source sql_create_db_full_local.sql"
```
