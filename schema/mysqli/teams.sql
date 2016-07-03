CREATE TABLE IF NOT EXISTS tki_teams (
  id int(11) NOT NULL DEFAULT '0',
  creator int(11) DEFAULT '0',
  team_name varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  description varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  number_of_members int(3) NOT NULL DEFAULT '0',
  admin varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (id),
  KEY tki_admin (admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
