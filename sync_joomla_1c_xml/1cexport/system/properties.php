<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: system/manufacture.php - Класс создания производителей
// Автор оригинала: Дуденков М.В. (email: mihail@termservis.ru)
// Помогали разрабатывать:	Alexandr Datsiuk
//							Павел Михнев 
//                          CALEORT
// Авторские права: Использовать, а также распространять данный скрипт
// 					разрешается только с разрешением автора скрипта
//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

$prop = new XMLReader();

function inserProperties($xml){
	$read_prop = new XMLReader();
	$read_prop->XML($xml);
	
	while($read_prop->read())
	{
		if($read_prop->nodeType == XMLReader::ELEMENT) 
		{
			switch($read_prop->name)
			{
			case 'Свойство':		
				inserProperty($read_prop->readOuterXML());
				break;
			}
		}

	}
}
function inserProperty($xml){
	global $log, $db, $dba, $id_admin, $prop;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
					
	$data = array();

	$data['id'] = "";
	$data['name'] = '';
	
	$data['field_type'] = "";
	$data['custom_jplugin_id'] = "";
	$data['custom_element'] = "";
	$data['custom_title'] = "";
	$data['custom_value'] = "";
	$data['custom_params']= "";
		
	$prop->XML($xml);			
	while($prop->read()) 
	{
		if($prop->nodeType == XMLReader::ELEMENT ) 
		{
			switch($prop->name) 
			{
					
				case 'Ид': 
					$data['id'] = $prop->readString();
					break;
									
				case 'Наименование':
					$data['name'] = trim($prop->readString());
					
					break;
									
				case 'ТипЗначений': 
					$str = trim($prop->readString());
					if($str == "Число"){ $data['field_type'] = 'I'; }
					else if($str == "Строка"){ $data['field_type'] = 'S'; }
					else { $data['field_type'] = 'E'; }
					break;
			}
		}
	}
	
	makeProperty($data);
}
function makeProperty($data){
	global $log, $db, $dba, $id_admin;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	
	if (isset($data['name']) and $data['name'] != '' and isset($data['field_type']) and $data['field_type'] != "")
	{
		
		$sql = "SELECT custom_id FROM #__".$dba['customs_to_1c_db']." where `c_custom_id` = '" . $data['id'] . "'";
		$db->setQuery ( $sql );
		$rows_sub_Count = $db->loadResult ();
		
		if (isset($rows_sub_Count) and $rows_sub_Count != '')
		{
			//Обновляем
		}
		else
		{
			//Добавляем
			$ins = new stdClass ();
			if (VM_VERVM == '2')
			{
				$ins->virtuemart_custom_id = NULL; 
				$ins->custom_parent_id  = '1'; 
				$ins->custom_jplugin_id = '10046'; //id плагина CMS
				$ins->custom_element = 'param';
				$ins->custom_title = $data['name'];
				$ins->custom_tip = $data['name'];
				$ins->custom_value = 'param';
				$ins->field_type = 'E';
				$ins->field_type_custom = $data['field_type'];
				$ins->custom_field_desc = $data['name'];
				
				$custom_params_array = array();
				//$name = $data['name'];
				//$name = str_replace("'", "", $name);
				
				$name = $data['name'];
				$name = json_encode(iconv('utf-8', 'utf-8', $name));
				//$name = json_encode($name);
				//echo json_encode($name)."<br/>";
				//echo $name;
				//$name = implode(json_encode(split($name)));
				//echo
				//$gfff=;
				//
				$custom_params_array[] = 'n='.$name.'';
				$custom_params_array[] = 's="1"';
				//$custom_params_array[] = 'l="0"';
				if($data['field_type'] == "S")
				{
					$custom_params_array[] = 'ft="text"';
				}
				else if($data['field_type'] == "I")
				{
					$custom_params_array[] = 'ft="int"';
				}
				else
				{
					$custom_params_array[] = 'ft="text"';
				}
				if($data['field_type'] == "S"){
					$custom_params_array[] = 't="checkbox"';
				}
				else if($data['field_type'] == "I"){
					$custom_params_array[] = 't="slider_double"';
				}
				else{
					$custom_params_array[] = 't="checkbox"';
				}
				$custom_params_array[] = 'm="OR"';
				$custom_params_array[] = 'af="0"';
				$custom_params_array[] = 'av=""';
				$custom_params_array[] = 'ld=""';
				$custom_params_array[] = 'z="default"';
				
				$ins->custom_params = implode("|", $custom_params_array);
				
				$ins->admin_only ='0';
				$ins->is_hidden = '0';
				$ins->is_list = '0';
				$ins->is_cart_attribute = '0';
				$ins->layout_pos ='';
				$ins->shared = '0';
				
				$ins->published = '1';
				$ins->created_on = date ('Y-m-d H:i:s');
				$ins->created_by = $id_admin;
				$ins->modified_on = date ('Y-m-d H:i:s');
				$ins->modified_by = $id_admin;
				$ins->show_title = '1';
				$ins->ordering = '0';
				$ins->locked_on = date ('Y-m-d H:i:s');
				$ins->locked_by = $id_admin;
			}
			else
			{
				/*$ins->custom_id = NULL;
				$ins->mf_name = $data['name'];
				$ins->mf_email = '';
				$ins->mf_desc = $data['name'];
				$ins->mf_category_id = '0';
				$ins->mf_url = '';*/
			}

			if (! $db->insertObject ( '#__'.$dba['customs_db'], $ins, $dba['pristavka']."custom_id" )) 
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['customs_db'] ) );
				
				if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql';
				}
				else
				{
					$logs_http[] = "<strong>Свойства</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['customs_db']."</strong>";
				}
				die;
			}
			
			$cus_id = ( int ) $ins->virtuemart_custom_id;
			$ins = new stdClass ();
			$ins->custom_id = $cus_id;
			$ins->c_custom_id = $data['id'];
			
			if (! $db->insertObject ( '#__'.$dba['customs_to_1c_db'], $ins )) 
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно вставить запись в таблицу - '.$dba['customs_to_1c_db'] ) );
					
				if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql';
				}
				else
				{
					$logs_http[] = "<strong>Свойства</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['customs_to_1c_db']."</strong>";
				}
				die;
			}
		
			$log->addEntry ( array ('comment' => 'Этап 4.1.3) Свойство '.$data['name'].' создан' ) );
			$logs_http[] = "<strong>Свойства</strong> - Свойство <strong>".$data['name']."</strong> создан";
		
		}
	}

}
function updateParametresProperties(){

	global $log, $db, $dba, $id_admin;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	//$db =& JFactory::getDBO();
	
	if (VM_VERVM == '2')
	{
		$sql = "SELECT * FROM #__".$dba['customs_db']." where `custom_parent_id` = '29'";
		$db->setQuery ( $sql );
		$rowsCustoms = $db->loadObjectList();
		//echo $rowsCustoms;
		if( count($rowsCustoms) > 0 )
		{
			foreach( $rowsCustoms as $rowC )
			{
				$selected_field = "";
				if($rowC->field_type_custom == "I")
				{
					$selected_field = "intvalue";
				}
				else if($rowC->field_type_custom == "S")
				{
					$selected_field = "value";
				}
				else
				{
					$selected_field = "value";
				}
				//
				
				$custom_params = updateParametresProperty($rowC->virtuemart_custom_id, $selected_field);
				$key = "|";
				$custom_params_db = explode("|", $rowC->custom_params);
				//
				$custom_params_array = array();
				//
				//$name = json_encode($custom_params_db[0]);
				$custom_params_array[] = $custom_params_db[0];//'n="'.$data['name'].'"';
				$custom_params_array[] = $custom_params_db[1];//'s="'.$data['name'].'"';
				//$custom_params_array[] = $custom_params_db[2];//'l="'.$data['name'].'"';
				$custom_params_array[] = $custom_params_db[2];//'ft="int"';
				$custom_params_array[] = $custom_params_db[3];//'t="checkbox"';
				$custom_params_array[] = $custom_params_db[4];//'m="OR"';
				//$custom_params_array[] = 'vd="'.$custom_params.'"';//'vd=""';
				$custom_params_array[] = $custom_params_db[5];//'af="0"';
				$custom_params_array[] = $custom_params_db[6];//'av=""';
				$custom_params_array[] = $custom_params_db[7];//'z="default"';

				$str = implode($key, $custom_params_array);
				//
				$sql = "UPDATE `#__".$dba['customs_db']."` SET `custom_params` = '".$str."' where `virtuemart_custom_id` = '".$rowC->virtuemart_custom_id."'";
				$db->setQuery ( $sql );
				$db->query ();
			}
		}
	}
}
function updateParametresProperty($id, $field){

	global $log, $db, $dba, $id_admin;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	$str="";
	
	$sql = "SELECT ".$field." FROM #__".$dba['customfields_plg_db']." where `virtuemart_custom_id` = '".$id."'";
	$db->setQuery ( $sql );
	$rowsValues = $db->loadObjectList();
	
	//echo $field;
	if($field == "value"){
		$str = implode(";", getValues($rowsValues));
	}
	if($field == "intvalue"){
		$str = implode(";", getValues($rowsValues));
	}
	return $str;
}
function getValues($rows){

	$values = array();
	
	foreach($rows as $row)
	{
		$value = $row->intvalue;
		if($value!='')
		{
			$check = 0;
			foreach($values as $val)
			{
				if($value == $val)
				{
					$check = $check + 1;
				}
			}
			if($check < 1)
			{
				$values[] = $value;
			}
		}
	}
	
	return asort($values);
}
function json_encode_cyr($str){
	global $log, $db, $dba, $id_admin;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	$str_json = "";
	$str_array = str_split($str);
	
	foreach($str_array as $str_a)
	{
		$json_code = json_encode($str_a);
		$str_json .= "\\".$json_code;
		
		//echo $str_json;
	}
	
	
	
	/*$arr_replace_utf = array('\u0410', '\u0430','\u0411','\u0431','\u0412','\u0432',
	'\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416',
	'\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a',
	'\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f',
	'\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443',
	'\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428',
	'\u0448','\u0429','\u0449','\u042a','\u044a','\u042d','\u044b','\u042c','\u044c',
	'\u042d','\u044d','\u042e','\u044e','\u042f','\u044f');
	$arr_replace_cyr = array('А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е',
	'Ё', 'ё', 'Ж','ж','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н','О','о',
	'П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ч','ч','Ш','ш',
	'Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я');*/
	//$str1 = json_encode($str);
	//$str2 = str_replace($arr_replace_cyr,$arr_replace_utf,$str);
	//$str2 = str_replace($arr_replace_utf,$arr_replace_cyr,$str1);
	$logs_http[] = $str."<br/>";
	$logs_http[] = $str_json;
	
	return $str_json;
}
?>