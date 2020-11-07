USE d;

/* Only has to be done once for transition from old (no KHW) to new system (with KHW) */
DROP TABLE artikel;
CREATE TABLE artikel (
    typ VARCHAR(3) NOT NULL, -- either "LM" or "KHW"
    lieferant_name VARCHAR(50) NOT NULL,
    artikel_nr VARCHAR(30) NOT NULL,
    artikel_name VARCHAR(180) NOT NULL,
    produktgruppen_name VARCHAR(50) NOT NULL,
    vk_preis DECIMAL(13,2) NOT NULL,
    pfand DECIMAL(13,2) DEFAULT NULL,
    mwst_satz DECIMAL(6,5) NOT NULL,
    PRIMARY KEY (lieferant_name, artikel_nr),
    INDEX (typ), -- allow fast subsetting to select only LM or KHW
    FULLTEXT (artikel_name, artikel_nr) -- allow quick full-text search in artikel_name and artikel_nr
);
/* ********************************************************************************** */

DELETE FROM artikel;
LOAD DATA LOCAL INFILE 'artikel_lm.txt' INTO TABLE artikel;
LOAD DATA LOCAL INFILE 'artikel_khw.txt' INTO TABLE artikel;
