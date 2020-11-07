USE kasse;

SELECT DISTINCT
  "LM", lieferant_name, a.artikel_nr, a.artikel_name,
  (CASE
    WHEN (p.toplevel_id = 2 AND p.sub_id = 17 AND p.subsub_id = 3) THEN "Weihnachten"
    WHEN (p.toplevel_id = 3 AND p.sub_id = 2) THEN "Getränke mit Alkohol"
    WHEN (p.toplevel_id = 3 AND p.sub_id > 2) THEN "Getränke ohne Alkohol"
    -- Take default product group name from sub-level:
    WHEN ((p.toplevel_id = 2 OR p.toplevel_id = 3) AND p.sub_id IS NOT NULL) THEN (SELECT produktgruppen_name FROM produktgruppe WHERE toplevel_id = p.toplevel_id AND sub_id = p.sub_id AND ISNULL(subsub_id))
    ELSE produktgruppen_name
  END) AS produktgruppe,
  a.vk_preis, pfandartikel.vk_preis AS pfand, mwst.mwst_satz
FROM artikel AS a
INNER JOIN lieferant USING (lieferant_id)
INNER JOIN produktgruppe AS p USING (produktgruppen_id)
INNER JOIN mwst USING (mwst_id)
LEFT JOIN pfand USING (pfand_id)
LEFT JOIN artikel AS pfandartikel ON pfand.artikel_id = pfandartikel.artikel_id
WHERE
  -- Lebensmittel und Getränke:
    -- Select all articles active and in sortiment:
  (
    (
      p.toplevel_id = 2 AND ( -- Lebensmittel
        p.sub_id != 17 -- "normale" Lebensmittel (kein Saisonprodukt)
        OR p.subsub_id = 3 -- Saisonprodukt (sub_id = 17), aber Weihnachten (subsub_id = 3)
      )
    )
    OR
    p.toplevel_id = 3 -- Getränke
  )
  AND a.sortiment = TRUE AND a.aktiv = TRUE
  AND a.variabler_preis = FALSE AND NOT ISNULL(a.vk_preis)
ORDER BY produktgruppe, REPLACE(a.artikel_name , "\"", "")
INTO OUTFILE 'artikel_lm.txt';

SELECT DISTINCT
  "KHW", lieferant_name, a.artikel_nr, a.artikel_name,
  (CASE
    WHEN (p.toplevel_id = 4 AND p.sub_id = 16 AND p.subsub_id = 2) THEN "Weihnachten"
    -- Take default product group name from sub-level:
    WHEN (p.sub_id IS NOT NULL) THEN (SELECT produktgruppen_name FROM produktgruppe WHERE toplevel_id = p.toplevel_id AND sub_id = p.sub_id AND ISNULL(subsub_id))
    ELSE produktgruppen_name
  END) AS produktgruppe,
  a.vk_preis, pfandartikel.vk_preis AS pfand, mwst.mwst_satz
FROM artikel AS a
INNER JOIN lieferant USING (lieferant_id)
INNER JOIN produktgruppe AS p USING (produktgruppen_id)
INNER JOIN mwst USING (mwst_id)
LEFT JOIN pfand USING (pfand_id)
LEFT JOIN artikel AS pfandartikel ON pfand.artikel_id = pfandartikel.artikel_id
WHERE
  -- Kunsthandwerk und Ergänzungsprodukte:
    -- Select all articles active and in sortiment:
  (
    (
      p.toplevel_id = 4 AND ( -- Kunsthandwerk
        p.sub_id != 16 -- "normales" Kunsthandwerk (kein Saisonprodukt)
        OR p.subsub_id = 2 -- Saisonprodukt (sub_id = 16), aber Weihnachten (subsub_id = 2)
      )
    )
    OR
    p.toplevel_id = 5 -- Ergänzungsprodukte
  )
  AND a.sortiment = TRUE AND a.aktiv = TRUE
  AND a.variabler_preis = FALSE AND NOT ISNULL(a.vk_preis)
ORDER BY produktgruppe, REPLACE(a.artikel_name , "\"", "")
INTO OUTFILE 'artikel_khw.txt';