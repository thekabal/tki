CREATE TABLE IF NOT EXISTS tki_sessions (
  sesskey character varying(104) NOT NULL,
  expiry timestamp without time zone NOT NULL,
  sessdata text,
  PRIMARY KEY (sesskey)
)
