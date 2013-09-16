SET NAMES 'utf8';

--
-- Base de données: `filez`
--

-- --------------------------------------------------------

--
-- Structure de la table `fz_file`
--

CREATE TABLE IF NOT EXISTS `fz_file` (
  `id` bigint(20) unsigned NOT NULL,
  `del_notif_sent` tinyint(1) DEFAULT '0',
  `file_name` varchar(100) NOT NULL,
  `file_size` int(11) DEFAULT '0',
  `available_from` date NOT NULL,
  `available_until` date NOT NULL,
  `comment` varchar(200) DEFAULT NULL,
  `download_count` int(11) DEFAULT '0',
  `notify_uploader` tinyint(1) DEFAULT '0',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `extends_count` int(11) DEFAULT '0',
  `password` varchar(40) DEFAULT NULL,
  `uploader_email` varchar(255) NOT NULL,
  `uploader_uid` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Structure de la table `fz_info`
--

CREATE TABLE IF NOT EXISTS `fz_info` (
  `key` varchar(30) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `fz_user`
--

CREATE TABLE IF NOT EXISTS `fz_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(40) DEFAULT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `quota` varchar(25) NOT NULL DEFAULT '2G',
  `is_admin` tinyint(4) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `username` (`username`,`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


INSERT INTO `fz_info` (`key`, `value`) VALUES ('cron_freq', NOW());
INSERT INTO `fz_info` (`key`, `value`) VALUES ('db_version', '3.0-alpha') ON DUPLICATE KEY UPDATE value = '3.0-alpha';

INSERT INTO `fz_user` (`username`, `password`, `firstname`, `lastname`, `email`, `is_admin`) 
VALUES ('admin', SHA1('filez'), 'FileZ', 'admin', 'foo@bar.com', 1);a

ALTER TABLE fz_file
ADD CONSTRAINT fz_file_ibfk_1 FOREIGN KEY (created_by) REFERENCES fz_user (id);

