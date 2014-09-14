CREATE TABLE IF NOT EXISTS bnt_messages (
  ID int(11) NOT NULL AUTO_INCREMENT,
  sender_id int(11) NOT NULL DEFAULT '0',
  recp_id int(11) NOT NULL DEFAULT '0',
  `subject` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  sent varchar(19) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  message text COLLATE utf8mb4_unicode_ci,
  notified varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

