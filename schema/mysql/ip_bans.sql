CREATE TABLE IF NOT EXISTS tki_ip_bans (
  ban_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  ban_mask varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (ban_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
