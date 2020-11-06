USE d;

/* Only has to be done once for transition from old (no KHW) to new system (with KHW) */
DROP TABLE artikel;
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
/* ********************************************************************************** */

DELETE FROM artikel_lm;
LOAD DATA LOCAL INFILE 'artikel_lm.txt' INTO TABLE artikel_lm;

DELETE FROM artikel_khw;
LOAD DATA LOCAL INFILE 'artikel_khw.txt' INTO TABLE artikel_khw;
