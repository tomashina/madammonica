-- Madam Monica collection menu migration
-- Target: OpenCart 3.x / oc_ prefix / HR + EN + DE
-- Idempotent: safe to run more than once. Back up the database before import.

SET NAMES utf8mb4;

SET @hr := (SELECT language_id FROM oc_language WHERE code = 'hr-hr' LIMIT 1);
SET @en := (SELECT language_id FROM oc_language WHERE code = 'en-gb' LIMIT 1);
SET @de := (SELECT language_id FROM oc_language WHERE code = 'de-de' LIMIT 1);

-- The Basel theme's active menu uses OpenCart's top-level categories.
-- Enable exactly the requested collection categories and order them explicitly.
UPDATE oc_category
SET top = 1,
    sort_order = CASE category_id
      WHEN 259 THEN 0
      WHEN 258 THEN 10
      WHEN 261 THEN 20
      WHEN 260 THEN 30
      WHEN 256 THEN 40
      WHEN 262 THEN 50
    END
WHERE category_id IN (256, 258, 259, 260, 261, 262);

-- Localized labels used in the menu and category heading.
UPDATE oc_category_description
SET name = CASE
  WHEN category_id = 259 AND language_id = @hr THEN 'Cijela kolekcija'
  WHEN category_id = 259 AND language_id = @en THEN 'Entire Collection'
  WHEN category_id = 259 AND language_id = @de THEN 'Gesamte Kollektion'
  WHEN category_id = 260 AND language_id = @hr THEN 'Bluze'
  WHEN category_id = 260 AND language_id = @en THEN 'Blouses'
  WHEN category_id = 260 AND language_id = @de THEN 'Blusen'
  WHEN category_id = 256 AND language_id = @hr THEN 'Kaputi i sakoi'
  WHEN category_id = 256 AND language_id = @en THEN 'Coats and Blazers'
  WHEN category_id = 256 AND language_id = @de THEN 'Mäntel und Blazer'
  ELSE name
END
WHERE category_id IN (256, 259, 260)
  AND language_id IN (@hr, @en, @de);

-- Keep theme-specific heading/image fields aligned when they exist in this store.
UPDATE oc_category_description
SET h1 = name,
    image_alt = name,
    image_title = name
WHERE category_id IN (256, 258, 259, 260, 261, 262)
  AND language_id IN (@hr, @en, @de);
