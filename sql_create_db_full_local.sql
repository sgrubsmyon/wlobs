DROP USER IF EXISTS 'm1444db4'@'localhost';
CREATE USER 'm1444db4'@'localhost' IDENTIFIED BY 'p';

DROP DATABASE IF EXISTS m1444db4;
-- CREATE DATABASE m1444db4 CHARACTER SET utf8 COLLATE utf8_general_ci; -- original like in Weltladenkasse
CREATE DATABASE m1444db4 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci; -- adopted from https://framagit.org/framasoft/framadate/framadate/-/wikis/Install/Database

USE m1444db4;

CREATE TABLE artikel (
    lieferant_name VARCHAR(50) NOT NULL,
    artikel_nr VARCHAR(30) NOT NULL,
    artikel_name VARCHAR(180) NOT NULL,
    produktgruppen_name VARCHAR(50) NOT NULL,
    vk_preis DECIMAL(13,2) NOT NULL,
    pfand DECIMAL(13,2) DEFAULT NULL,
    mwst_satz DECIMAL(6,5) NOT NULL,
    PRIMARY KEY (lieferant_name, artikel_nr)
);
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
--     email VARCHAR(100) NOT NULL,
--     adresse VARCHAR(100) DEFAULT NULL,
--     tel VARCHAR(30) DEFAULT NULL,
--     hinweise VARCHAR(500) DEFAULT NULL,
--     lieferung BOOLEAN DEFAULT FALSE,
--     secret CHAR(30) NOT NULL,
--     PRIMARY KEY (bestell_nr),
--     FOREIGN KEY (bestell_nr) REFERENCES bestellung(bestell_nr)
-- );

GRANT LOCK TABLES ON m1444db4.* TO 'm1444db4'@'localhost';
GRANT SELECT ON m1444db4.* TO 'm1444db4'@'localhost';
-- GRANT INSERT, UPDATE ON m1444db4.bestellung TO 'm1444db4'@'localhost';
GRANT INSERT ON m1444db4.bestellung TO 'm1444db4'@'localhost';
GRANT INSERT ON m1444db4.bestellung_details TO 'm1444db4'@'localhost';
-- GRANT INSERT, DELETE ON m1444db4.bestellung_secret TO 'm1444db4'@'localhost';

LOAD DATA LOCAL INFILE 'artikel.txt' INTO TABLE artikel;

-- After DB has been created: when articles in Weltladenkasse change, export
-- again with sql_export.sql and update this DB with new article list: (as user 'root')

-- DELETE FROM artikel;
-- LOAD DATA LOCAL INFILE 'artikel.txt' INTO TABLE artikel;
