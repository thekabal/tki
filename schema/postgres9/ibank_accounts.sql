CREATE TABLE IF NOT EXISTS tki_ibank_accounts (
  ship_id integer NOT NULL DEFAULT '0',
  balance integer DEFAULT '0',
  loan integer DEFAULT '0',
  loantime timestamp without time zone DEFAULT NULL,
  PRIMARY KEY (ship_id)
)
