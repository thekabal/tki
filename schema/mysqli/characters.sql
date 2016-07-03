CREATE TABLE IF NOT EXISTS tki_characters (
  character_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  character_name varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  credits int(20) NOT NULL DEFAULT '0',
  turns int(4) NOT NULL DEFAULT '0',
  turns_used int(10) unsigned NOT NULL DEFAULT '0',
  rating int(11) NOT NULL DEFAULT '0',
  score int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (character_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
