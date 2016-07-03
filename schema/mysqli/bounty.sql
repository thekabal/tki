CREATE TABLE IF NOT EXISTS tki_bounty (
  bounty_id int(10) NOT NULL AUTO_INCREMENT,
  amount int(20) NOT NULL DEFAULT '0',
  bounty_on int(10) NOT NULL DEFAULT '0',
  placed_by int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (bounty_id),
  KEY tki_bounty_on (bounty_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
