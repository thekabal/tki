CREATE TABLE IF NOT EXISTS bnt_players (
  player_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  recovery_time int(20) DEFAULT NULL,
  email varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  last_login datetime DEFAULT NULL,
  ip_address varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  lang varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'english.inc',
  PRIMARY KEY (player_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
