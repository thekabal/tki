CREATE TABLE IF NOT EXISTS tki_languages (
  lang_id integer NOT NULL DEFAULT nextval('tki_languages_lang_id_seq'),
  section character varying(30) NOT NULL DEFAULT 'english',
  "name" character varying(75) NOT NULL,
  category character varying(30) NOT NULL,
  type character varying(8) NOT NULL,
  "value" character varying(2000) NOT NULL,
  PRIMARY KEY (lang_id)
);
