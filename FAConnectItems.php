<?php
if (!class_exists("FAConnectItems"))
{
	class FAConnectItems {
		
		public $options;
		
		public $total;
		public $results;
		public $obj;
		public $imgobj;
		
		public $html;
		public $details_flag = false;
		public $pg;
		
		public $pricedisplay;	
		
		public $lparm;
		public $separator = '&';
		
		public $defimg;
		public $cat_tmp;
		
		public $category_tag = array("","");	
		
		function __construct($att=null) {
			
			$this->pg 	= get_query_var('page') ? get_query_var('page') : 1;
			
			if(is_array($att)) 
			{ 
				$this->options		= $att;
			}
			//$this->set_url_separator();
			$this->set_link_params();
			
			$this->obj = new FAConnectItemHtml;
			
		}


		function setDefaltImage($h,$w) {
			
			//$this->defimg = $this->obj->gallery_image( plugins_url( 'FACImage.php' , __FILE__ ), $h, $w  );
			
			//$this->defimg = $this->obj->gallery_image( plugins_url( 'noimage.gif' , __FILE__ ), $h, $w  );
			
			$this->defimg = "http://dummyimage.com/720x480/fff/c0c0c0.gif&text=Image+Not+Available";			

			
		}

		function get_img_src($results) {
			return $this->options["fac_store_url"]."/".$this->options["fac_prod_img_folder"]."/".$results['stock_id'].".jpg";
		}
		function prepare_document() {
			
			$dataloop 	= null; 

			/*echo "<pre>";
			var_dump(is_array($this->results));
			echo "</pre>";*/	
			
			if( is_array($this->results) ) {
				$dataloop 	= $this->item_display_loop( $this->results );
			}
			
			//$this->set_url_separator();
			$this->set_link_params();
			
			$tokens = array (
				"/<fadataloop\/>/i" 	=> "$dataloop",	
				"/<fapermalink\/>/i" 	=> get_permalink(),
				"/<curr_abrev\/>/i" 	=> $this->options['fac_curr_abrev'],
				"/<curr_symbol\/>/i" 	=> $this->options['fac_curr_symbol'],
				"/<tax_rate\/>/i"		=> $this->options['fac_tax_rate'],
				"/<tax_name\/>/i"		=> $this->options['fac_tax_name'],
				"/<image\/>/i"			=> null,
			);
			return $tokens;
		}
		
		function price($results) {
			
			$price = 0;
			$markup = $this->options['fac_add_pct'];
			
			if( $results["price"] ) {
				$price = explode(",",$results["price"]);
				//var_dump($price);
			}else
			if( $results["material_cost"] ) {
				$price = array_sum( array($results["material_cost"], $results["labour_cost"], $results["overhead_cost"]) );
				if($markup){
					$price = $this->add_markup($markup, $price);
				}
			}else
			if( $results["actual_cost"] ) {
				$price = $results["actual_cost"];
				if($markup){
					$price = $this->add_markup($markup, $price);
				}							
			}else{
				$price = $results["std_cost_unit"];
				if($markup){
					$price = $this->add_markup($markup, $price);
				}							
			}			
			return $price;
		}

		/*function set_url_separator()
		{
			$this->separator = "?";				
			if ( strpos( get_permalink(), '?') ) {
				$this->separator = "&";
			}		
		}*/		
		function add_markup($markup, $price) {
			return $price += $price * ($markup/100);
		}
		function round_to_nearest( $price )
		{
			$round_to = $this->options['fac_round_to'];
		
			if ($price == 0)
				return 0;
			$pow = pow(10, $this->options['fac_price_dec'] );
			if ($pow >= $round_to)
				$mod = ($pow % $round_to);
			else
				$mod = ($round_to % $pow);
			if ($mod != 0)
				$price = ceil($price) - ($pow - $round_to) / $pow;
			else	
				$price = ceil($price * ($pow / $round_to)) / ($pow / $round_to);
			return $price;
		}		
		function http_file_exists($url)
		{
			$f=@fopen($url,"r");
			if($f)
			{
				fclose($f);
				return true;
			}
			return false;
		}
		function is_multi_array($array) {
			return ( count($array) != count($array, 1) );
		}
		function get_image($id){
		
			$match = "/^(http|https)/";
			$url = preg_match($match, $this->options["fac_store_url"] ) ? $this->options["fac_store_url"] : "http://".$this->options["fac_store_url"];
			
			return $url."/".$this->options["fac_prod_img_folder"]."/".$id.".jpg";
		}
		function get_shortdesc() {	
			return $this->results['description'];		
		}
		function get_longdesc() {
			return $this->results['long_description'];
		}
		function get_price() {
			$pri = $this->price($this->results);
			if( is_array($pri) ) {
				return null;
			}
			return $this->price($this->results);
		}
		function get_units(){
			return $this->results['units'];
		}
		function get_category_desc($id) {
			if($id) {			
				$fac_category_table = $this->options['fac_category_table'];		
				if( is_array($fac_category_table) ) 
				{
					foreach($fac_category_table as $value)
					{
						if( $value['category_id'] == $id ) {
							return $value['description'];						
						}
					}
				}
			}		
		}
		function get_tax_type_row( $id ) {
			if($id) {			
				$fac_tax_array = $this->options['fac_tax_array'];		
				if( is_array($fac_tax_array) ) 
				{
					foreach($fac_tax_array as $value)
					{
						if( $value['id'] == $id ) {
							return $value;						
						}
					}
				}
			}
		}
		
		function tax_exempt_status($tax_id) {
				
			$tax_type_row = $this->get_tax_type_row( $tax_id );
			
			return $tax_type_row['exempt'];
		}	
			
		function get_sales_type_row($id) {
			if($id) {
				$fac_sales_types_array 	= $this->options['fac_sales_types_array'];				
				if( is_array($fac_sales_types_array) ) {
					foreach($fac_sales_types_array as $value)
					{		
						if( $value['id'] == $id ) {
							return $value;						
						}				
					}
				}	
			}		
		}
		function set_pricedisplay($id=null) {	

			if($id){
				$tax_type_row = $this->get_tax_type_row( $id );	
			}			
			$this->pricedisplay = null;
			if( is_user_logged_in() ) {
				if( ! $tax_type_row['exempt'] ){
					$this->pricedisplay = $this->obj->price_display_tax();
				}else{
					$this->pricedisplay = $this->obj->price_display_taxincluded();				
				}				
			}		
		}
		function set_link_params() {
			
			$this->lparm = $this->separator."stock_id=<stock_id/>";		
		}
		
		function override_pg($pg){
			$this->pg = $pg;
		}
	
		function fa_get_total()
		{
			//Connect to the frontaccounting database
			//
		
			$obj	= new FAConnectDB($this->options);
			$fadb 	= $obj->dbObj();
		
			$limit 	= $this->options['fac_itemperpage'];
			$co		= $this->options['fac_dbtblpref'];	// Frontaccounting company id
			$page 	= $this->pg;
		
				
			if( $page > 1 ) {	$start 	= ($page - 1) * $limit; //first item to display on this page
			}else{	$start = 0; }
				
			$sql = <<<EOF
SELECT COUNT(DISTINCT sm.stock_id)
FROM
	mgadmin_frontaccounting.%d_stock_master sm
LEFT JOIN
	mgadmin_frontaccounting.%d_stock_moves smo
ON
	smo.stock_id = sm.stock_id
LEFT JOIN
	mgadmin_frontaccounting.%d_stock_category scat
ON
	scat.category_id = sm.category_id
LEFT JOIN
	mgadmin_frontaccounting.%d_purch_order_details pod
ON
	pod.item_code = sm.stock_id
LEFT JOIN
	mgadmin_frontaccounting.%d_prices pr
ON
	pr.stock_id = sm.stock_id
	AND
	pr.sales_type_id = 1
WHERE
	sm.inactive = 0
AND
	sm.no_sale = 0
AND
	( sm.material_cost > 0 OR pr.price > 0 )
		
EOF;
		
			$this->total = $fadb->get_var( $fadb->prepare($sql, $co, $co, $co, $co, $co) );
		}
/*
*
* all items
*
*/
		function fa_get_items()
		{
			//Connect to the frontaccounting database
			//

			$obj	= new FAConnectDB($this->options);
			$fadb 	= $obj->dbObj();

			$limit 	= $this->options['fac_itemperpage'];
			$co		= $this->options['fac_dbtblpref'];	// Frontaccounting company id
			$page 	= $this->pg; 

			
			if( $page > 1 ) {	$start 	= ($page - 1) * $limit; //first item to display on this page
			}else{	$start = 0; }
			
			$sql = <<<EOF
SELECT COUNT(DISTINCT sm.stock_id)
FROM
	mgadmin_frontaccounting.%d_stock_master sm
LEFT JOIN
	mgadmin_frontaccounting.%d_stock_moves smo
ON 
	smo.stock_id = sm.stock_id
LEFT JOIN
	mgadmin_frontaccounting.%d_stock_category scat
ON
	scat.category_id = sm.category_id	
LEFT JOIN
	mgadmin_frontaccounting.%d_purch_order_details pod
ON
	pod.item_code = sm.stock_id
LEFT JOIN
	mgadmin_frontaccounting.%d_prices pr
ON
	pr.stock_id = sm.stock_id 
	AND
	pr.sales_type_id = 1
WHERE
	sm.inactive = 0
AND
	sm.no_sale = 0
AND
	( sm.material_cost > 0 OR pr.price > 0 )	

EOF;

			$this->total = $fadb->get_var(
							$fadb->prepare($sql, $co, $co, $co, $co, $co)
									);									
			$sql = <<<EOF
SELECT
	sm.*,
	(SELECT GROUP_CONCAT(pri.price) 
		FROM mgadmin_frontaccounting.%d_prices pri 
		WHERE pri.stock_id = sm.stock_id) 
		AS price,
	(SELECT GROUP_CONCAT(pri.sales_type_id) 
		FROM mgadmin_frontaccounting.%d_prices pri 
		WHERE pri.stock_id = sm.stock_id) 
		AS salestypecombined,
	pr.sales_type_id,
	pod.unit_price,
	pod.std_cost_unit,
	scat.description as category,
	COALESCE( SUM(smo.qty), 0) as qty 
FROM
	mgadmin_frontaccounting.%d_stock_master sm
LEFT JOIN
	mgadmin_frontaccounting.%d_stock_moves smo
ON 
	smo.stock_id = sm.stock_id
LEFT JOIN
	mgadmin_frontaccounting.%d_stock_category scat
ON
	scat.category_id = sm.category_id	
LEFT JOIN
	mgadmin_frontaccounting.%d_purch_order_details pod
ON
	pod.item_code = sm.stock_id
LEFT JOIN
	mgadmin_frontaccounting.%d_prices pr
ON
	pr.stock_id = sm.stock_id 
	AND
	pr.sales_type_id = 1
WHERE
	sm.inactive = 0
AND
	sm.no_sale = 0
AND
	( sm.material_cost > 0 OR pr.price > 0 )

GROUP BY
	1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26
ORDER BY
    category
LIMIT %d, %d

	
EOF;
			
			
			$this->results = $fadb->get_results(
							$fadb->prepare($sql, $co, $co, $co, $co, $co, $co, $co, $start, $limit), ARRAY_A );
			/*
			echo "<pre>";
			var_dump($fadb->prepare($sql, $co, $co, $co, $co, $co, $co, $start, $limit));
			echo "</pre>";
			*/
		}
	}
}
?>