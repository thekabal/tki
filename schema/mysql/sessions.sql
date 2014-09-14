CREATE TABLE IF NOT EXISTS tki_sessions (
  sesskey varchar(104) COLLATE utf8mb4_unicode_ci NOT NULL,
  expiry datetime NOT NULL,
  sessdata text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (sesskey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
