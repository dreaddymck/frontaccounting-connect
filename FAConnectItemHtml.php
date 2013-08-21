<?php

if (!class_exists("FAConnectItemHtml")) 
{
	class FAConnectItemHtml extends FAConnectItems {
		
		public $obj = null;
		
		function __construct() {}

		
		function the_content()
		{
			$template = $this->get_template("fac-the-content");
				
			if( !empty( $template) ) {
				return trim( $template);
			}
				
			return <<<EOF

<image/>

<the_content/>

<fac_custom_content_1/>

<p>	
	<strong class='fac-price-display'><curr_symbol/><price/> </strong> <units/> ( <small><tax_name/> INC.</small> ) 
	<br>
	<small>stock id: # <stock_id/></small>
</p>




		
EOF;
		}			
		function widget_content() 
		{
			$template = $this->get_template("fac-widget-content");
			
			if( !empty( $template) ) {
				return trim( $template);
			}	
		
return 	<<<EOF

<li>
	<a href="<permalink/>"><image/></a>
	<br>
	<a href="<permalink/>"><title/></a>	

	<br>
	<strong class='fac-price-display'><curr_symbol/><price/> </strong> <units/> ( <small><tax_name/> INC.</small> )	
	
	<br>
	# <stock_id/>	
</li>
		
EOF;

		}	
		function shortcode_content() {
			
			$template = $this->get_template("fac-shortcode-content");
				
			if( !empty( $template) ) {
				return trim( $template);
			}
			
			return 	<<<EOF
			
<li>
	<a href="<permalink/>"><image/></a>
	<br>
	<a href="<permalink/>"><title/></a>
			
	<br>
	
	<the_content/>
	
	<br>
	
	<strong class='fac-price-display'><curr_symbol/><price/> </strong> <units/> ( <small><tax_name/> INC.</small> )
			
	<br>
	# <stock_id/>
</li>
			
EOF;
			
		}	
		
		
		function get_template($name=null) {
			if(empty($name)) { 
				return null;
			}
			$tmplte = get_query_template($name);
			return !empty($tmplte) ? file_get_contents($tmplte) : null;		
		}
	}	
}

?>