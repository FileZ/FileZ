
CREATE TABLE  `fz_info` (
 `key`   VARCHAR( 30 ) NOT NULL ,
 `value` VARCHAR( 50 ) NOT NULL ,
  PRIMARY KEY (  `key` )
);

INSERT INTO `fz_info` (`key`, `value`) VALUES ('db_version', '2.0.0-2');