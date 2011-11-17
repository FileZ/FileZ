-- SQLITE3 use UTF8 format by default SET NAMES 'utf8';
CREATE TABLE IF NOT EXISTS `fz_file` (
  `id`              BIGINT UNSIGNED NOT NULL,
  `del_notif_sent`  BOOLEAN         DEFAULT 0,
  `file_name`       varchar(100)    NOT NULL,
  `file_size`       INTEGER         DEFAULT 0,
  `available_from`  DATE            NOT NULL,
  `available_until` DATE            NOT NULL,
  `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment`         varchar(200),
  `download_count`  INTEGER         DEFAULT 0,
  `notify_uploader` BOOLEAN         DEFAULT 0,
  `uploader_uid`    varchar(30)     DEFAULT NULL,
  `uploader_email`  varchar(60)     DEFAULT NULL,
  `extends_count`   INTEGER         DEFAULT '0',
  `password`        varchar(40)     DEFAULT NULL

);
CREATE UNIQUE INDEX IF NOT EXISTS 'fz_file_id_idx' on 'fz_file' ('id');


CREATE TABLE IF NOT EXISTS  `fz_info` (
 `key`   VARCHAR( 30 ) NOT NULL,
 `value` VARCHAR( 50 ) NOT NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS 'fz_info_key_idx' on 'fz_info' ('key');
INSERT INTO `fz_info` (`key`, `value`) VALUES ('db_version', '2.0.0-2');

