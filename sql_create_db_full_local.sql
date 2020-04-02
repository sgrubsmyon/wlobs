DROP USER IF EXISTS 'm1444db4'@'localhost';
CREATE USER 'm1444db4'@'localhost' IDENTIFIED BY 'p';

DROP DATABASE IF EXISTS m1444db4;
CREATE DATABASE m1444db4 CHARACTER SET utf8 COLLATE utf8_general_ci;

USE m1444db4;

CREATE TABLE artikel (
    lieferant_name VARCHAR(50) NOT NULL,
    artikel_nr VARCHAR(30) NOT NULL,
    artikel_name VARCHAR(180) NOT NULL,
    produktgruppen_name VARCHAR(50) NOT NULL,
    vk_preis DECIMAL(13,2) DEFAULT NULL,
    pfand DECIMAL(13,2) DEFAULT NULL,
    mwst_satz DECIMAL(6,5) NOT NULL,
    PRIMARY KEY (lieferant_name, artikel_nr)
) DEFAULT CHARSET=utf8;
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE bestellung (
    bestell_nr INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    bestelldatum DATETIME NOT NULL,
    PRIMARY KEY (bestell_nr)
) DEFAULT CHARSET=utf8;

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
) DEFAULT CHARSET=utf8;

GRANT LOCK TABLES ON m1444db4.* TO 'm1444db4'@'localhost';
GRANT SELECT ON m1444db4.* TO 'm1444db4'@'localhost';
GRANT INSERT ON m1444db4.bestellung TO 'm1444db4'@'localhost';
GRANT INSERT ON m1444db4.bestellung_details TO 'm1444db4'@'localhost';

LOAD DATA LOCAL INFILE 'artikel.txt' INTO TABLE artikel;

-- After DB has been created, update it with new article list: (as user 'root')

-- DELETE FROM artikel;
-- LOAD DATA LOCAL INFILE 'artikel.txt' INTO TABLE artikel;
