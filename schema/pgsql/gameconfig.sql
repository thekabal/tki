CREATE TABLE IF NOT EXISTS tki_gameconfig (
  config_id integer NOT NULL DEFAULT nextval('tki_gameconfig_config_id_seq'),
  section character varying(30) NOT NULL DEFAULT 'game',
  "name" character varying(75) NOT NULL,
  category character varying(30) NOT NULL,
  type character varying(8) NOT NULL,
  "value" character varying(128) NOT NULL,
  PRIMARY KEY (config_id)
);
