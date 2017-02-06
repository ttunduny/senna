CREATE TABLE `ospos_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial_no` varchar(30) NOT NULL,
  `date_of_purchase` date NOT NULL,
  `name` varchar(45) NOT NULL,
  `category` int(11) NOT NULL,
  `depreciation` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `resale_price` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1