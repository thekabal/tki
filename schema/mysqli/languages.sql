CREATE TABLE IF NOT EXISTS tki_languages (
  lang_id int(5) NOT NULL AUTO_INCREMENT,
  section varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'english',
  `name` varchar(75) COLLATE utf8mb4_unicode_ci NOT NULL,
  category varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  type varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (lang_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
