-- Madam Monica multilingual SEO data migration
-- Target: OpenCart 3.x / oc_ prefix / HR + EN + DE
-- Idempotent: safe to run more than once. Back up the database before import.

SET NAMES utf8mb4;
SET @hr := (SELECT language_id FROM oc_language WHERE code = 'hr-hr' LIMIT 1);
SET @en := (SELECT language_id FROM oc_language WHERE code = 'en-gb' LIMIT 1);
SET @de := (SELECT language_id FROM oc_language WHERE code = 'de-de' LIMIT 1);

-- Store and structured-data defaults.
UPDATE oc_setting SET value = 'Madam Monica | Dizajnerske haljine i sako-kaputići'
WHERE store_id = 0 AND `key` = 'config_meta_title';
UPDATE oc_setting SET value = 'Otkrijte ženske haljine, sako-kaputiće, bluze i statement komade Madam Monica. Profinjeni krojevi, posebni materijali i sigurna online kupnja.'
WHERE store_id = 0 AND `key` = 'config_meta_description';
UPDATE oc_setting SET value = 'Madam Monica'
WHERE store_id = 0 AND `key` = 'hb_snippets_brand';
UPDATE oc_setting SET value = '0'
WHERE store_id = 0 AND `key` IN ('hb_snippets_kg_enable', 'hb_snippets_search_enable');
UPDATE oc_setting SET value = '0'
WHERE store_id = 0 AND `key` IN ('hb_canonical_type', 'hb_canonical_type_c');
UPDATE oc_setting SET value = ''
WHERE store_id = 0 AND `key` = 'hb_snippets_pricevaliddate';
INSERT INTO oc_setting (store_id, code, `key`, value, serialized)
SELECT 0, 'module_menu_side_image', 'module_menu_side_image_category_id', '258', 0
WHERE NOT EXISTS (
  SELECT 1 FROM oc_setting WHERE store_id = 0 AND `key` = 'module_menu_side_image_category_id'
);

-- Do not publish the incomplete €1 product. Assign new dresses to Haljine.
UPDATE oc_product SET status = 0 WHERE product_id = 2654;
INSERT IGNORE INTO oc_product_to_category (product_id, category_id) VALUES (2662, 258), (2664, 258);

-- Canonical category URLs missing from the current database.
INSERT INTO oc_seo_url (store_id, language_id, `query`, keyword) VALUES
(0, @hr, 'category_id=256', 'sako-jakne'),
(0, @en, 'category_id=256', 'coat'),
(0, @de, 'category_id=256', 'mantel'),
(0, @hr, 'category_id=258', 'haljine'),
(0, @en, 'category_id=258', 'dresses'),
(0, @de, 'category_id=258', 'kleider'),
(0, @hr, 'category_id=259', 'madam-monica'),
(0, @en, 'category_id=259', 'madam-monica-259-en'),
(0, @de, 'category_id=259', 'madam-monica-259-de'),
(0, @hr, 'category_id=260', 'bluza'),
(0, @en, 'category_id=260', 'blouse'),
(0, @de, 'category_id=260', 'bluse'),
(0, @hr, 'category_id=261', 'haljine-od-zakarda'),
(0, @en, 'category_id=261', 'jacquard-dresses'),
(0, @de, 'category_id=261', 'jacquardkleider'),
(0, @hr, 'category_id=262', 'tajice'),
(0, @en, 'category_id=262', 'leggings'),
(0, @de, 'category_id=262', 'leggings-de')
ON DUPLICATE KEY UPDATE keyword = VALUES(keyword);

-- Localized contact route and missing complaint URLs.
INSERT INTO oc_seo_url (store_id, language_id, `query`, keyword) VALUES
(0, @hr, 'information/contact', 'kontaktirajte-nas'),
(0, @en, 'information/contact', 'contact'),
(0, @de, 'information/contact', 'kontakt'),
(0, @hr, 'information_id=24', 'pravo-na-podnosenje-prigovora'),
(0, @en, 'information_id=24', 'how-to-submit-a-complaint'),
(0, @de, 'information_id=24', 'schriftliche-beschwerde-einreichen')
ON DUPLICATE KEY UPDATE keyword = VALUES(keyword);

-- SEO URLs for the products that previously had no slug.
INSERT INTO oc_seo_url (store_id, language_id, `query`, keyword) VALUES
(0, @hr, 'product_id=2646', 'haljina-monroe'), (0, @en, 'product_id=2646', 'monroe-dress'), (0, @de, 'product_id=2646', 'monroe-kleid'),
(0, @hr, 'product_id=2647', 'haljina-magnolia'), (0, @en, 'product_id=2647', 'magnolia-dress'), (0, @de, 'product_id=2647', 'magnolia-kleid'),
(0, @hr, 'product_id=2648', 'haljina-divnala'), (0, @en, 'product_id=2648', 'divnala-dress'), (0, @de, 'product_id=2648', 'divnala-kleid'),
(0, @hr, 'product_id=2649', 'haljina-divna'), (0, @en, 'product_id=2649', 'divna-dress'), (0, @de, 'product_id=2649', 'divna-kleid'),
(0, @hr, 'product_id=2650', 'tirkizna-haljina'), (0, @en, 'product_id=2650', 'turquoise-dress'), (0, @de, 'product_id=2650', 'tuerkises-kleid'),
(0, @hr, 'product_id=2651', 'haljina-bluebella'), (0, @en, 'product_id=2651', 'bluebella-dress'), (0, @de, 'product_id=2651', 'bluebella-kleid'),
(0, @hr, 'product_id=2652', 'haljina-aurabella'), (0, @en, 'product_id=2652', 'aurabella-dress'), (0, @de, 'product_id=2652', 'aurabella-kleid'),
(0, @hr, 'product_id=2653', 'haljina-dottina'), (0, @en, 'product_id=2653', 'dottina-dress'), (0, @de, 'product_id=2653', 'dottina-kleid'),
(0, @hr, 'product_id=2655', 'tajice-bellona'), (0, @en, 'product_id=2655', 'bellona-leggings'), (0, @de, 'product_id=2655', 'bellona-leggings-de'),
(0, @hr, 'product_id=2656', 'haljina-linda'), (0, @en, 'product_id=2656', 'linda-dress'), (0, @de, 'product_id=2656', 'linda-kleid'),
(0, @hr, 'product_id=2657', 'haljina-betty'), (0, @en, 'product_id=2657', 'betty-dress'), (0, @de, 'product_id=2657', 'betty-kleid'),
(0, @hr, 'product_id=2658', 'haljina-coralina'), (0, @en, 'product_id=2658', 'coralina-dress'), (0, @de, 'product_id=2658', 'coralina-kleid'),
(0, @hr, 'product_id=2659', 'haljina-aquabella'), (0, @en, 'product_id=2659', 'aquabella-dress'), (0, @de, 'product_id=2659', 'aquabella-kleid'),
(0, @hr, 'product_id=2660', 'haljina-dorabella'), (0, @en, 'product_id=2660', 'dorabella-dress'), (0, @de, 'product_id=2660', 'dorabella-kleid'),
(0, @hr, 'product_id=2661', 'haljina-bordobella'), (0, @en, 'product_id=2661', 'bordobella-dress'), (0, @de, 'product_id=2661', 'bordobella-kleid'),
(0, @hr, 'product_id=2662', 'haljina-poisabella'), (0, @en, 'product_id=2662', 'poisabella-dress'), (0, @de, 'product_id=2662', 'poisabella-kleid'),
(0, @hr, 'product_id=2663', 'haljina-livia'), (0, @en, 'product_id=2663', 'livia-dress'), (0, @de, 'product_id=2663', 'livia-kleid'),
(0, @hr, 'product_id=2664', 'haljina-pinella'), (0, @en, 'product_id=2664', 'pinella-dress'), (0, @de, 'product_id=2664', 'pinella-kleid')
ON DUPLICATE KEY UPDATE keyword = VALUES(keyword);

-- Category titles, descriptions and visible copy in all active languages.
CREATE TEMPORARY TABLE mm_category_meta (
  category_id INT NOT NULL,
  language_id INT NOT NULL,
  meta_title VARCHAR(255) NOT NULL,
  meta_description VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  PRIMARY KEY (category_id, language_id)
) CHARACTER SET utf8mb4;

INSERT INTO mm_category_meta VALUES
(256,@hr,'Ženski sako-kaputići i jakne | Madam Monica','Otkrijte ženske sako-kaputiće, jakne i balonere Madam Monica u upečatljivim bojama, cvjetnim uzorcima i luksuznim žakard materijalima.','<p>Otkrijte sako-kaputiće, jakne i balonere Madam Monica oblikovane za upečatljive, ženstvene kombinacije. Kolekcija spaja strukturirane krojeve, cvjetne uzorke, pamučni saten, lan i raskošni dvostrani žakard. Provjerite sastav, dostupnu veličinu i detalje svakog modela prije naručivanja.</p>'),
(256,@en,'Women’s Blazer Coats and Jackets | Madam Monica','Discover Madam Monica women’s blazer coats, jackets and trench coats in distinctive colours, floral patterns and luxurious jacquard fabrics.','<p>Discover Madam Monica blazer coats, jackets and trench coats designed for distinctive feminine styling. The collection combines structured cuts, floral patterns, cotton sateen, linen and luxurious double-faced jacquard. Check the composition, available size and details on each product page before ordering.</p>'),
(256,@de,'Damen-Blazermäntel und Jacken | Madam Monica','Entdecken Sie Damen-Blazermäntel, Jacken und Trenchcoats von Madam Monica in besonderen Farben, Blumenmustern und luxuriösem Jacquard.','<p>Entdecken Sie Blazermäntel, Jacken und Trenchcoats von Madam Monica für ausdrucksstarke feminine Looks. Die Kollektion verbindet strukturierte Schnitte, Blumenmuster, Baumwollsatin, Leinen und luxuriösen doppelseitigen Jacquard. Beachten Sie vor der Bestellung Material, verfügbare Größe und Produktdetails.</p>'),
(258,@hr,'Ženske dizajnerske haljine | Madam Monica','Otkrijte ženstvene haljine Madam Monica: poslovne, svečane, retro i unikatne modele od viskoze, pamuka, lana, svile i žakarda.','<p>Istražite ženske haljine Madam Monica za poslovne, svečane i dnevne prilike. Kolekcija obuhvaća retro siluete, pripijene i lepršave krojeve te unikatne modele od pamuka, viskoze, lana, svile i žakarda. Na stranici svakog proizvoda pronaći ćete opis kroja, sastav, dostupne veličine, cijenu i informacije o dostavi.</p>'),
(258,@en,'Designer Dresses for Women | Madam Monica','Discover feminine Madam Monica dresses for business, occasion and everyday wear in viscose, cotton, linen, silk and jacquard.','<p>Explore Madam Monica women’s dresses for business, occasion and everyday wear. The collection includes retro silhouettes, fitted and flowing cuts and one-of-a-kind designs in cotton, viscose, linen, silk and jacquard. Each product page provides fit details, composition, available sizes, price and delivery information.</p>'),
(258,@de,'Designerkleider für Damen | Madam Monica','Entdecken Sie feminine Kleider von Madam Monica für Business, Anlässe und Alltag aus Viskose, Baumwolle, Leinen, Seide und Jacquard.','<p>Entdecken Sie Damenkleider von Madam Monica für Business, besondere Anlässe und den Alltag. Die Kollektion umfasst Retro-Silhouetten, figurbetonte und fließende Schnitte sowie Einzelstücke aus Baumwolle, Viskose, Leinen, Seide und Jacquard. Jede Produktseite zeigt Passform, Material, verfügbare Größen, Preis und Lieferinformationen.</p>'),
(259,@hr,'Madam Monica kolekcija | Ženska dizajnerska moda','Pregledajte cijelu kolekciju Madam Monica: haljine, sako-kaputiće, bluze i statement modele za profinjen, ženstven i samouvjeren stil.','<p>Pregledajte cijelu kolekciju Madam Monica na jednom mjestu. Otkrijte haljine, sako-kaputiće, bluze i statement modele naglašenih silueta, posebnih uzoraka i pažljivo odabranih materijala. Filtrirajte proizvode prema kategoriji i provjerite sastav, dostupnu veličinu te informacije o dostavi i povratu.</p>'),
(259,@en,'Madam Monica Collection | Designer Womenswear','Browse the complete Madam Monica collection of dresses, blazer coats, blouses and statement designs for a refined, feminine and confident style.','<p>Browse the complete Madam Monica collection in one place. Discover dresses, blazer coats, blouses and statement pieces with feminine silhouettes, distinctive patterns and carefully selected fabrics. Use the categories to explore products and check composition, available size, delivery and return information.</p>'),
(259,@de,'Madam Monica Kollektion | Designer-Damenmode','Entdecken Sie die gesamte Madam Monica Kollektion mit Kleidern, Blazermänteln, Blusen und Statement-Pieces für einen femininen Stil.','<p>Entdecken Sie die gesamte Madam Monica Kollektion an einem Ort. Finden Sie Kleider, Blazermäntel, Blusen und Statement-Pieces mit femininen Silhouetten, besonderen Mustern und sorgfältig ausgewählten Stoffen. Prüfen Sie bei jedem Produkt Material, verfügbare Größe sowie Liefer- und Rückgabeinformationen.</p>'),
(260,@hr,'Ženske bluze | Madam Monica','Otkrijte elegantne ženske bluze Madam Monica od profinjenih i ugodnih materijala, oblikovane za poslovne, svečane i dnevne kombinacije.','<p>Ženske bluze Madam Monica spajaju profinjene materijale, ženstvene detalje i krojeve koji se jednostavno kombiniraju sa suknjama, hlačama i sakoima. Provjerite sastav, način vezanja ili kopčanja te dostupne veličine na stranici odabranog modela.</p>'),
(260,@en,'Women’s Blouses | Madam Monica','Discover elegant Madam Monica women’s blouses in refined, comfortable fabrics for business, occasion and everyday styling.','<p>Madam Monica women’s blouses combine refined fabrics, feminine details and versatile cuts that pair with skirts, trousers and blazers. Check the composition, fastening or tie details and available sizes on the selected product page.</p>'),
(260,@de,'Damenblusen | Madam Monica','Entdecken Sie elegante Damenblusen von Madam Monica aus angenehmen, edlen Stoffen für Business, Anlässe und Alltag.','<p>Damenblusen von Madam Monica verbinden edle Stoffe, feminine Details und vielseitige Schnitte, die sich mit Röcken, Hosen und Blazern kombinieren lassen. Informationen zu Material, Bindung oder Verschluss und verfügbaren Größen finden Sie auf der jeweiligen Produktseite.</p>'),
(261,@hr,'Žakard haljine za žene | Madam Monica','Istražite elegantne ženske haljine od žakarda bogate teksture, profinjenih uzoraka i ženstvenih krojeva za posebne i svečane prilike.','<p>Haljine od žakarda Madam Monica ističu se bogatom teksturom, profinjenim uzorcima i strukturiranim ženstvenim krojevima. Otkrijte modele od dvostranog i klasičnog žakarda za svečane događaje i posebne prilike te na proizvodu provjerite sastav, dostupnu veličinu i održavanje.</p>'),
(261,@en,'Jacquard Dresses for Women | Madam Monica','Explore elegant women’s jacquard dresses with rich texture, refined patterns and feminine silhouettes for occasions and special events.','<p>Madam Monica jacquard dresses stand out through rich texture, refined patterns and structured feminine silhouettes. Discover double-faced and classic jacquard designs for occasions and special events, then check composition, available size and care details on the product page.</p>'),
(261,@de,'Jacquardkleider für Damen | Madam Monica','Entdecken Sie elegante Jacquardkleider mit reicher Textur, edlen Mustern und femininen Schnitten für Anlässe und besondere Ereignisse.','<p>Jacquardkleider von Madam Monica zeichnen sich durch reiche Texturen, edle Muster und strukturierte feminine Schnitte aus. Entdecken Sie Modelle aus doppelseitigem und klassischem Jacquard für besondere Anlässe und prüfen Sie Material, verfügbare Größe und Pflegehinweise.</p>'),
(262,@hr,'Ženske elegantne tajice | Madam Monica','Otkrijte ženske tajice Madam Monica modernog, ženstvenog kroja i statement detalja za elegantne dnevne i večernje kombinacije.','<p>Ženske tajice Madam Monica oblikovane su za moderne, ženstvene kombinacije. Elastični materijali, pripijeni krojevi i statement detalji omogućuju kombiniranje sa sakoom, elegantnim topom ili obućom s potpeticom. Provjerite sastav i dostupne veličine na stranici proizvoda.</p>'),
(262,@en,'Elegant Women’s Leggings | Madam Monica','Discover Madam Monica women’s leggings with modern feminine cuts and statement details for elegant day and evening styling.','<p>Madam Monica women’s leggings are designed for modern feminine styling. Stretch fabrics, fitted cuts and statement details pair with blazers, elegant tops and heels. Check the composition and available sizes on the product page.</p>'),
(262,@de,'Elegante Damen-Leggings | Madam Monica','Entdecken Sie Damen-Leggings von Madam Monica mit modernen femininen Schnitten und Statement-Details für Tag und Abend.','<p>Damen-Leggings von Madam Monica sind für moderne feminine Looks entworfen. Elastische Stoffe, figurbetonte Schnitte und Statement-Details lassen sich mit Blazern, eleganten Oberteilen und Absatzschuhen kombinieren. Material und verfügbare Größen finden Sie auf der Produktseite.</p>');

UPDATE oc_category_description cd
JOIN mm_category_meta mm ON mm.category_id = cd.category_id AND mm.language_id = cd.language_id
SET cd.meta_title = mm.meta_title,
    cd.meta_description = mm.meta_description,
    cd.description = mm.body,
    cd.meta_keyword = '',
    cd.h1 = cd.name,
    cd.image_alt = cd.name,
    cd.image_title = cd.name;
DROP TEMPORARY TABLE mm_category_meta;

-- Informational-page metadata in all languages.
CREATE TEMPORARY TABLE mm_information_meta (
  information_id INT NOT NULL,
  language_id INT NOT NULL,
  meta_title VARCHAR(255) NOT NULL,
  meta_description VARCHAR(255) NOT NULL,
  PRIMARY KEY (information_id, language_id)
) CHARACTER SET utf8mb4;

INSERT INTO mm_information_meta VALUES
(3,@hr,'Opći uvjeti korištenja web shopa | Madam Monica','Pročitajte uvjete korištenja i kupnje u web shopu Madam Monica, uključujući naručivanje, plaćanje, dostavu, povrat i odgovornosti stranaka.'),
(3,@en,'Online Shop Terms and Conditions | Madam Monica','Read the terms for using and purchasing from the Madam Monica online shop, including ordering, payment, delivery, returns and responsibilities.'),
(3,@de,'Allgemeine Geschäftsbedingungen | Madam Monica','Lesen Sie die Bedingungen für Nutzung und Einkauf im Madam Monica Onlineshop, einschließlich Bestellung, Zahlung, Lieferung und Rückgabe.'),
(5,@hr,'Sigurnost online plaćanja | Madam Monica','Saznajte kako Madam Monica štiti online plaćanja putem CorvusPay sustava te koje se sigurnosne mjere primjenjuju pri kartičnoj kupnji.'),
(5,@en,'Online Payment Security | Madam Monica','Learn how Madam Monica protects online payments through CorvusPay and which security measures apply when paying by card.'),
(5,@de,'Sicherheit bei Online-Zahlungen | Madam Monica','Erfahren Sie, wie Madam Monica Online-Zahlungen über CorvusPay schützt und welche Sicherheitsmaßnahmen bei Kartenzahlungen gelten.'),
(6,@hr,'Pravila privatnosti i zaštita podataka | Madam Monica','Saznajte koje osobne podatke Madam Monica prikuplja, zašto ih obrađuje, koliko ih čuva te koja prava imate prema pravilima zaštite podataka.'),
(6,@en,'Privacy and Data Protection Policy | Madam Monica','Learn which personal data Madam Monica collects, why it is processed, how long it is retained and which data protection rights you have.'),
(6,@de,'Datenschutz und Privatsphäre | Madam Monica','Erfahren Sie, welche personenbezogenen Daten Madam Monica erhebt, warum sie verarbeitet werden, wie lange sie gespeichert werden und welche Rechte Sie haben.'),
(9,@hr,'Povrat robe, zamjene i reklamacije | Madam Monica','Saznajte kako zatražiti povrat, zamjenu ili reklamaciju proizvoda kupljenog u web shopu Madam Monica te koji su rokovi, uvjeti i potrebni podaci.'),
(9,@en,'Returns, Exchanges and Complaints | Madam Monica','Learn how to request a return, exchange or complaint for products purchased from Madam Monica, including deadlines, conditions and required details.'),
(9,@de,'Rückgabe, Umtausch und Reklamation | Madam Monica','Erfahren Sie, wie Sie Rückgabe, Umtausch oder Reklamation für bei Madam Monica gekaufte Produkte beantragen und welche Fristen gelten.'),
(10,@hr,'Rješavanje pritužbi i sporova | Madam Monica','Informacije o podnošenju pritužbi i načinima mirnog rješavanja potrošačkih sporova vezanih uz kupnju u web shopu Madam Monica.'),
(10,@en,'Complaint and Dispute Resolution | Madam Monica','Information about submitting complaints and options for amicable resolution of consumer disputes related to purchases from Madam Monica.'),
(10,@de,'Beschwerde- und Streitbeilegung | Madam Monica','Informationen zur Einreichung von Beschwerden und zur außergerichtlichen Beilegung von Verbraucherstreitigkeiten bei Madam Monica.'),
(14,@hr,'Vodič za veličine ženske odjeće | Madam Monica','Provjerite tablicu veličina Madam Monica i mjere za grudi, struk i bokove kako biste lakše odabrali odgovarajuću veličinu odjevnog komada.'),
(14,@en,'Women’s Clothing Size Guide | Madam Monica','Check the Madam Monica size chart and bust, waist and hip measurements to help you select the most suitable clothing size.'),
(14,@de,'Größenratgeber für Damenmode | Madam Monica','Prüfen Sie die Größentabelle von Madam Monica mit Brust-, Taillen- und Hüftmaßen, um die passende Konfektionsgröße auszuwählen.'),
(21,@hr,'Česta pitanja o kupnji, dostavi i povratu | Madam Monica','Pronađite odgovore o naručivanju, plaćanju, GLS dostavi, rokovima isporuke, povratu, zamjenama i reklamacijama u web shopu Madam Monica.'),
(21,@en,'Shopping, Delivery and Returns FAQ | Madam Monica','Find answers about ordering, payment, GLS delivery, delivery times, returns, exchanges and complaints in the Madam Monica online shop.'),
(21,@de,'FAQ zu Einkauf, Lieferung und Rückgabe | Madam Monica','Antworten zu Bestellung, Zahlung, GLS-Lieferung, Lieferzeiten, Rückgabe, Umtausch und Reklamationen im Madam Monica Onlineshop.'),
(24,@hr,'Kako podnijeti pisani prigovor | Madam Monica','Saznajte kako kupac može podnijeti pisani prigovor društvu Madam Monica d.o.o., koje podatke treba navesti i kada može očekivati odgovor.'),
(24,@en,'How to Submit a Written Complaint | Madam Monica','Learn how to submit a written complaint to Madam Monica d.o.o., which details to include and when you can expect a response.'),
(24,@de,'Schriftliche Beschwerde einreichen | Madam Monica','Erfahren Sie, wie Sie eine schriftliche Beschwerde an Madam Monica d.o.o. senden, welche Angaben erforderlich sind und wann Sie eine Antwort erhalten.');

UPDATE oc_information_description id
JOIN mm_information_meta mm ON mm.information_id = id.information_id AND mm.language_id = id.language_id
SET id.meta_title = mm.meta_title,
    id.meta_description = mm.meta_description,
    id.meta_keyword = '';
DROP TEMPORARY TABLE mm_information_meta;

-- Correct the German size-guide title and replace the Croatian body accidentally used there.
UPDATE oc_information_description
SET title = 'Größenratgeber',
    description = '<p>Vergleichen Sie Ihre Körpermaße mit der Tabelle. Messen Sie Brust, Taille und Hüfte waagerecht und wählen Sie bei Maßen zwischen zwei Größen die bequemere Größe.</p><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Größe</th><th>Brust</th><th>Taille</th><th>Hüfte</th></tr></thead><tbody><tr><td>34</td><td>80–83 cm</td><td>62–65 cm</td><td>86–89 cm</td></tr><tr><td>36</td><td>84–87 cm</td><td>66–69 cm</td><td>90–93 cm</td></tr><tr><td>38</td><td>88–91 cm</td><td>70–73 cm</td><td>94–97 cm</td></tr><tr><td>40</td><td>92–95 cm</td><td>74–77 cm</td><td>98–101 cm</td></tr><tr><td>42</td><td>98–101 cm</td><td>80–83 cm</td><td>104–107 cm</td></tr><tr><td>44</td><td>106–109 cm</td><td>86–89 cm</td><td>106–109 cm</td></tr><tr><td>46</td><td>111–114 cm</td><td>91–94 cm</td><td>110–113 cm</td></tr><tr><td>48</td><td>116–119 cm</td><td>96–99 cm</td><td>116–119 cm</td></tr><tr><td>50</td><td>123–126 cm</td><td>102–105 cm</td><td>122–125 cm</td></tr></tbody></table></div>'
WHERE information_id = 14 AND language_id = @de;

-- Normalized Croatian product metadata. Existing EN/DE product metadata is
-- already localized; Pinella is completed explicitly below in all languages.
CREATE TEMPORARY TABLE mm_product_meta (
  product_id INT NOT NULL,
  language_id INT NOT NULL,
  meta_title VARCHAR(255) NOT NULL,
  meta_description VARCHAR(255) NOT NULL,
  PRIMARY KEY (product_id, language_id)
) CHARACTER SET utf8mb4;

INSERT INTO mm_product_meta VALUES
(2626,@hr,'Kaputić Floria – unikatni cvjetni kaputić | Madam Monica','Kaputić Floria unikatni je žuti cvjetni model od pamuka s elastinom, podstavljen bijelom viskozom i dostupan samo u veličini 42.'),
(2627,@hr,'Rosalie – unikatni ružičasti kaputić | Madam Monica','Rosalie je unikatni ružičasti kaputić od pamuka i lana, s reljefnim gumbima u obliku ruže. Dostupan je samo u veličini 38.'),
(2628,@hr,'Baloner Nocturna – crno-zlatni žakard | Madam Monica','Baloner Nocturna izrađen je od crno-zlatnog dvostranog žakarda s elastinom. Luksuzan statement model bez klasične podstave.'),
(2629,@hr,'Haljina Celestina – plavi pamučni saten | Madam Monica','Celestina je haljina od plavog pamučnog satena s bijelim prugama, naglašenim strukom i raskošnom suknjom krojenom od približno četiri metra tkanine.'),
(2630,@hr,'Haljina Corsella – viskoza i korzetni kroj | Madam Monica','Corsella od prozračne viskoze s elastinom spaja korzetni izgled, oblikovan struk i raskošnu suknju krojenu u pola kruga.'),
(2631,@hr,'Haljina Ornella – žakard s paisley uzorkom | Madam Monica','Ornella je haljina od luksuznog žakarda s paisley uzorkom u bijelim i Blue Curaçao plavim tonovima. Elegantna, ženstvena i upečatljiva.'),
(2632,@hr,'Haljina Rosabella – viskoza s uzorkom ruža | Madam Monica','Rosabella je unikatna haljina od mekane viskozne trikotaže s uzorkom ruža i raskošnim valovima. Dostupna samo u veličini 44.'),
(2633,@hr,'Haljina Dotella – crvena s bijelim točkama | Madam Monica','Dotella je crvena haljina s bijelim točkicama od viskozne mješavine s elastinom. Razigrana i ugodna za dnevne i večernje kombinacije.'),
(2634,@hr,'Haljina Victoria – poslovni sirena kroj | Madam Monica','Victoria je statement poslovna haljina sirena kroja koja naglašava siluetu i spaja ženstvenu eleganciju sa samouvjerenim, profinjenim stavom.'),
(2635,@hr,'Haljina Noemi – crna poslovna haljina | Madam Monica','Noemi je elegantna crna haljina od strukturirane viskoze s elastinom, idealna za posao, sastanke, događaje i večernje kombinacije.'),
(2636,@hr,'Haljina Angelica – bijeli viskozni baršun | Madam Monica','Angelica je romantična bijela haljina od viskoznog baršuna s prozirnim cvjetnim motivima i podstavom od 100% viskoze.'),
(2637,@hr,'Bluza Eliana – bijeli viskozni saten | Madam Monica','Eliana je elegantna bijela bluza od sjajnog viskoznog satena, s vezanjem na leđima koje dodatno oblikuje i naglašava struk.'),
(2639,@hr,'Aviana srebrni sako-kaputić od žakarda | Madam Monica','Aviana je srebrni sako-kaputić od luksuznog dvostranog žakarda s uzorkom ptica. Model bez podstave, profinjenog sjaja.'),
(2640,@hr,'Kaputić Bordelia – cvjetni pamučni saten | Madam Monica','Bordelia je unikatni kaputić od pamučnog satena s bordo cvijećem i kontrastnom podstavom. Dostupan samo u veličini 44.'),
(2641,@hr,'Aviana plavi sako-kaputić od žakarda | Madam Monica','Aviana je plavi sako-kaputić od luksuznog dvostranog žakarda s uzorkom ptica. Upečatljiv model bez klasične podstave.'),
(2642,@hr,'Sako Evelina – ljetni sako od viskoze i lana','Evelina je lagani ljetni sako od viskoze i lana, bez podstave i sa sofisticiranom šal kragnom. Prozračan model za elegantne kombinacije.'),
(2643,@hr,'Haljina Octavia – dvostrani žakard | Madam Monica','Octavia je ženstvena haljina od dvostranog žakarda u Nimbus Cloud i mint zelenim nijansama, bogate teksture i profinjenog kroja.'),
(2644,@hr,'Haljina Emabella – zeleni cvjetni uzorak | Madam Monica','Emabella je romantična zelena haljina s cvjetnim uzorkom od 100% viskoze. Mekana, ugodna i lepršava pri svakom pokretu.'),
(2645,@hr,'Haljina Terrabella – ciglasta viskoza i čipka','Terrabella je romantična haljina od 100% viskoze u toploj ciglastoj nijansi, s bočnim detaljem od čipke i raskošnim valovima.'),
(2646,@hr,'Haljina Monroe – pamučna retro haljina | Madam Monica','Monroe je ženstvena retro haljina od 98% pamuka i 2% elastina, oblikovana za udobnost, naglašeni struk i elegantnu siluetu.'),
(2647,@hr,'Haljina Magnolia – raskošna pamučna suknja | Madam Monica','Magnolia je unikatna haljina od 100% pamuka s raskošnom suknjom izrađenom od približno četiri metra tkanine. Dostupna u veličini 46.'),
(2648,@hr,'Haljina Divnala – strukturirani pamučni kroj | Madam Monica','Divnala je elegantna strukturirana haljina od 97% pamuka i 3% elastina, oblikovana za ženstvenu, naglašenu siluetu.'),
(2649,@hr,'Haljina Divna – pamučna haljina s elastinom | Madam Monica','Divna je unikatna haljina od 97% pamuka i 3% elastina, ugodnog i ženstvenog kroja. Dostupna samo u veličini 40.'),
(2650,@hr,'Tirkizna haljina od 100% pamuka | Madam Monica','Unikatna tirkizna haljina od 100% pamuka sastoji se od dva usklađena dijela. Prozračan i upečatljiv model dostupan u veličini 38.'),
(2651,@hr,'Bluebella – plava viskozna haljina | Madam Monica','Bluebella je elegantna plava haljina od 100% viskoze s drapiranim dekolteom i ženstvenom siluetom. Dostupna samo u veličini 36.'),
(2652,@hr,'Aurabella – cvjetna viskozna haljina | Madam Monica','Aurabella je cvjetna haljina od viskoze s elegantno drapiranim dekolteom i ženstvenim krojem. Dostupna samo u veličini 40.'),
(2653,@hr,'Dottina – ružičasta retro haljina na točkice','Dottina je ružičasta retro haljina na točkice od 98% pamuka i 2% elastina, inspirirana ženstvenim siluetama pedesetih godina.'),
(2655,@hr,'Tajice Bellona – ženske statement tajice | Madam Monica','Bellona su ženske statement tajice od 74% poliamida i 26% elastina. Pripijeni, elastični model za moderne dnevne i večernje kombinacije.'),
(2656,@hr,'Haljina Linda – pamuk, lan i svila | Madam Monica','Linda je lagana i profinjena haljina od mješavine pamuka, lana i svile, oblikovana za prozračnost, udobnost i ženstvenu eleganciju.'),
(2657,@hr,'Haljina Betty – unikatna retro pamučna haljina','Betty je unikatna retro haljina od 100% pamuka, inspirirana klasičnim ženstvenim siluetama. Dostupna samo u veličini 40.'),
(2658,@hr,'Haljina Coralina – puderasto-koraljni pamuk','Coralina je ženstvena haljina od 100% pamuka u puderasto-koraljnoj nijansi, s oblikovanim dekolteom i naglašenom siluetom.'),
(2659,@hr,'Aquabella – cvjetna viskozna haljina | Madam Monica','Aquabella je lepršava cvjetna haljina od 100% viskoze s elegantno drapiranim detaljima i ženstvenim krojem.'),
(2660,@hr,'Dorabella – karirana haljina s čipkom | Madam Monica','Dorabella je tirkizno-bijela karirana haljina od pretežno pamučnog materijala, obogaćena romantičnim detaljima od čipke.'),
(2661,@hr,'Bordobella – bordo viskozna haljina | Madam Monica','Bordobella je elegantna bordo haljina od 100% viskoze s drapiranim detaljima, mekim padom materijala i ženstvenom siluetom.'),
(2662,@hr,'Poisabella – tamnoplava haljina na točkice','Poisabella je tamnoplava haljina od 100% viskoze s crvenim točkicama, razigranim uzorkom i elegantnim ženstvenim krojem.'),
(2663,@hr,'Livia – ružičasta haljina pripijenog kroja','Livia je ružičasta haljina pripijenog kroja od elastične mješavine poliamida, s efektnim volanom i naglašenom siluetom.'),
(2664,@hr,'Pinella – svijetloplava haljina uz tijelo | Madam Monica','Pinella je elegantna svijetloplava haljina pripijenog kroja, strukturiranog dekoltea i nježnog geometrijskog uzorka.'),
(2664,@en,'Pinella – Fitted Light Blue Dress | Madam Monica','Pinella is an elegant fitted light blue dress with a structured neckline and a delicate geometric pattern.'),
(2664,@de,'Pinella – Figurbetontes hellblaues Kleid | Madam Monica','Pinella ist ein elegantes, figurbetontes hellblaues Kleid mit strukturiertem Ausschnitt und feinem geometrischem Muster.');

UPDATE oc_product_description pd
JOIN mm_product_meta mm ON mm.product_id = pd.product_id AND mm.language_id = pd.language_id
SET pd.meta_title = mm.meta_title,
    pd.meta_description = mm.meta_description,
    pd.meta_keyword = '',
    pd.h1 = pd.name,
    pd.image_alt = pd.name,
    pd.image_title = pd.name;
DROP TEMPORARY TABLE mm_product_meta;

-- Complete the previously empty Pinella product copy without inventing its
-- composition or available sizes; those facts should be added after confirmation.
UPDATE oc_product_description SET description = '<p>Pinella je elegantna svijetloplava haljina pripijenog kroja, strukturiranog dekoltea i nježnog geometrijskog uzorka. Za točan sastav materijala i dostupne veličine obratite se korisničkoj podršci prije naručivanja.</p>' WHERE product_id = 2664 AND language_id = @hr;
UPDATE oc_product_description SET description = '<p>Pinella is an elegant fitted light blue dress with a structured neckline and a delicate geometric pattern. Please contact customer support before ordering to confirm the exact fabric composition and available sizes.</p>' WHERE product_id = 2664 AND language_id = @en;
UPDATE oc_product_description SET description = '<p>Pinella ist ein elegantes, figurbetontes hellblaues Kleid mit strukturiertem Ausschnitt und feinem geometrischem Muster. Bitte fragen Sie vor der Bestellung beim Kundenservice nach Materialzusammensetzung und verfügbaren Größen.</p>' WHERE product_id = 2664 AND language_id = @de;

-- Repair one existing English title typo.
UPDATE oc_product_description SET meta_title = 'Bluebella Blue Viscose Dress | Madam Monica' WHERE product_id = 2651 AND language_id = @en;
UPDATE oc_product_description SET description = REPLACE(description, '&lt;h1&gt;DEUTSCH&lt;/h1&gt;', '') WHERE product_id = 2634 AND language_id = @en;

-- Keep the longest existing product snippets within practical search-result
-- display limits without removing material, size or design information.
UPDATE oc_product_description SET meta_title = 'Rosalie Pink Cotton-Linen Jacket | Madam Monica' WHERE product_id = 2627 AND language_id = @en;
UPDATE oc_product_description SET meta_title = 'Rosalie rosa Baumwoll-Leinen-Jacke | Madam Monica' WHERE product_id = 2627 AND language_id = @de;
UPDATE oc_product_description SET meta_description = 'Angelica ist ein weißes Kleid aus Viskosesamt mit transparenten Blumenmotiven und weißem Viskosefutter – romantisch und elegant für besondere Anlässe.' WHERE product_id = 2636 AND language_id = @de;
UPDATE oc_product_description SET meta_title = 'Aviana Silver Jacquard Blazer | Madam Monica' WHERE product_id = 2639 AND language_id = @en;
UPDATE oc_product_description SET meta_title = 'Aviana silberner Jacquard-Blazer | Madam Monica' WHERE product_id = 2639 AND language_id = @de;
UPDATE oc_product_description SET meta_title = 'Bordelia Mantel aus Baumwollsatin | Madam Monica' WHERE product_id = 2640 AND language_id = @de;
UPDATE oc_product_description SET meta_title = 'Aviana Blue Jacquard Blazer | Madam Monica' WHERE product_id = 2641 AND language_id = @en;
UPDATE oc_product_description SET meta_title = 'Aviana blauer Jacquard-Blazer | Madam Monica' WHERE product_id = 2641 AND language_id = @de;
UPDATE oc_product_description SET meta_description = 'Octavia ist ein feminines Kleid aus doppelseitigem Jacquard in Nimbus Cloud und Mintgrün – luxuriös, elegant und besonders.' WHERE product_id = 2643 AND language_id = @de;
UPDATE oc_product_description SET meta_description = 'Terrabella ist ein romantisches Kleid aus 100 % Viskose in warmem Ziegelrot, mit seitlichem Spitzendetail und üppigen Wellen.' WHERE product_id = 2645 AND language_id = @de;
UPDATE oc_product_description SET meta_description = 'Monroe ist ein feminines Kleid aus 98 % Baumwolle und 2 % Elasthan, inspiriert von zeitlosem Glamour, mit Komfort und eleganter Silhouette.' WHERE product_id = 2646 AND language_id = @de;
UPDATE oc_product_description SET meta_description = 'Magnolia ist ein Kleid aus 100 % Baumwolle mit betonter Taille und voluminösem Rock aus etwa vier Metern Stoff, erhältlich in Größe 46.' WHERE product_id = 2647 AND language_id = @de;
UPDATE oc_product_description SET meta_description = 'Divna ist ein elegantes Kleid aus 97 % Baumwolle und 3 % Elasthan. Der strukturierte Stoff formt eine feminine Silhouette; erhältlich in Größe 40.' WHERE product_id = 2649 AND language_id = @de;
UPDATE oc_product_description SET meta_description = 'Tirkizna ist ein Kleid aus 100 % Baumwolle, inspiriert vom Türkis-Edelstein. Das einzigartige Modell wurde zweimal in Größe 38 gefertigt.' WHERE product_id = 2650 AND language_id = @de;
UPDATE oc_product_description SET meta_description = 'Bellona women’s leggings have a feminine fitted cut and striking elongated hem. A statement piece for heels, a blazer or an elegant top.' WHERE product_id = 2655 AND language_id = @en;
UPDATE oc_product_description SET meta_description = 'Bellona Damen-Leggings haben einen femininen Schnitt und markant verlängerten Beinabschluss – ein Statement-Piece für High Heels, Blazer oder Top.' WHERE product_id = 2655 AND language_id = @de;

-- Normalize stored preload markup; the storefront controller keeps both homepage
-- videos muted and autoplaying while avoiding duplicate control attributes.
UPDATE oc_module
SET setting = REPLACE(setting, 'preload=&quot;auto&quot;', 'preload=&quot;metadata&quot;')
WHERE module_id = 90 AND code = 'basel_content';

-- Clear obsolete meta keywords throughout the optimized public content.
UPDATE oc_product_description SET meta_keyword = '' WHERE product_id BETWEEN 2626 AND 2664;
UPDATE oc_category_description SET meta_keyword = '' WHERE category_id IN (256,258,259,260,261,262);
UPDATE oc_information_description SET meta_keyword = '' WHERE information_id IN (3,5,6,9,10,14,21,24);
