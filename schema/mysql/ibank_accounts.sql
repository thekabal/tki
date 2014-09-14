CREATE TABLE IF NOT EXISTS tki_ibank_accounts (
  ship_id int(11) NOT NULL DEFAULT '0',
  balance int(20) DEFAULT '0',
  loan int(20) DEFAULT '0',
  loantime datetime DEFAULT NULL,
  PRIMARY KEY (ship_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

