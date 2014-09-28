CREATE TABLE IF NOT EXISTS tki_presets (
  preset_id integer NOT NULL DEFAULT nextval('tki_presets_preset_id'),
  ship_id integer NOT NULL DEFAULT '0',
  preset integer NOT NULL DEFAULT '1',
  type character(1) NOT NULL DEFAULT 'R',
  PRIMARY KEY(preset_id)
);
