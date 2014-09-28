CREATE TABLE IF NOT EXISTS tki_bans (
  ban_id integer NOT NULL DEFAULT nextval('tki_bans_ban_id_seq'),
  ban_type integer NOT NULL DEFAULT '0',
  ban_mask character varying(16) DEFAULT NULL,
  ban_ship integer DEFAULT NULL,
  ban_date timestamp without time zone DEFAULT NULL,
  public_info text,
  admin_info text,
  PRIMARY KEY (ban_id)
);
