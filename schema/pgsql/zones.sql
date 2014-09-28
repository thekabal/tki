CREATE TABLE IF NOT EXISTS tki_zones (
  zone_id integer NOT NULL DEFAULT nextval('tki_zones_zone_id_seq'),
  zone_name character varying(40) DEFAULT NULL,
  "owner" integer NOT NULL DEFAULT '0',
  corp_zone character varying(1) NOT NULL DEFAULT 'N',
  allow_beacon character varying(1) NOT NULL DEFAULT 'Y',
  allow_attack character varying(1) NOT NULL DEFAULT 'Y',
  allow_planetattack character varying(1) NOT NULL DEFAULT 'Y',
  allow_warpedit character varying(1) NOT NULL DEFAULT 'Y',
  allow_planet character varying(1) NOT NULL DEFAULT 'Y',
  allow_trade character varying(1) NOT NULL DEFAULT 'Y',
  allow_defenses character varying(1) NOT NULL DEFAULT 'Y',
  max_hull integer NOT NULL DEFAULT '0',
  PRIMARY KEY (zone_id)
);
