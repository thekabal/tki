CREATE TABLE IF NOT EXISTS tki_kabal (
  kabal_id varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  active varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  aggression int(5) NOT NULL DEFAULT '0',
  orders int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (kabal_id),
  KEY tki_kabal_id (kabal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

