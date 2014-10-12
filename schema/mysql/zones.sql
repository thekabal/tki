CREATE TABLE IF NOT EXISTS tki_zones (
  zone_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  zone_name varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  team_zone varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  allow_beacon varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  allow_attack varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  allow_planetattack varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  allow_warpedit varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  allow_planet varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  allow_trade varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  allow_defenses varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  max_hull int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (zone_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
