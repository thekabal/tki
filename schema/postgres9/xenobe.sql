CREATE TABLE IF NOT EXISTS tki_xenobe (
  xenobe_id character varying(40) NOT NULL,
  active character varying(1) NOT NULL DEFAULT 'Y',
  aggression integer NOT NULL DEFAULT '0',
  orders integer NOT NULL DEFAULT '0',
  PRIMARY KEY (xenobe_id)
)
