CREATE TABLE IF NOT EXISTS tki_sector_defense (
  defense_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  ship_id int(11) NOT NULL DEFAULT '0',
  sector_id int(10) unsigned NOT NULL DEFAULT '0',
  defense_type varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'M',
  quantity int(20) NOT NULL DEFAULT '0',
  fm_setting varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'toll',
  PRIMARY KEY (defense_id),
  KEY tki_sector_id_key (sector_id),
  KEY tki_ship_id_key (ship_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
