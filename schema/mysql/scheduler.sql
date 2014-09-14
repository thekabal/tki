CREATE TABLE IF NOT EXISTS bnt_scheduler (
  sched_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  run_once varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  ticks_left int(10) unsigned NOT NULL DEFAULT '0',
  ticks_full int(10) unsigned NOT NULL DEFAULT '0',
  spawn int(10) unsigned NOT NULL DEFAULT '0',
  sched_file varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  extra_info varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  last_run int(20) DEFAULT NULL,
  PRIMARY KEY (sched_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=11 ;

