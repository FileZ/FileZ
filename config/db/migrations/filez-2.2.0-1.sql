SET NAMES 'utf8';

CREATE TABLE `fz_user` (
  `id`          SERIAL      NOT NULL,
  `username`    VARCHAR(30) NOT NULL,
  `password`    VARCHAR(40) NOT NULL,
  `salt`        VARCHAR(40),
  `firstname`   VARCHAR(50) NOT NULL,
  `lastname`    VARCHAR(50) NOT NULL,
  `email`       VARCHAR(50) NOT NULL,
  `is_admin`    BOOLEAN     DEFAULT 0,
  `created_at`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = MYISAM ;

ALTER TABLE `fz_file` DROP COLUMN `uploader_uid`;
ALTER TABLE `fz_file` DROP COLUMN `uploader_email`;

ALTER TABLE `fz_file` ADD COLUMN       `created_by` INTEGER NOT NULL;
ALTER TABLE `fz_file` ADD INDEX       (`created_by`);
ALTER TABLE `fz_file` ADD FOREIGN KEY (`created_by`) REFERENCES fz_user(id);

UPDATE `fz_info` SET `value`='2.2.0-1' WHERE `key`='db_version';
