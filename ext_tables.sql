#
# Table structure for table 'tx_cymessageboard_domain_model_message'
#
CREATE TABLE tx_cymessageboard_domain_model_message (

	user int(11) unsigned DEFAULT '0',
	text MEDIUMTEXT, 
	timestamp TIMESTAMP,
	changed SMALLINT (5) UNSIGNED DEFAULT '1' NOT NULL,
	expiry_date DATE DEFAULT NULL,
	UNIQUE KEY user (user),
	
);

#
# Table structure for table 'fe_users' 
#
CREATE TABLE fe_users (
   info_mail_when_message_board_changed  SMALLINT (5) UNSIGNED DEFAULT '1' NOT NULL,
);


