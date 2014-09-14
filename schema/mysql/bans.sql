CREATE TABLE IF NOT EXISTS tki_bans (
  ban_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  ban_type int(3) unsigned NOT NULL DEFAULT '0',
  ban_mask varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ban_ship int(10) unsigned DEFAULT NULL,
  ban_date datetime DEFAULT NULL,
  public_info text COLLATE utf8mb4_unicode_ci,
  admin_info text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (ban_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
