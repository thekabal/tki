CREATE TABLE IF NOT EXISTS tki_logs (
  log_id integer NOT NULL DEFAULT nextval('tki_logs_log_id_seq'),
  ship_id integer NOT NULL DEFAULT '0',
  "type" integer NOT NULL DEFAULT '0',
  "time" timestamp without time zone DEFAULT NULL,
  "data" text,
  PRIMARY KEY (log_id)
);
