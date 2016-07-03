CREATE TABLE IF NOT EXISTS tki_scheduler (
  sched_id integer NOT NULL DEFAULT nextval('tki_scheduler_sched_id_seq'),
  run_once character varying(1) NOT NULL DEFAULT 'N',
  ticks_left integer NOT NULL DEFAULT '0',
  ticks_full integer NOT NULL DEFAULT '0',
  spawn integer NOT NULL DEFAULT '0',
  sched_file character varying(30) NOT NULL,
  extra_info character varying(50) DEFAULT NULL,
  last_run integer DEFAULT NULL,
  PRIMARY KEY (sched_id)
);
