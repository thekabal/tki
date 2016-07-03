CREATE TABLE IF NOT EXISTS tki_messages (
  id integer NOT NULL DEFAULT nextval('tki_messages_id_seq'),
  sender_id integer NOT NULL DEFAULT '0',
  recp_id integer NOT NULL DEFAULT '0',
  "subject" character varying(250) DEFAULT NULL,
  sent character varying(19) DEFAULT NULL,
  message text,
  notified character varying(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (id)
);
