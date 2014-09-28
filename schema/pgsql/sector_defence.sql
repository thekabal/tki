CREATE TABLE IF NOT EXISTS tki_sector_defence (
  defence_id integer NOT NULL DEFAULT nextval('tki_sector_defence_defence_id_seq'),
  ship_id integer NOT NULL DEFAULT '0',
  sector_id integer NOT NULL DEFAULT '0',
  defence_type varchar(1) NOT NULL DEFAULT 'M',
  quantity integer NOT NULL DEFAULT '0',
  fm_setting varchar(6) NOT NULL DEFAULT 'toll',
  PRIMARY KEY (defence_id)
);
