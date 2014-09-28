CREATE TABLE IF NOT EXISTS tki_teams (
  id integer NOT NULL DEFAULT '0',
  creator integer DEFAULT '0',
  team_name character varying(20) DEFAULT NULL,
  description character varying(20) DEFAULT NULL,
  number_of_members integer NOT NULL DEFAULT '0',
  admin character varying NOT NULL DEFAULT 'N',
  PRIMARY KEY (id)
)
