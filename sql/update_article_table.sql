USE d;

/* ********************************************************************************** */
/* This only has to be done once for transition from old (no KHW) to new system (with KHW) */
-- DROP TABLE artikel;
-- CREATE TABLE artikel (
--     typ VARCHAR(3) NOT NULL, -- either "LM" or "KHW"
--     sortiment BOOLEAN NOT NULL DEFAULT FALSE,
--     lieferant_name VARCHAR(50) NOT NULL,
--     artikel_nr VARCHAR(30) NOT NULL,
--     artikel_name VARCHAR(180) NOT NULL,
--     produktgruppen_name VARCHAR(50) NOT NULL,
--     vk_preis DECIMAL(13,2) NOT NULL,
--     pfand DECIMAL(13,2) DEFAULT NULL,
--     mwst_satz DECIMAL(6,5) NOT NULL,
--     PRIMARY KEY (lieferant_name, artikel_nr),
--     INDEX (typ), -- allow fast subsetting to select only LM or KHW
--     INDEX (sortiment), -- allow fast subsetting to select only Sortimentsartikel
--     FULLTEXT (artikel_name, artikel_nr) -- allow quick full-text search in artikel_name and artikel_nr
-- );
-- -- Create a temporary copy of the bestellung_details table:
-- CREATE TABLE bestellung_details2 (
--     bestell_nr INTEGER(10) UNSIGNED NOT NULL,
--     position SMALLINT(5) UNSIGNED NOT NULL,
--     stueckzahl SMALLINT(5) NOT NULL DEFAULT 1,
--     lieferant_name VARCHAR(50) NOT NULL,
--     artikel_nr VARCHAR(30) NOT NULL,
--     artikel_name VARCHAR(180) NOT NULL,
--     ges_preis DECIMAL(13,2) NOT NULL,
--     ges_pfand DECIMAL(13,2) DEFAULT NULL,
--     mwst_satz DECIMAL(6,5) NOT NULL,
--     PRIMARY KEY (bestell_nr, position),
--     FOREIGN KEY (bestell_nr) REFERENCES bestellung(bestell_nr)
-- );
-- INSERT INTO bestellung_details2
-- (bestell_nr, position, stueckzahl, lieferant_name, artikel_nr,
-- artikel_name, ges_preis, ges_pfand, mwst_satz)
-- SELECT * FROM bestellung_details;
-- -- Delete the old bestellung_details table:
-- DROP TABLE bestellung_details;
-- -- Create the table in new format:
-- CREATE TABLE bestellung_details (
--     bestell_nr INTEGER(10) UNSIGNED NOT NULL,
--     position SMALLINT(5) UNSIGNED NOT NULL,
--     stueckzahl SMALLINT(5) NOT NULL DEFAULT 1,
--     typ VARCHAR(3) NOT NULL, -- either "LM" or "KHW"
--     sortiment BOOLEAN NOT NULL DEFAULT FALSE,
--     lieferant_name VARCHAR(50) NOT NULL,
--     artikel_nr VARCHAR(30) NOT NULL,
--     artikel_name VARCHAR(180) NOT NULL,
--     ges_preis DECIMAL(13,2) NOT NULL,
--     ges_pfand DECIMAL(13,2) DEFAULT NULL,
--     mwst_satz DECIMAL(6,5) NOT NULL,
--     PRIMARY KEY (bestell_nr, position),
--     FOREIGN KEY (bestell_nr) REFERENCES bestellung(bestell_nr)
-- );
-- -- Copy data from old table into new: (all articles so far have been of type LM)
-- INSERT INTO bestellung_details
-- (bestell_nr, position, stueckzahl, typ, sortiment, lieferant_name, artikel_nr,
-- artikel_name, ges_preis, ges_pfand, mwst_satz)
-- SELECT
-- bestell_nr, position, stueckzahl, 'LM', TRUE, lieferant_name, artikel_nr,
-- artikel_name, ges_preis, ges_pfand, mwst_satz
-- FROM bestellung_details2;
-- -- Delete temporary table:
-- DROP TABLE bestellung_details2;
/* ********************************************************************************** */

/* ********************************************************************************** */
/* This only has to be done once for transition to extension of artikel table with menge, einheit, herkunft */
-- DROP TABLE artikel;
-- CREATE TABLE artikel (
--     typ VARCHAR(3) NOT NULL, -- either "LM" or "KHW"
--     sortiment BOOLEAN NOT NULL DEFAULT FALSE,
--     lieferant_name VARCHAR(50) NOT NULL,
--     artikel_nr VARCHAR(30) NOT NULL,
--     artikel_name VARCHAR(180) NOT NULL,
--     produktgruppen_name VARCHAR(50) NOT NULL,
--     vk_preis DECIMAL(13,2) NOT NULL,
--     pfand DECIMAL(13,2) DEFAULT NULL,
--     mwst_satz DECIMAL(6,5) NOT NULL,
--     menge DECIMAL(8,5) DEFAULT NULL,
--     einheit VARCHAR(10) DEFAULT NULL,
--     herkunft VARCHAR(100) DEFAULT NULL,
--     PRIMARY KEY (lieferant_name, artikel_nr),
--     INDEX (typ), -- allow fast subsetting to select only LM or KHW
--     INDEX (sortiment), -- allow fast subsetting to select only Sortimentsartikel
--     FULLTEXT (artikel_name, artikel_nr) -- allow quick full-text search in artikel_name and artikel_nr
-- );
/* ********************************************************************************** */

DELETE FROM artikel;
LOAD DATA LOCAL INFILE 'artikel_lm.txt' INTO TABLE artikel;
LOAD DATA LOCAL INFILE 'artikel_khw.txt' INTO TABLE artikel;
