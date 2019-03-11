#
# Table structure for table 'tx_documentstorage_domain_model_document'
#
CREATE TABLE tx_documentstorage_domain_model_document (

                                                        id varchar(255) DEFAULT '' NOT NULL,
                                                        db varchar(255) DEFAULT '' NOT NULL,
                                                        data_protected text,

                                                        UNIQUE KEY guid (db, id),
                                                        KEY db (db),
                                                        KEY id (id)
);
