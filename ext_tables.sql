#
# Table structure for table 'tx_orphanfiles_queue'
#
CREATE TABLE tx_orphanfiles_queue (
  uid int(11) NOT NULL auto_increment,
  crdate int(11) DEFAULT '0' NOT NULL,

  file_path tinytext,
  #file_size tinytext,

  PRIMARY KEY (uid)
);

#
# Table structure for table 'tx_orphanfiles_process'
#
CREATE TABLE tx_orphanfiles_process (
  uid int(11) NOT NULL auto_increment,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,

  active tinyint(4) DEFAULT '0' NOT NULL,
  filecount int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid)
) ENGINE=InnoDB;