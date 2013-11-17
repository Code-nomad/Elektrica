<?php

ini_set('max_execution_time', 300); //300 seconds = 5 minutes

//dependencies
require(dirname(__FILE__) . "/simple_html_dom.php");

class  ChargePoint
{

	public $name = "";
	public $city = "";
	public $district = "";
	public $url = "";
	
	public $zip = "";
	public $street = "";
	
	function __construct($name, $city, $district, $url) {
		$this->name = $name;
		$this->url = $url;
		$this->city	= $city;
		$this->district = $district;
	}
}

class Crawler
{
	public $url = '';
	public $response = null;
	public $result = null;
	
	public function __construct($url)
	{
		$this->url = $url;
	}
	
	public function getData($url)
	{
		$this->response = file_get_html( $this->url . $url );
		$i=0;
		
		foreach($this->response->find('tr') as $element) 
		{
			if(!is_null($element->find('td span',0)) 
				&& trim(strtolower($element->find('td span',0)->plaintext)) != "naam" 
				&& trim($element->find('td span',3)->plaintext) == "auto"
			) {
			
				$p = new ChargePoint(
					trim( strtolower ( $element->find('td span',0)->plaintext)),
					trim( strtolower ( $element->find('td span',1)->plaintext)), 
					trim( strtolower ( $element->find('td span',2)->plaintext)),
					$element->find('td span a',0)->href
				);
				$this->extend($p);
				$this->result[] = $p; 
			}	
		}
		 
	}
	
	private function extend(&$obj){
		$details = file_get_html($this->url . urldecode($obj->url) );
		preg_match('/Adres: (.*) Postcode en Gemeente: (\d*)/', preg_replace('/\s+/', ' ',$details->find('.mainContent_column .mainContent_column_inlay',0)->plaintext), $matches);
		
		$obj->street = $matches[1];
		$obj->zip = $matches[2];
		
	}
}

header('Content-Type: application/json');
$c = new Crawler("http://www.oplaadpunten.org/");
$c->getData("Oplaadpunten-Antwerpen.php");


print json_encode($c->result) 


?> 