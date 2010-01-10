
CREATE TABLE IF NOT EXISTS `fz_file` (
  `id`              INTEGER         NOT NULL,
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

  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

