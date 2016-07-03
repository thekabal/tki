CREATE TABLE IF NOT EXISTS tki_traderoutes (
  traderoute_id integer NOT NULL DEFAULT nextval('tki_traderoutes_traderoute_id_seq'),
  source_id integer NOT NULL DEFAULT '0',
  dest_id integer NOT NULL DEFAULT '0',
  source_type character varying(1) NOT NULL DEFAULT 'P',
  dest_type character varying(1) NOT NULL DEFAULT 'P',
  move_type character varying(1) NOT NULL DEFAULT 'W',
  "owner" integer NOT NULL DEFAULT '0',
  circuit character varying(1) NOT NULL DEFAULT '2',
  PRIMARY KEY (traderoute_id)
);
