CREATE TABLE IF NOT EXISTS tki_players (
  player_id integer NOT NULL DEFAULT nextval('tki_players_player_id_seq'),
  "password" character varying(255) NOT NULL,
  recovery_time integer DEFAULT NULL,
  email character varying(60) DEFAULT NULL,
  last_login timestamp without time zone DEFAULT NULL,
  ip_address character varying(16) NOT NULL,
  lang character varying(30) NOT NULL DEFAULT 'english.inc',
  PRIMARY KEY (player_id)
);
