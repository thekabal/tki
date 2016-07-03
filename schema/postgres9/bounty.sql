CREATE TABLE IF NOT EXISTS tki_bounty (
  bounty_id integer NOT NULL DEFAULT nextval('tki_bounty_bounty_id_seq'),
  amount integer NOT NULL DEFAULT '0',
  bounty_on integer NOT NULL DEFAULT '0',
  placed_by integer NOT NULL DEFAULT '0',
  PRIMARY KEY (bounty_id)
);
