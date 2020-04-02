USE kasse;

SELECT DISTINCT
  lieferant_name, a.artikel_nr, a.artikel_name,
  (CASE
    WHEN (toplevel_id = 2 AND sub_id = 17 AND lieferant_name = "Bantam") THEN "Saatgut"
    WHEN (toplevel_id = 2 AND sub_id = 17 AND (lieferant_name = "GEPA" OR lieferant_name = "WeltPartner" OR lieferant_name = "Ethiquable")) THEN "Ostern"
    WHEN (toplevel_id = 2 OR toplevel_id = 3) THEN produktgruppen_name
    ELSE "Kosmetik, Hygiene und Haushalt"
  END) AS produktgruppe,
  a.vk_preis, pfandartikel.vk_preis AS pfand, mwst.mwst_satz
FROM artikel AS a
INNER JOIN lieferant USING (lieferant_id)
INNER JOIN produktgruppe USING (produktgruppen_id)
INNER JOIN mwst USING (mwst_id)
LEFT JOIN pfand USING (pfand_id)
LEFT JOIN artikel AS pfandartikel ON pfand.artikel_id = pfandartikel.artikel_id
WHERE (
  (
    -- Lebensmittel und Getränke:
      -- first select all articles active and in sortiment:
    (toplevel_id = 2 OR toplevel_id = 3)
    AND a.sortiment = TRUE AND a.aktiv = TRUE
      -- then restrict to those whose artikel_nr (not artikel_id!) was in any
      -- recent verkauf or bestellung:
    AND (
      a.artikel_nr IN (
        SELECT DISTINCT artikel_nr FROM artikel
        INNER JOIN verkauf_details USING (artikel_id)
        INNER JOIN verkauf USING (rechnungs_nr)
        WHERE DATE(verkaufsdatum) >= '2020-01-01'
      ) OR a.artikel_nr IN (
        SELECT DISTINCT artikel_nr FROM artikel
        INNER JOIN bestellung_details USING (artikel_id)
        INNER JOIN bestellung USING (bestell_nr)
        WHERE DATE(bestell_datum) >= '2020-01-01'
      )
    )
  ) OR (
    -- Extra Lebensmittel: alle Saisonartikel von Bantam/Bingenheimer im Sortiment (Saatgut)
    toplevel_id = 2 AND sub_id = 17 AND lieferant_name = "Bantam"
    AND a.sortiment = TRUE
  ) OR (
    -- Extra Lebensmittel: alle Saisonartikel von GEPA, wp, Ethiquable (Ostern)
    toplevel_id = 2 AND sub_id = 17 AND
    (lieferant_name = "GEPA" OR lieferant_name = "WeltPartner" OR lieferant_name = "Ethiquable")
    AND a.sortiment = TRUE
  ) OR (
    -- 4.14 Kosmetik:
    toplevel_id = 4 AND sub_id = 14
    AND a.sortiment = TRUE
  ) OR (
    -- 5 Ergänzungsprodukte:
    toplevel_id = 5
    AND a.sortiment = TRUE
  ) OR
  -- Sonstiges Kunsthandwerk:
  a.artikel_nr = "7365301" OR
  a.artikel_nr = "7365302" OR
  a.artikel_nr = "801-8201" OR
  a.artikel_nr = "801-8202" OR
  a.artikel_nr = "801-8203" OR
  a.artikel_nr = "801-8220" OR
  a.artikel_nr = "801-8221" OR
  a.artikel_nr = "801-8222" OR
  a.artikel_nr = "801-8223" OR
  a.artikel_nr = "801-8301" OR
  a.artikel_nr = "806-8001" OR
  -- Sonstiges Papier:
  a.artikel_nr = "608575020" OR
  a.artikel_nr = "K1643" OR
  a.artikel_nr = "S2344" OR
  a.artikel_nr = "S2346" OR
  a.artikel_nr = "S2348" OR
  a.artikel_nr = "sl9-30-001"
) AND a.aktiv = TRUE AND a.variabler_preis = FALSE
ORDER BY lieferant_name, a.artikel_nr
INTO OUTFILE '/var/lib/mysql-files/artikel.txt';
