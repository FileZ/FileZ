CREATE TABLE IF NOT EXISTS `fz_user` (
    `id` INTEGER PRIMARY KEY,
    `username` VARCHAR( 30 ) NOT NULL ,
    `password` VARCHAR( 40 ) NOT NULL ,
    `salt` VARCHAR( 40 ),
    `firstname` VARCHAR( 50 ) NOT NULL ,
    `lastname` VARCHAR( 50 ) NOT NULL ,
    `email` VARCHAR( 50 ) NOT NULL ,
    `is_admin` BOOLEAN DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

--CREATE UNIQUE INDEX IF NOT EXISTS 'fz_user_id_idx' ON 'fz_user' ('id');
