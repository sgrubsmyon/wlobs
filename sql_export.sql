USE kasse;

SELECT
DISTINCT lieferant_name, a.artikel_nr, a.artikel_name, a.vk_preis, mwst.mwst_satz, pfandartikel.vk_preis AS pfand
FROM artikel AS a
INNER JOIN lieferant USING (lieferant_id)
INNER JOIN produktgruppe USING (produktgruppen_id)
INNER JOIN mwst USING (mwst_id)
LEFT JOIN pfand USING (pfand_id)
LEFT JOIN artikel AS pfandartikel ON pfand.artikel_id = pfandartikel.artikel_id
INNER JOIN verkauf_details ON verkauf_details.artikel_id = a.artikel_id
INNER JOIN verkauf USING (rechnungs_nr)
LEFT JOIN bestellung_details ON bestellung_details.artikel_id = a.artikel_id
LEFT JOIN bestellung USING (bestell_nr)
WHERE (
  DATE(verkaufsdatum) >= '2020-01-01' OR
  DATE(bestell_datum) >= '2020-01-01'
)
AND a.aktiv = TRUE AND a.sortiment = TRUE
AND (toplevel_id = 2 OR toplevel_id = 3)
ORDER BY lieferant_name, a.artikel_nr
INTO OUTFILE '/var/lib/mysql-files/artikel.txt';
