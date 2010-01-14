DROP TABLE IF EXISTS `ipn_requests_details`;
CREATE TABLE IF NOT EXISTS `ipn_requests_details` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ipn_request_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `type` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ipn_id_name_value` (`ipn_request_id`,`name`,`value`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


DROP TABLE IF EXISTS `ipn_requests`;
CREATE TABLE IF NOT EXISTS `ipn_requests` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `verified` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `ipn_queue`;
CREATE TABLE IF NOT EXISTS `ipn_queue` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `ipn_request_id` int(10) unsigned NOT NULL default '0',
  `processed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `ipn_request_id` (`ipn_request_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `ipn_error_log`;
CREATE TABLE IF NOT EXISTS `ipn_error_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `type` varchar(30) NOT NULL default '',
  `details` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
