CREATE TABLE IF NOT EXISTS tki_characters (
  character_id integer NOT NULL DEFAULT nextval('tki_characters_character_id_seq'),
  character_name character varying(20) NOT NULL,
  credits integer NOT NULL DEFAULT '0',
  turns integer NOT NULL DEFAULT '0',
  turns_used integer NOT NULL DEFAULT '0',
  rating integer NOT NULL DEFAULT '0',
  score integer NOT NULL DEFAULT '0',
  PRIMARY KEY (character_id)
);
