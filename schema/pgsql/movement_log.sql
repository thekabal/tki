CREATE TABLE IF NOT EXISTS tki_movement_log (
  event_id integer NOT NULL DEFAULT nextval('tki_movement_log_event_id_seq'),
  ship_id integer NOT NULL DEFAULT '0',
  sector_id integer DEFAULT '0',
  "time" timestamp without time zone DEFAULT NULL,
  PRIMARY KEY (event_id)
);
