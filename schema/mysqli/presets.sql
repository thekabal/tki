CREATE TABLE IF NOT EXISTS tki_presets (
  preset_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  ship_id smallint(10) unsigned NOT NULL DEFAULT '0',
  preset int(10) unsigned NOT NULL DEFAULT '1',
  type char(1) NOT NULL DEFAULT 'R',
  PRIMARY KEY(preset_id),
  KEY presets_ship_id (`ship_id`, `preset`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;
