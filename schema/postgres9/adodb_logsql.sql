CREATE TABLE IF NOT EXISTS tki_adodb_logsql (
  created timestamp without time zone NOT NULL,
  sql0 character varying(250) NOT NULL,
  sql1 text,
  params text,
  tracer text,
  timer numeric(16,0) NOT NULL
)
