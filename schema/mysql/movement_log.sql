CREATE TABLE IF NOT EXISTS tki_movement_log (
  event_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  ship_id int(11) NOT NULL DEFAULT '0',
  sector_id int(11) DEFAULT '0',
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (event_id),
  KEY tki_ship_id (ship_id),
  KEY tki_sector_id (sector_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
