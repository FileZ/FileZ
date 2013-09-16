ALTER TABLE  `fz_user` ADD  `quota` VARCHAR( 25 ) NOT NULL DEFAULT  '2G' AFTER  `email`

INSERT INTO `fz_info` (`key`, `value`) VALUES ('db_version', '3.0-alpha') ON DUPLICATE KEY UPDATE value = '3.0-alpha';
