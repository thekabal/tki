CREATE TABLE IF NOT EXISTS bnt_adodb_logsql (
  created datetime NOT NULL,
  sql0 varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  sql1 text COLLATE utf8mb4_unicode_ci,
  params text COLLATE utf8mb4_unicode_ci,
  tracer text COLLATE utf8mb4_unicode_ci,
  timer decimal(16,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
