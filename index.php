<?php
ini_set("max_execution_time", "150");
$g = $_SERVER['QUERY_STRING'];
libxml_use_internal_errors(true);

function getDep($g,$ni){
$src = file_get_contents("http://wap.nationalrail.co.uk/ld/find/?f=" . "$g" . "&i=" . $ni . "&uid=439");
$dom = new DOMDocument;
$dom->loadHTML($src);
$items = $dom->getElementsByTagName('a');
$deps = array();
for ($i = 0; $i < $items->length; $i++){
        $value = $items->item($i)->nodeValue;
        if($value != 'Refresh' && $value != 'Arrivals' && $value != 'Other Stations' && $value != 'Trains' && $value != 'More'){
        $exp = false;
        $status = '';
        if (strpos($value,' Exp ') !== false) {
         preg_match("/Exp\s*\d{2}:\d{2}/" , $value, $match);
         $value = preg_replace("/Exp\s*\d{2}:\d{2}/",'',$value);
         $parts = explode(" ",$match[0]);
         $status = str_replace('Exp','Late',$parts[0]);
         $exp = $parts[1];
}else if (strpos($value,'On time') !== false) {
         $value = str_replace('On time','',$value);
         $status = 'On time';
}else if (strpos($value,'Starts here') !== false) {
         $value = str_replace('Starts here','',$value);
         $status = 'Starts here';
}else if (strpos($value,'Delayed') !== false) {
         $value = str_replace('Delayed','',$value);
         $status = 'Delayed';
}else if (strpos($value,'CANCELLED') !== false) {
         $value = str_replace('CANCELLED','',$value);
         $status = 'CANCELLED';
}else if (strpos($value,'No report') !== false) {
         $value = str_replace('No report','',$value);
         $status = 'No report';
}
if($exp){
$minarray = array(
  "due"=>explode(' ', $value)[0],
  "to"=>trim(str_replace(explode(' ', $value)[0], '', $value)),
  "status"=>$status,
  "est"=>$exp);
     
} else {
$minarray = array(
  "due"=>explode(' ', $value)[0],
  "to"=>trim(str_replace(explode(' ', $value)[0], '', $value)),
  "status"=>$status,
  "est"=>explode(' ', $value)[0]);
}
array_push($deps, $minarray);   
}
}
return $deps;
}

function getArr($g,$ni){
$src = file_get_contents("http://wap.nationalrail.co.uk/la/find/?f=" . "$g" . "&i=" . $ni . "&uid=439");
$dom = new DOMDocument;
$dom->loadHTML($src);
$itemss = $dom->getElementsByTagName('p');
$itemsa = explode("sourced from National Rail Enquiries", $itemss->item(0)->nodeValue)[1];
$itemsb = explode("<br/>", $itemsa)[0];
$items = preg_split("/\s{2}(?=\d{2}:\d{2})/m", $itemsb);
$deps = array();
for ($i = 1; $i < count($items); $i++){
        $value = trim($items[$i]);
        if($value != 'Refresh' && $value != 'Arrivals' && $value != 'Other Stations' && $value != 'Trains' && $value != 'More'){
        
        $exp = false;
        $status = '';
if (strpos($value,' Exp ') !== false) {
         preg_match("/Exp\s*\d{2}:\d{2}/" , $value, $match);
         $value = preg_replace("/Exp\s*\d{2}:\d{2}/",'',$value);
         $parts = explode(" ",$match[0]);
         $status = str_replace('Exp','Late',$parts[0]);
         $exp = $parts[1];
}else if (strpos($value,'On time') !== false) {
         $value = str_replace('On time','',$value);
         $status = 'On time';
}else if (strpos($value,'Starts here') !== false) {
         $value = str_replace('Starts here','',$value);
         $status = 'Starts here';
}else if (strpos($value,'Delayed') !== false) {
         $value = str_replace('Delayed','',$value);
         $status = 'Delayed';
}else if (strpos($value,'CANCELLED') !== false) {
         $value = str_replace('CANCELLED','',$value);
         $status = 'CANCELLED';
}else if (strpos($value,'No report') !== false) {
         $value = str_replace('No report','',$value);
         $status = 'No report';
}
if($exp){
     $minarray = array(
  "due"=>explode(' ', $value)[0],
  "from"=>trim(str_replace("More","",str_replace("Refresh\n\nDepartures\n\nOther Stations\n\nTrains","",str_replace(explode(' ', $value)[0], '', $value)))),
  "status"=>$status,
  "est"=>$exp);
     } else {
     $minarray = array(
  "due"=>explode(' ', $value)[0],
  "from"=>trim(str_replace("More","",str_replace("Refresh\n\nDepartures\n\nOther Stations\n\nTrains","",str_replace(explode(' ', $value)[0], '', $value)))),
  "status"=>$status,
  "est"=>explode(' ', $value)[0]);
  
  
     }
array_push($deps, $minarray);      
}
}
return $deps;
}
echo json_encode(array("dep"=>array_merge(getDep($g,0) , getDep($g,5)),"arr"=> array_merge(getArr($g,0) , getArr($g,5))));
?>