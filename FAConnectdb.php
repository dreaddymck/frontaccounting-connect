<?php

if (!class_exists("FAConnectDB")) {

	class FAConnectDB {
		
		public $att;
		
		public function __construct($att) {
			$this->att 	= $att;
		}

		public function dbObj() {
			
			return new wpdb(	
							$this->att['fac_dbuser'],
							$this->att['fac_dbpwd'],
							$this->att['fac_dbname'],
							$this->att['fac_dbhost'] 
							);
		}
	}
}
?>