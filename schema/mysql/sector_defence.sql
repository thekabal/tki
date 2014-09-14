CREATE TABLE IF NOT EXISTS bnt_sector_defence (
  defence_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  ship_id int(11) NOT NULL DEFAULT '0',
  sector_id int(10) unsigned NOT NULL DEFAULT '0',
  defence_type varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'M',
  quantity int(20) NOT NULL DEFAULT '0',
  fm_setting varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'toll',
  PRIMARY KEY (defence_id),
  KEY bnt_sector_id_key (sector_id),
  KEY bnt_ship_id_key (ship_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
