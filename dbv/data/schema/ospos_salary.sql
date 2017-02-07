CREATE TABLE `ospos_salary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `gross_sal` int(11) NOT NULL,
  `tax` int(11) NOT NULL,
  `nhif` int(11) NOT NULL,
  `nssf` int(11) NOT NULL,
  `pay_date` varchar(100) NOT NULL,
  `net_sal` int(11) NOT NULL,
  `isDeleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1