CREATE TABLE IF NOT EXISTS tki_news (
  news_id int(11) NOT NULL AUTO_INCREMENT,
  headline varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  newstext text COLLATE utf8mb4_unicode_ci,
  user_id int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  news_type varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (news_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

