CREATE TABLE IF NOT EXISTS tki_universe (
  sector_id integer NOT NULL DEFAULT nextval('tki_universe_sector_id_seq'),
  sector_name character varying(30) DEFAULT NULL,
  zone_id integer NOT NULL DEFAULT '0',
  port_type character varying(8) NOT NULL DEFAULT 'none',
  port_organics integer NOT NULL DEFAULT '0',
  port_ore integer NOT NULL DEFAULT '0',
  port_goods integer NOT NULL DEFAULT '0',
  port_energy integer NOT NULL DEFAULT '0',
  beacon character varying(50) DEFAULT NULL,
  angle1 decimal(10,0) NOT NULL DEFAULT '0',
  angle2 decimal(10,0) NOT NULL DEFAULT '0',
  distance integer NOT NULL DEFAULT '0',
  fighters integer NOT NULL DEFAULT '0',
  PRIMARY KEY (sector_id)
);
