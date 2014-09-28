CREATE TABLE IF NOT EXISTS tki_ip_bans (
  ban_id integer NOT NULL DEFAULT nextval('tki_ip_bans_ban_id_seq'),
  ban_mask character varying(16) NOT NULL,
  PRIMARY KEY (ban_id)
);
