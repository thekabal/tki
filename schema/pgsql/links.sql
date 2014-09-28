CREATE TABLE IF NOT EXISTS tki_links (
  link_id integer NOT NULL DEFAULT nextval('tki_links_link_id_seq'),
  link_start integer NOT NULL DEFAULT '0',
  link_dest integer NOT NULL DEFAULT '0',
  PRIMARY KEY (link_id)
);
