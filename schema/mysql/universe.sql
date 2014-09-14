CREATE TABLE IF NOT EXISTS bnt_universe (
  sector_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  sector_name varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  zone_id int(11) NOT NULL DEFAULT '0',
  port_type varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  port_organics int(20) NOT NULL DEFAULT '0',
  port_ore int(20) NOT NULL DEFAULT '0',
  port_goods int(20) NOT NULL DEFAULT '0',
  port_energy int(20) NOT NULL DEFAULT '0',
  beacon varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  angle1 decimal(10,0) NOT NULL DEFAULT '0',
  angle2 decimal(10,0) NOT NULL DEFAULT '0',
  distance int(20) unsigned NOT NULL DEFAULT '0',
  fighters int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (sector_id),
  KEY bnt_zone_id (zone_id),
  KEY bnt_port_type (port_type)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
