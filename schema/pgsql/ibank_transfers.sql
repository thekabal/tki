CREATE TABLE IF NOT EXISTS tki_ibank_transfers (
  transfer_id integer NOT NULL DEFAULT nextval('tki_ibank_transfers_transfer_id_seq'),
  source_id integer NOT NULL DEFAULT '0',
  dest_id integer NOT NULL DEFAULT '0',
  "time" timestamp without time zone DEFAULT NULL,
  amount integer NOT NULL DEFAULT '0',
  PRIMARY KEY (transfer_id)
);
