CREATE TABLE IF NOT EXISTS bnt_links (
  link_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  link_start int(10) unsigned NOT NULL DEFAULT '0',
  link_dest int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (link_id),
  KEY bnt_link_start (link_start),
  KEY bnt_link_dest (link_dest)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
