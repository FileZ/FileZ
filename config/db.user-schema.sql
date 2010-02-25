
CREATE TABLE  `filez`.`fz_user` (
`id` SERIAL NOT NULL ,
`username` VARCHAR( 30 ) NOT NULL ,
`password` VARCHAR( 40 ) NOT NULL ,
`salt` VARCHAR( 40 ),
`firstname` VARCHAR( 50 ) NOT NULL ,
`lastname` VARCHAR( 50 ) NOT NULL ,
`email` VARCHAR( 50 ) NOT NULL
) ENGINE = MYISAM ;
