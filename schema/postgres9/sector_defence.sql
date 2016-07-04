CREATE TABLE IF NOT EXISTS tki_sector_defense (
  defense_id integer NOT NULL DEFAULT nextval('tki_sector_defense_defense_id_seq'),
  ship_id integer NOT NULL DEFAULT '0',
  sector_id integer NOT NULL DEFAULT '0',
  defense_type varchar(1) NOT NULL DEFAULT 'M',
  quantity integer NOT NULL DEFAULT '0',
  fm_setting varchar(6) NOT NULL DEFAULT 'toll',
  PRIMARY KEY (defense_id)
);
