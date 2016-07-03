CREATE TABLE IF NOT EXISTS tki_xenobe (
  xenobe_id varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  active varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  aggression int(5) NOT NULL DEFAULT '0',
  orders int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (xenobe_id),
  KEY tki_xenobe_id (xenobe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

