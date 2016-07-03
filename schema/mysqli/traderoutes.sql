CREATE TABLE IF NOT EXISTS tki_traderoutes (
  traderoute_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  source_id int(10) unsigned NOT NULL DEFAULT '0',
  dest_id int(10) unsigned NOT NULL DEFAULT '0',
  source_type varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'P',
  dest_type varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'P',
  move_type varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'W',
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  circuit varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2',
  PRIMARY KEY (traderoute_id),
  KEY tki_owner_key (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
