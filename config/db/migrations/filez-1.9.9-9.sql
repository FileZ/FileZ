SET NAMES 'utf8';

ALTER TABLE `Fichiers` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci ;
ALTER TABLE `Fichiers` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
ALTER TABLE `Fichiers` 
CHANGE `id`                 `id`                BIGINT       UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `del_notif_sended`   `del_notif_sent`    TINYINT(1)   NOT NULL DEFAULT '0', 
CHANGE `nom`                `file_name`         VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `nom_physique`       `nom_physique`      VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `mail_proprietaire`  `uploader_email`    VARCHAR(60)  CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
CHANGE `taille`             `file_size`         INT(11)      NOT NULL DEFAULT '0',
CHANGE `adresse`            `adresse`           VARCHAR(50)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `date_debut`         `available_from`    DATE         NOT NULL DEFAULT '0000-00-00',
CHANGE `date_fin`           `available_until`   DATE         NOT NULL DEFAULT '0000-00-00',
CHANGE `nb_dl`              `download_count`    INT(11)      NOT NULL DEFAULT '0',
CHANGE `notif`              `notify_uploader`   TINYINT(1)   NOT NULL DEFAULT '0',
CHANGE `uid`                `uploader_uid`      VARCHAR(30)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `nb_ren`             `extends_count`     INT(11)      NOT NULL DEFAULT '0',
CHANGE `comment`            `comment`           VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `Fichiers` ADD COLUMN `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP;

RENAME TABLE `Fichiers` TO fz_file;
