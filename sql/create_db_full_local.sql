DROP USER IF EXISTS 'u'@'localhost';
CREATE USER 'u'@'localhost' IDENTIFIED BY 'p';

DROP DATABASE IF EXISTS d;
-- CREATE DATABASE d CHARACTER SET utf8 COLLATE utf8_general_ci; -- original like in Weltladenkasse
CREATE DATABASE d DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci; -- adopted from https://framagit.org/framasoft/framadate/framadate/-/wikis/Install/Database

-- War ein Griff ins Klo: (Umlaute gehen beim Einlesen kaputt, vielleicht weil
-- Input aus UTF-8-DB ist? Oder vielleicht nur für Windows geeignet?)
-- CREATE DATABASE d DEFAULT CHARACTER SET = latin1 COLLATE = latin1_german2_ci; -- modified framagit suggestion according to: https://dev.mysql.com/doc/refman/8.0/en/charset-collation-effect.html

USE d;

CREATE TABLE artikel_lm (
    lieferant_name VARCHAR(50) NOT NULL,
    artikel_nr VARCHAR(30) NOT NULL,
    artikel_name VARCHAR(180) NOT NULL,
    produktgruppen_name VARCHAR(50) NOT NULL,
    vk_preis DECIMAL(13,2) NOT NULL,
    pfand DECIMAL(13,2) DEFAULT NULL,
    mwst_satz DECIMAL(6,5) NOT NULL,
    PRIMARY KEY (lieferant_name, artikel_nr),
    INDEX (artikel_nr, artikel_name) -- allow quick search in both artikel_nr and artikel_name
);
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE artikel_khw (
    lieferant_name VARCHAR(50) NOT NULL,
    artikel_nr VARCHAR(30) NOT NULL,
    artikel_name VARCHAR(180) NOT NULL,
    produktgruppen_name VARCHAR(50) NOT NULL,
    vk_preis DECIMAL(13,2) NOT NULL,
    mwst_satz DECIMAL(6,5) NOT NULL,
    PRIMARY KEY (lieferant_name, artikel_nr),
    INDEX (artikel_nr, artikel_name) -- allow quick search in both artikel_nr and artikel_name
);

CREATE TABLE bestellung (
    bestell_nr INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    bestelldatum DATETIME NOT NULL,
--    bestaetigt BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (bestell_nr)
);

CREATE TABLE bestellung_details (
    bestell_nr INTEGER(10) UNSIGNED NOT NULL,
    position SMALLINT(5) UNSIGNED NOT NULL,
    stueckzahl SMALLINT(5) NOT NULL DEFAULT 1,
    lieferant_name VARCHAR(50) NOT NULL,
    artikel_nr VARCHAR(30) NOT NULL,
    artikel_name VARCHAR(180) NOT NULL,
    ges_preis DECIMAL(13,2) NOT NULL,
    ges_pfand DECIMAL(13,2) DEFAULT NULL,
    mwst_satz DECIMAL(6,5) NOT NULL,
    PRIMARY KEY (bestell_nr, position),
    FOREIGN KEY (bestell_nr) REFERENCES bestellung(bestell_nr)
);

-- CREATE TABLE bestellung_secret (
--     bestell_nr INTEGER(10) UNSIGNED NOT NULL,
--     name VARCHAR(50) NOT NULL,
--     email VARCHAR(72) NOT NULL,
--     adresse VARCHAR(100) DEFAULT NULL,
--     tel VARCHAR(30) DEFAULT NULL,
--     hinweise VARCHAR(500) DEFAULT NULL,
--     lieferung BOOLEAN DEFAULT FALSE,
--     secret CHAR(30) NOT NULL,
--     PRIMARY KEY (bestell_nr),
--     FOREIGN KEY (bestell_nr) REFERENCES bestellung(bestell_nr)
-- );

GRANT LOCK TABLES ON d.* TO 'u'@'localhost';
GRANT SELECT ON d.* TO 'u'@'localhost';
-- GRANT INSERT, UPDATE ON d.bestellung TO 'u'@'localhost';
GRANT INSERT ON d.bestellung TO 'u'@'localhost';
GRANT INSERT ON d.bestellung_details TO 'u'@'localhost';
-- GRANT INSERT, DELETE ON d.bestellung_secret TO 'u'@'localhost';

LOAD DATA LOCAL INFILE 'artikel_lm.txt' INTO TABLE artikel_lm;
LOAD DATA LOCAL INFILE 'artikel_khw.txt' INTO TABLE artikel_khw;

-- After DB has been created: when articles in Weltladenkasse change, export
-- again with sql_export.sql and update this DB with new article list: (as user 'root')

-- DELETE FROM artikel_lm;
-- LOAD DATA LOCAL INFILE 'artikel_lm.txt' INTO TABLE artikel_lm;

-- or:
-- Create a table only dump containing drop table instruction:
--
-- mysqldump -umyuser -p mydb artikel_lm -r artikel_lm.sql
--
-- May need to add 'USE mydb;' statement in the beginning if phpmyadmin
-- complains. Import the file with phpmyadmin into the DB.
--
