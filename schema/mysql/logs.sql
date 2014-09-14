CREATE TABLE IF NOT EXISTS bnt_logs (
  log_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  ship_id int(11) NOT NULL DEFAULT '0',
  `type` int(5) NOT NULL DEFAULT '0',
  `time` datetime DEFAULT NULL,
  `data` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (log_id),
  KEY bnt_idate (ship_id,`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
