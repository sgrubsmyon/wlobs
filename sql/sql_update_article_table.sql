USE d;

DELETE FROM artikel;
LOAD DATA LOCAL INFILE 'artikel.txt' INTO TABLE artikel;
