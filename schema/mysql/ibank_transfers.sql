CREATE TABLE IF NOT EXISTS bnt_ibank_transfers (
  transfer_id int(11) NOT NULL AUTO_INCREMENT,
  source_id int(11) NOT NULL DEFAULT '0',
  dest_id int(11) NOT NULL DEFAULT '0',
  `time` datetime DEFAULT NULL,
  amount int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (transfer_id),
  KEY bnt_amount (amount)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
