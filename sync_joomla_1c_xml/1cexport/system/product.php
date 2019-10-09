<?php


if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

global $product_parametres, $data, $system_data, $sqlObjectID;

function inserProducts($xml, $modif='false'){

	global $log, $db, $product, $dba, $id_admin, $lang_1c, $data, $product_parametres, $system_data, $sqlObjectID;
	
	$read_products = new XMLReader();

	$read_products->XML($xml);
	
	while($read_products->read())
	{
		if($read_products->nodeType == XMLReader::ELEMENT) 
		{
			switch($read_products->name)
			{
				case 'Товар':
					//try{
					$read_product = new XMLReader();
					$read_product = $read_products->readOuterXML();
					inserProduct($read_product,$modif);
					//} catch (Exception $e){
					//	$log->addEntry ( array ('comment' => 'Error'.$e) );
					//}
					$data=NULL;
					$system_data=NULL;
					$product_parametres=NULL;
					$sqlObjectID = 0;
					$product_groups = NULL;
					unset($read_product);
					//
					$read_products->next();
					
				break;
			}
		}

	}
}
function inserProduct($xml_pr,$modif='false'){
	global $log, $db, $product, $dba, $id_admin, $lang_1c, $data, $product_parametres, $system_data, $sqlObjectID;
	
	$data = array();
	$system_data = array();
	$system_data['change'] = false;
	$system_data['small_img'] = "";
	$system_data['mimetype'] = "";
	$system_data['del_img'] = false;
	$system_data['tbn_img'] = "";
	$system_data['meta_img'] = "";
	$product_parametres = array();
	$sqlObjectID = 0;
	
	$data['id'] = "";
	$data['current_id'] = 0;
	$data['media_id'] = 0;
	$data['uuid'] = "";
	$data['name'] = "";
	$full_name = "";
	$data['full_name'] = "";
	$data['full_name_2'] = "";
	$data['baz_ed'] = "";
	$data['art'] = "";
	$data['model'] = "";
	$data['description'] = "";
	$data['description_file'] = "";
	$data['s_description'] = "";
	$data['html_description'] = "";
	$data['status'] = "";
	$data['nds'] = "";
	$data['ves'] = "";
	$data['packaging'] = "0";
	$data['min_order_level']="";
	$data['image'] = "";
	$data['files'] = "";
	$data['category_1c_id'] = array();
	$data['category_id'] = 0;
	$product_groups = array();
	$data['manufacturer_1c_id'] = "";
	//
	$data['custom_1c_id'] = "";
	$data['custom_id'] = '';
	$data['custom_val'] = '';
	//
	$data['slug'] = "";
	$data['metakey'] = "";
	$data['metadesc'] = "";
	$data['metarobot'] = "";
	$data['metaauthor'] = "";
	$data['manufacturer'] = "";
	$data['manufacturer_id'] = "";
	$data['published'] = "0";
	$data['product_published'] = "0";
	$data['hash'] = "";
	$data['hash_db'] = "";
	
	$harakt[]='';
	$fileExtentionNow="";
	$PROPERTIES = array();
	if($xml_pr == NULL) return;
	$product = new XMLReader();
	$product->XML($xml_pr);
	//$nds_xml = new XMLReader();					
	//$log->addEntry ( array ('comment' => '$product '.$product) );
	
	while($product->read()) 
	{
		//$log->addEntry ( array ('comment' => '$product->nodeType '.$product->nodeType) );
		if($product->nodeType == XMLReader::ELEMENT ) 
		{
			//$log->addEntry ( array ('comment' => '$product->name '.$product->name.' $product->nodeType '.$product->nodeType) );
			switch($product->name) 
			{	
				case 'Ид':
					//Берем первую часть uuid т.к. могут быть и uuid#id
					$uuid = explode("#", $product->readString());
					$data['id'] = (string)$uuid[0];
					$data['uuid'] = (string)$uuid[0];
					
					//
					//
					$product->next();
					//$log->addEntry ( array ('comment' => '$uuid'.$uuid ) );
					break;
									
				case 'Наименование':
					$data['name'] = (string)$product->readString();
					$data['name'] = trim($data['name']);
					$product->next();
					break;
					
				case 'ПолноеНаименование':
					$data['full_name'] = trim((string)$product->readString());
					//$data['name'] = $data['full_name'];
					$product->next();
					break;
					
				case 'БазоваяЕдиница':
					//$data['baz_ed'] = (string)$product->readString();	
					//$role = $product->attributes();
					//$log->addEntry ( array ('comment' => 'БазоваяЕдиница') );
					$value_attribute = $product->getAttribute("НаименованиеПолное");
					//$value_attribute->БазоваяЕдиница->attributes()->НаименованиеПолное
					//$log->addEntry ( array ('comment' => 'Атрибут = '.$value_attribute) );
					/*foreach($product as $key => $value) {
						
						if($role == "НаименованиеПолное")
						{
							if($value == "Штука") $data['baz_ed'] = "шт.";
							if($value == "л") $data['baz_ed'] = "л.";
							if($value == "Килограмм") $data['baz_ed'] = "кг.";
							if($value == "Погонный метр") $data['baz_ed'] = "пог. м";
							if($value == "м2") $data['baz_ed'] = "м2";
							if($value == "Пара (2 шт.)") $data['baz_ed'] = "пара";
						}
					}*/
					if($value_attribute == "Штука") $data['baz_ed'] = "шт.";
					if($value_attribute == "л") $data['baz_ed'] = "л.";
					if($value_attribute == "Килограмм") $data['baz_ed'] = "кг.";
					if($value_attribute == "Погонный метр") $data['baz_ed'] = "пог. м";
					if($value_attribute == "м2") $data['baz_ed'] = "м2";
					if($value_attribute == "Пара (2 шт.)") $data['baz_ed'] = "пара";
					
					$product->next();
					break;
					
				case 'Артикул':
					$data['art'] = (string)$product->readString();
					$product->next();
					break;
									
				// Изображение
				case 'Картинка':
					//Обрабатываем несколько изображений
					if (isset($data['image']) AND $data['image'] <> "") 
					{
						$data['product_image'][] = (string)$product->readString();
					}
					else 
					{
						$data['image'] = (string)$product->readString();
					}
					$product->next();
					break;
				case 'Группы':
					$read_group = new XMLReader();
					$read_group->XML($product->readOuterXML());
					//$product_groups = array();
					while($read_group->read())
					{
						if($read_group->nodeType == XMLReader::ELEMENT)
						{
							switch($read_group->name)
							{
								case 'Ид':
									$catid = (string)$read_group->readString();
									$data['category_1c_id'][] = $catid;
									//if($data['id'] == "6ff7d733-138f-11e1-9f83-bcaec5992f29"){
										//$log->addEntry ( array ('comment' => 'Читаем группу '.$catid) );
									//}
								break;
							}
						}
					}
					//$xml = $product->readOuterXML();
					//$xml = simplexml_load_string($xml);
					//$data['category_1c_id'] = strval($xml->Ид);	
					
					//if (!isset($data['category_1c_id']))
					//{
					//	$data['category_1c_id'] = "";
					//}
					//unset($xml);
					//while
					$product->next();
					break;
					
				case 'Изготовитель':
					$read_manufes = new XMLReader();
					$read_manufes = $product->readOuterXML();
					//require_once(JPATH_BASE_1C .DS.'system'.DS.'manufacture.php');
					
					//inserManufacture($read_manufes);
					//
					$read_manufes_param = new XMLReader();
					$read_manufes_param->XML($read_manufes);			
					while($read_manufes_param->read()) 
					{
						if($read_manufes_param->nodeType == XMLReader::ELEMENT ) 
						{
							switch($read_manufes_param->name) 
							{
											
								case 'Ид': 
									$data['manufacturer_1c_id'] = $read_manufes_param->readString();
									//
									//$log->addEntry ( array ('comment' => 'Этап 4.1.23) Будем искать по базе - '.$data['manufacturer_1c_id'] ) );
									
									$rows_sub_Count = sqlloadResult($sql = "SELECT manufacturer_id FROM #__".$dba['manufacturer_to_1c_db']." where `c_manufacturer_id` = '" . $db->getEscaped($data['manufacturer_1c_id']) . "'", $db);
									//
									if(isset ( $rows_sub_Count )) 
									{
										$data['manufacturer_id'] = ( int )$rows_sub_Count;
										//$log->addEntry ( array ('comment' => 'Этап 4.1.23) Нашли производителя по базе - '.$data['manufacturer_id'].' $rows_sub_Count='.$rows_sub_Count ) );
									}
									else
									{
										$data['manufacturer_id'] = 0;
										//$log->addEntry ( array ('comment' => 'Этап 4.1.23) Не нашли производителя по базе - '.$data['manufacturer_id'].' $rows_sub_Count='.$rows_sub_Count ) );
									}
									
									//$logs_http[] = $data['id'];
									break;
													
							}
						}
					}
					
					//$log->addEntry ( array ('comment' => 'Этап 4.1.3) Вот такой производитель - '.$data['manufacturer_1c_id'].' и еще вот это'.$data['manufacturer_1c_id'] ) );
					$product->next();
					break;
				case 'ЗначенияСвойств':
					//
					
					//
					$read_prop = new XMLReader();
					$read_prop->XML($product->readOuterXML());
					$product_parametres = array();
					//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Свойства' ) );
						while($read_prop->read())
						{
							if($read_prop->nodeType == XMLReader::ELEMENT) 
							{
								switch($read_prop->name)
								{
									case 'ЗначенияСвойства':		
										addProperties($read_prop->readOuterXML());
										break;
								}
							}

						}
					$product->next();	
					break;
				/*case 'Модель':
					$data['model'] = (string)$product->readString();	
					//$product->next();
					break;*/
									
				case 'Статус':
					$data['status'] = (string)$product->readString();	
					
					$product->next();
					break;								
									
				/*case 'ХарактеристикиТовара':
					if(VM_XML_VERS == '203')
					{
						$xml = simplexml_load_string($product->readOuterXML());
					
						foreach($xml as $harakteristiki)
						{
							$namehar = ( string )$harakteristiki->Наименование;	
							$znachhar = ( string )$harakteristiki->Значение;
							
							for ($q=0; $q < count($lang_1c); $q++)
							{
								if($lang_1c[$q] == $namehar)
								{
									$harakt[$q] = $znachhar;
								}
							}
						}
		
						unset($xml);
							
					}
					$product->next();
					break;*/
									
				case 'СтавкаНалога':
					$nds_xml = new XMLReader();
					$nds_xml->XML($product->readOuterXML());
					while($nds_xml->read()) 
					{
						if($nds_xml->nodeType == XMLReader::ELEMENT ) 
						{
							switch($nds_xml->name) 
							{
								case 'Ставка':
									$data['nds_db'] = (int)$nds_xml->readString();
									$nds_xml->next();
									break;
								case 'Наименование':
									$data['nds_name'] = (string)$nds_xml->readString();
									$nds_xml->next();
									break;
							}
						}
					}
					unset($nds_xml);
					$product->next();
					break;

				case 'ЗначениеРеквизита':
					$xml_r = new XMLReader();
					$xml_r = simplexml_load_string($product->readOuterXML());
					//$log->addEntry ( array ('comment' => '$xml->Наименование'.$xml->Наименование ) );
					switch($xml_r->Наименование)
					{
						case 'Вес':
							$data['ves'] = (string)$xml_r->Значение;
						break;
						
						case 'Полное наименование':
							$full_name = trim((string)$xml_r->Значение);
							if($full_name != ""){
								if($full_name != $data['name']) $data['name'] = $full_name;
							}
							//$data['full_name_2'] = trim((string)$xml_r->Значение);
						break;
						
						case 'КоэффициентУпаковки':
							if(intval($data['packaging']) <= 0){
								$data['packaging'] = (string)$xml_r->Значение;
								$data['min_order_level'] = $data['packaging'];
							}
							//$logs_http[] = "package = ".$data['packaging'];
							//$log->addEntry ( array ('comment' => 'package = '.$data['packaging'] ) );
							
						break;						
						case 'Описание':
							$data['description'] = (string)$xml_r->Значение;
							//$product->next();
							break;
						case 'Файл':
							$val=substr ( (string)$xml_r->Значение, 16);
							//$data['image'] = substr ( $data['image'], 16 );
							$filepath = JURI::base( true ).JPATH_PICTURE.DS."files/".$val;
							$class_type_file="unknown";
							$file_info = pathinfo($filepath);
							$file_extention =  $file_info['extension'];
							//end(explode(".", $filepath));
							$ok="not";
							if(getAvailableExtension($file_extention))
							{
								$ok="yes";
								if($file_extention=="doc")$class_type_file="word";
								if($file_extention=="docx")$class_type_file="word_x";
								if($file_extention=="xls")$class_type_file="excel";
								if($file_extention=="xlsx")$class_type_file="excel_x";
								if($file_extention=="pdf")$class_type_file="pdf";
							}
							$data['files'].='</br></br><div class="'.$class_type_file.'"><a href="'.$filepath.'" target="_blank" title="Техническое описание">Техническое описание</a></div><br/>';
							if(strlen($val)>0) 
							{
								//$data['description_file'].= $data['description'].
								$data['description'].=$data['files'];
							}
						break;
						case 'КороткоеОписание':
							$data['s_description'] = (string)$xml_r->Значение;
							//$product->next();
							break;
						case 'HTMLОписание':
							$data['html_description'] = (string)$xml_r->Значение;	
							//$product->next();
							break;
					}

					unset($xml_r);
					unset($xml);
					$product->next();
					break;
			}
		}
	}
	
	if (VM_VERVM == '2' and isset($namehar) and $namehar != '')
	{
		//require_once(JPATH_BASE_1C .DS.'system'.DS.'customfields.php');
		//$custom_id = customfields();
	}
	else
	{
		$custom_id = '';
	}
	
	if (!empty($data['name']) 
		and $data['name'] != '')
	{
		//$log->addEntry ( array ('comment' => 'Обновляем товар '.$data['id'] ) );
		createProduct($modif,$custom_id,$harakt);
	}
	
	$product = NULL;
	$data = NULL;
	$product_parametres = NULL;
	$system_data = NULL;
	$sqlObjectID = 0;
}
function getAvailableExtension($file_extention){
	$extentions = explode(";",VM_EXT_FILES);
	$ok = false;
	foreach($extentions as $str)
	{
		if($str!="")
		{
			if($str == $file_extention)
			{
				$ok = true;
			}
		}
	}
    return $ok;
  }
function addProperties($xml_properties){

	global $log, $db, $dba, $id_admin, $username, $lang_1c, $data, $product_parametres, $system_data, $sqlObjectID;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	$properties = new XMLReader();
	$properties->XML($xml_properties);
	
	while($properties->read()) 
	{
		if($properties->nodeType == XMLReader::ELEMENT ) 
		{
			switch($properties->name) 
			{		
				case 'Ид': 
					$data['custom_1c_id'] = $properties->readString();
					break;
									
				case 'Значение':
					$data['custom_val'] = $properties->readString();
					$data['custom_val'] = str_replace(",",".",$data['custom_val']);
					break;
			}
			
		}
		
	}
	addPropertyToArray();
}
function addPropertyToArray(){

	global $log, $db, $dba, $id_admin, $username, $lang_1c, $data, $product_parametres, $system_data, $sqlObjectID;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
		
		$custom_id = sqlloadResult("SELECT custom_id FROM #__".$dba['customs_to_1c_db']." where `c_custom_id` = '" . $db->getEscaped($data['custom_1c_id']) . "'", $db);
		
		$product_id = sqlloadResult("SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($data['id']) . "'", $db);
		
		$rows_property = sqlloadObject("SELECT * FROM #__".$dba['customs_db']." where `virtuemart_custom_id` = '" . $custom_id . "'", $db);
		//
		//Минимальное количество
		//if(intval($data['packaging']) <= 1 && ($data['baz_ed'] == "м/п" || $data['baz_ed'] == "пог. м")){
		if((intval($data['packaging']) <= 1 || intval($data['min_order_level']) <=1) && ($data['baz_ed'] == "м/п" || $data['baz_ed'] == "пог. м")){
			$data['packaging'] = "300";
			$data['min_order_level'] = $data['packaging'];
		}
		//
		//$logs_http[] = "Количество проверяемых полей = ".count($rows_property);
		if($rows_property){
			$custom_name = (string)$rows_property->custom_title;
			if($custom_name == "Минимальное количество для интернет заказа"){
				//$logs_http[] = "Минимальное значение = ".intval($data['custom_val'])." для кода = ".$product_id;
				if(intval($data['custom_val']) != 0){
					$data['packaging'] = $data['custom_val'];
					$data['min_order_level'] = $data['packaging'];
					//$logs_http[] = "package = ".$data['packaging']." для кода = ".$product_id;
				}
			}
		}
		//
		$product_parametres[] = array(
			"c_product_id" => $data['id'],
			"product_id" => $product_id,
			"c_custom_id" => $data['custom_1c_id'],
			"custom_id" => $custom_id,
			"value" => $data['custom_val']
		);
}
function addProductProperty(){

	global $log, $db, $dba, $id_admin, $username, $lang_1c, $data, $product_parametres, $system_data, $sqlObjectID;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
	
		foreach($product_parametres as $new_product_parametr){
			//echo $new_product_parametr["c_custom_id"]." ".$new_product_parametr["custom_id"]." ".$data["id"];
			$param_custom_id = $new_product_parametr["custom_id"];
			$param_value = $new_product_parametr["value"];
			$param_product_id = $new_product_parametr["product_id"];
			
			//customfields_plg_db
			$rows = sqlloadObject("SELECT * FROM #__".$dba['customfields_plg_db']." where `virtuemart_custom_id` = '".$param_custom_id."' and `virtuemart_product_id` = '".$param_product_id."'", $db);
			$rows_c = sqlloadObject("SELECT * FROM #__".$dba['customs_db']." where `virtuemart_custom_id` = '".$param_custom_id."'", $db);

			$custom_value = "";
			$custom_intvalue = 0;
			
			if (VM_VERVM == '2'){
				if($rows){
					
					$custom_value_id = 0;
					
					$update = array();
					
					if(isset($rows->val))
					{
						$custom_value = $rows->val;
					}
					if(isset($rows->intval))
					{
						$custom_intvalue = $rows->intval;
					}
					//							
					$rows_sub_Count = sqlloadResult("SELECT id FROM #__".$dba['customfields_plg_values_db']." where `value` = '" . $param_value . "' and `virtuemart_custom_id`='" . $param_custom_id ."'", $db);
					
					if (isset ( $rows_sub_Count ))
					{
						$custom_value_id = (int)$rows_sub_Count;
					}
					//
					if(is_numeric($param_value) && $rows_c->field_type_custom != "S")
					{
						if($custom_value != '' && intval($custom_value) != intval($param_value)){
							$update['intval'] = "`intval`='".(float)$param_value."'";
							$update['val'] = "`val`='".$custom_value_id."'";
						}
					}
					elseif(is_string($param_value) || $rows_c->field_type_custom == "S")
					{
						if($custom_value != '' && strval($custom_value) != strval($param_value)){
							$update['intval'] = "`intval`='0'";
							$update['val'] = "`val`='".$custom_value_id."'";
						}
					}
					else
					{
						//$update['val'] = "`val`='".$custom_value_id."'";
					}

					if(!empty($update)){
						//$log->addEntry ( array ('comment' => ' Что-то надо поменять' ) );
						$sql_upd = "";
						$count_index = 0;
						$count_upd = count($update);
						foreach($update as $upd )
						{
							$count_index = $count_index + 1;
							if($count_index < $count_upd)
							{
								$sql_upd .= $upd.", ";
							}
							else
							{
								$sql_upd .= $upd;
							}
						}
						$sql = "UPDATE #__".$dba['customfields_plg_db']." SET ".$sql_upd." where `virtuemart_custom_id` = '".$param_custom_id."' and `virtuemart_product_id` = '".$param_product_id."'";
						//
						if (!sqlQueryOK($sql, $db))
						{
							$log->addEntry ( array ('comment' => 'Невозможно обновить параметр для продукта id - ' . $param_product_id ) );
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql ) );
							if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
								echo $sql;
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить параметр для продукта id - <strong>".$param_product_id."</strong>";
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql."</strong>";
							}
							if(!defined( 'VM_SITE' ))
							{
								die;
							}
							else
							{
								$die = true;
							}
						}
					}
					//
				}
				else{
					//echo $row["value"];
					//Добавляем новое сведение о товаре
					if(isset($param_value) && !empty($param_value))
					{
						if(is_numeric($param_value) && $rows_c->field_type_custom != "S")
						{
							$custom_intvalue = (float)$param_value;
						}
						elseif(is_string($param_value) || $rows_c->field_type_custom == "S")
						{
							$custom_value = (string)$param_value;
						}
						else
						{
							$custom_value = (string)$param_value;
						}
						
						$ins = new stdClass ();
						$ins->id = NULL;
						$ins->virtuemart_product_id = (int)$param_product_id;
						$ins->virtuemart_custom_id = (int)$param_custom_id;
						$custom_value_id = 0;
						
						if($custom_value != "")
						{
							$ins_value = new stdClass ();
							$ins_value->id = NULL;
							$ins_value->virtuemart_custom_id = (int)$param_custom_id;
							$ins_value->value = $custom_value;
							$ins_value->status = 0;
							$ins_value->published = 1;
							$ins_value->ordering = 0;
							
							$rows_sub_Count = sqlloadResult("SELECT id FROM #__".$dba['customfields_plg_values_db']." where `value` = '" . $ins_value->value . "' and `virtuemart_custom_id`='" . $param_custom_id ."'", $db);
							
							if ( isset($rows_sub_Count) )
							{
								$custom_value_id = (int)$rows_sub_Count;
							}
							else
							{
								$table = getString("#__", (string)$dba['customfields_plg_values_db']);
								sqlinsertObject($table, $ins_value, "`id`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['customfields_plg_values_db']."</strong>");
								$rows_sub_Count = sqlloadResult("SELECT id FROM #__".$dba['customfields_plg_values_db']." where `value` = '" . $ins_value->value . "'", $db);
								$custom_value_id = (int)$rows_sub_Count;
							}

						}
						//echo $custom_value;
						//gfdgfd
						if($custom_value_id!=0)
						{
							$ins->val = $custom_value_id;
						}
						else
						{
							$ins->val = $custom_value_id;
						}
						$ins->intval = (float)$custom_intvalue;
						$table = getString("#__", (string)$dba['customfields_plg_db']);
						sqlinsertObject ( $table, $ins, "`id`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['customfields_plg_db']."</strong>" );
						
					}
				}
			}
			//customfields virtuemart
			$rows = sqlloadObject("SELECT * FROM #__".$dba['customfields_db']." where `virtuemart_custom_id` = '".$param_custom_id."' and `virtuemart_product_id` = '".$param_product_id."'", $db);
			
			//$log->addEntry ( array ('comment' => 'rows_product_parametr = '.$param_product_id ) );
			
			if (VM_VERVM == '2'){
			
			$custom_value = "";
			
				if($rows){
					//изменяем сведения
					$update = array();
					
					if(isset($rows->custom_value))
					{
						$custom_value = $rows->custom_value;
					}
					//
					if($custom_value != '' && strval($custom_value) != strval($param_value))
					{
						$update['custom_value'] = "`custom_value`='".strval($param_value)."'";
					}
					if(!empty($update)){
						
						$sql_upd = "";
						
						$count_index = 0;
						$count_upd = count($update);
						foreach($update as $upd )
						{
							$count_index = $count_index + 1;
							if($count_index < $count_upd)
							{
								$sql_upd .= $upd.", ";
							}
							else
							{
								$sql_upd .= $upd;
							}
						}
						$sql = "UPDATE #__".$dba['customfields_db']." SET ".$sql_upd." where `virtuemart_custom_id` = '".$param_custom_id."' and `virtuemart_product_id` = '".$param_product_id."'";
						//
						if (!sqlQueryOK($sql, $db))
						{
							$log->addEntry ( array ('comment' => 'Невозможно обновить параметр для продукта id - ' . $param_product_id ) );
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql ) );
							if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
								echo $sql;
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить параметр для продукта id - <strong>".$param_product_id."</strong>";
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql."</strong>";
							}
							if(!defined( 'VM_SITE' ))
							{
								die;
							}
							else
							{
								$die = true;
							}
						}
					}
				}
				else{
					//Добавляем новое сведение о товаре
					if(isset($param_value) && !empty($param_value))
					{
						$ins = new stdClass ();
						$ins->virtuemart_customfield_id = NULL;
						$ins->virtuemart_product_id = (int)$param_product_id;
						$ins->virtuemart_custom_id = (int)$param_custom_id;
						
						$ins->custom_value = (string)$param_value;
						$ins->published = 0;
						$ins->ordering = 0;
						$table = getString("#__", (string)$dba['customfields_db']);
						sqlinsertObject ( $table, $ins, "`virtuemart_customfield_id`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['customfields_db']."</strong>" );
					}
				}
			}
		}
}
function searchImageForEdge($d){
	global $log, $db, $dba, $id_admin, $username, $lang_1c, $data, $product_parametres, $system_data, $sqlObjectID;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
	//product_db
	//$id_virtuemart_product = sqlloadResult("SELECT * FROM #__".$dba['product_db']." where `virtuemart_product_id` = '".$d['current_id']."'", $db);
	$id_virtuemart_product = sqlloadResult("SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($d['id']) . "'", $db);
	$is_prices = '1';
	$is_images = '1';
	if(count($d['category_1c_id']) > 0){
		for($x=0; $x < count($d['category_1c_id']); $x++){
		
			$cat_uid = $db->getEscaped(strval($d['category_1c_id'][$x]));
			$rows_category = sqlloadResult("SELECT category_id FROM #__".$dba['category_to_1c_db']." where `c_category_id` = '".$cat_uid."'", $db);
			if(isset($rows_category)){
				$category_id = (int)$rows_category;
				
				$rows = sqlloadObject("SELECT * FROM #__".$dba['category_db']." where `".$dba['pristavka']."category_id` = '" . $category_id . "'", $db);
				
				if(isset($rows)){
					if($rows->category_layout == "nisonEdge"
					|| $rows->category_layout == "nisonMap"){
						//prices

						$rows_price = sqlloadObjectList("SELECT virtuemart_product_price_id FROM #__".$dba['product_price_db']." where `virtuemart_product_id` = '" . $id_virtuemart_product . "'", $db);
						if(count($rows_price) <= 0){
							$is_prices = '0';
							//$logs_http[] = "".$sql5."";
						} else { 
							$is_prices = '1'; 
							
						}
						//
						//images 
						$rows_medias = sqlloadObjectList("SELECT id FROM #__".$dba['product_files_db']." where `virtuemart_product_id` = '" . $id_virtuemart_product . "'", $db);
						if(count($rows_medias) <= 0){
							$is_images = '0';
							//$logs_http[] = "".$sql6."";
						} else { 
							$is_images = '1'; 
							
						}
						//$logs_http[] = "is_images = ".$is_images." is_prices=".$is_prices;
						//switch off
						if($is_images == '0' || $is_prices == '0'){
							
							$sql7 = "UPDATE #__".$dba['product_db']." SET `published` = '0' WHERE `".$dba['pristavka']."product_id`='".$id_virtuemart_product."'";
							if (!sqlQueryOK($sql7, $db))
							{
								$log->addEntry ( array ('comment' => 'Невозможно отключить публикацию id - ' . $id_virtuemart_product ) );
								$log->addEntry ( array ('comment' => '' . $sql7 ) );
								if(!defined( 'VM_SITE' ))
								{
									echo 'failure\n';
									echo 'error mysql update\n';
									echo $sql;
								}
								else
								{
									$logs_http[] = "<strong><font color='red'>Неудача:</font></strong> Невозможно отключить публикацию id - <strong>".$id_virtuemart_product."</strong>";
									$logs_http[] = "<strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql7."</strong>";
								}
							}
						}
						if($is_images == '1' && $is_prices == '1'){
							
							$sql8 = "UPDATE #__".$dba['product_db']." SET `published` = '1' WHERE `".$dba['pristavka']."product_id`='".$id_virtuemart_product."'";
							//$logs_http[] = "".$sql8."";
							if (!sqlQueryOK($sql8, $db))
							{
								$log->addEntry ( array ('comment' => 'Невозможно отключить публикацию id - ' . $id_virtuemart_product ) );
								$log->addEntry ( array ('comment' => '' . $sql8 ) );
								if(!defined( 'VM_SITE' ))
								{
									echo 'failure\n';
									echo 'error mysql update\n';
									echo $sql;
								}
								else
								{
									$logs_http[] = "<strong><font color='red'>Неудача:</font></strong> Невозможно отключить публикацию id - <strong>".$id_virtuemart_product."</strong>";
									$logs_http[] = "<strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql7."</strong>";
								}
							}
						}
					}
				}
			}
		}
	}
}
function createProduct($modif='false', $custom_id='0',$harakt=''){
	
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
	
	//$log->addEntry ( array ('comment' => 'Вошли в функцию - createProduct()') );
	if (empty($data['full_name']) or $data['full_name'] == "")
	{
		$data['full_name'] = $data['full_name_2'];
	}
	//$log->addEntry ( array ('comment' => 'Переходим к проверке = '.$data['id'] ) );
	if(!empty($data['nds_name']) and $data['nds_name'] != '' and isset($data['nds_name']))
	{
		if (!isset($data['nds_db']))
		{
			$data['nds_db'] = 0;
		}
		if (VM_VERVM == '1') 
		{
			$nds_db = $data['nds_db']/100;
		}
		else
		{
			$nds_db = $data['nds_db'];
		}
										
		$rows_sub_Count = sqlloadResult("SELECT ".$dba['tax_rate_id_t']."  FROM #__".$dba['tax_rate_db']." where `".$dba['tax_rate_name']."` = '" . $data['nds_name'] . "'", $db);
		if (isset ( $rows_sub_Count ))
		{
			$data['nds'] = (int)$rows_sub_Count;
		}
		else
		{
			$ins = new stdClass ();
			if (VM_VERVM == '1')
			{
				$ins->tax_rate_id	=	NULL;
				$ins->vendor_id 	=	'1';
				$ins->tax_country 	=	VM_NDS_COUNTRY;
				$ins->mdate 		=	time ();
				$ins->tax_rate 		=	$nds_db;
				$ins->tax_state		=	'-';
			}
			elseif (VM_VERVM == '2')
			{
				$ins->virtuemart_calc_id	=	NULL;
				$ins->virtuemart_vendor_id	=	'1'; //Belongs to vendor
				$ins->calc_name				=	$data['nds_name']; //Name of the rule
				$ins->calc_descr			=	$data['nds_name'].' '.$nds_db.'%'; //Description
				$ins->calc_kind				=	'Tax'; //Discount/Tax/Margin/Commission	
				$ins->calc_value_mathop		=	'+%'; //the mathematical operation like (+,-,+%,-%)	
				$ins->calc_value			=	$nds_db; //The Amount	
				$ins->calc_currency			=	'131'; //Currency of the Rule	
				$ins->calc_shopper_published	=	'1'; //Visible for Shoppers	
				$ins->calc_vendor_published	=	'1'; //Visible for Vendors	
				$ins->publish_up			=	date ('Y-m-d H:i:s'); //Startdate if nothing is set = permanent	
				$ins->publish_down			=	'0000-00-00 00:00:00'; //Enddate if nothing is set = permanent	
				if(VM_VERVM_S != 'F')
				{
					$ins->calc_qualify			=	'0'; //qualifying productId's	
					$ins->calc_affected			=	'0'; //affected productId's	
					$ins->calc_amount_cond		=	'0'; //Number of affected products	
					$ins->calc_amount_dimunit	=	'0'; //The dimension, kg, m, ‚Ç¨
				}
				$ins->for_override			=	'0'; 	
				$ins->ordering				=	'0'; 	
				$ins->shared				=	'0'; 	
				$ins->published				=	'1';	
				$ins->created_on			=	date ('Y-m-d H:i:s');
				$ins->created_by			=	$id_admin;
				$ins->modified_on			=	date ('Y-m-d H:i:s');
				$ins->modified_by			=	$id_admin;
			}
			$table = getString("#__", (string)$dba['tax_rate_db']);
			
			sqlinsertObject ( $table, $ins, "`".$dba['tax_rate_id_t']."`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['tax_rate_db']."</strong>" );
												
			if(VM_VERVM == '2')
			{
				$data['nds'] = ( int ) $ins->virtuemart_calc_id;
			}
			else
			{
				$data['nds'] = ( int ) $ins->tax_rate_id;
			}
		}
	}
	else
	{
		$data['nds'] = "";
	}
	
	if(!empty($data['image']) and $data['image'] <> '')
	{
		$data['image'] = substr ( $data['image'], 16 );
		if(substr ( $data['image'], -4 ) == 'jpeg')
		{
			$system_data['tbn_img'] = str_replace(".jpeg", "", $data['image']);
			$system_data['small_img'] = "resized".DS.$system_data['tbn_img']."_".VM_TBN_H."x".VM_TBN_W.".".VM_JPG_S;
			$system_data['mimetype'] = 'jpeg';
		}
		else
		{
			$system_data['meta_img'] = substr ( $data['image'], - 3 );
			$system_data['tbn_img'] = str_replace(".".$system_data['meta_img'], "", $data['image']);
			$system_data['small_img'] = "resized".DS.$system_data['tbn_img']."_".VM_TBN_H."x".VM_TBN_W.".".$system_data['meta_img'];
			if ($system_data['meta_img'] == 'jpg')
			{
				$system_data['mimetype'] = 'jpeg';
			}
			else
			{
				$system_data['mimetype'] = $system_data['meta_img'];
			}
		}
		$system_data['change'] = true;
		$system_data['del_img'] = false;
		
	}
	elseif ($data['image'] == '' and $modif=='true')
	{
		$system_data['change'] = false;
		$data['image'] = "";
		$system_data['small_img'] = "";
		$system_data['del_img'] = false;
	}
	else
	{
		$data['image'] = "";
		$system_data['small_img'] = "";
		$system_data['change'] = true;
		$system_data['del_img'] = true;
	}
	
	if(empty($data['art']) or $data['art'] == '')
	{
		$data['art'] = substr((string)$data['id'],0,8);
	}
	
	if(empty($data['ves']) or $data['ves'] == '')
	{
		$data['ves'] = "0";
	}
	if(empty($data['packaging']) or $data['packaging'] == "")
	{
		$data['packaging'] = "1";
		$data['min_order_level'] = $data['packaging'];
		//$logs_http[] = "<strong>Установили новую упаковку для - наименование - <strong>".$data['name']." Объем упаковки = ".$data['packaging']."</strong>";		
	}
	if(empty($data['min_order_level']) or $data['min_order_level'] == '')
	{
		$data['min_order_level'] = "1";		
	}
	
	if (empty($data['status']) or $data['status'] == '')
	{
		$data['status'] = "нет";
	}
	
	if (empty($data['description']) or $data['description'] == '' or !isset($data['description']))
	{
		$data['description'] = "";//$data['full_name'];
	}
	else
	{
		$data['description'] = $data['description'];
	}
	
	if (empty($data['category_1c_id']) or !isset($data['category_1c_id']))
	{
		//$data['category_1c_id'] = "";
		return;
	}
	if (empty($data['manufacturer_id']) or $data['manufacturer_id'] == '' or !isset($data['manufacturer_id']))
	{
		$data['manufacturer_id'] = 0;
		return;
	}
	
	if (VM_VERVM == '2')
	{
		$slug_str = str_replace("(", "", $data['name']);
		$slug_str = str_replace(")", "", $slug_str);
		$slug_str = str_replace(".", "_", $slug_str);
		$slug_str = str_replace("/", "_", $slug_str);
		$slug_str = str_replace("-", "_", $slug_str);
		$slug_str = str_replace("+", "_", $slug_str);
		$slug_str = str_replace("=", "_", $slug_str);
		$slug_str = str_replace("&plusmn;", "_", $slug_str);
		$slug_str = str_replace(",", "", $slug_str);
		$slug_str = str_replace("&frasl;", "_", $slug_str);
		$slug_str = str_replace("'", "", $slug_str);
		$slug_str = strtr($slug_str,":", "_");
		$slug_str = str_replace(":", "_", $slug_str);
		
		$slug_str = str_replace('"', "", $slug_str);
		$slug_str = str_replace('</br>', "", $slug_str);
		$slug_str = str_replace('<br', "", $slug_str);
		$slug_str = str_replace('/>', "", $slug_str);
		$slug_str = str_replace('•', "", $slug_str);
		$slug_str = str_replace('&#149;', "", $slug_str);
		
		$search = array ("'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript
						 "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги
						 "'([\r\n])[\s]+'",                 // Вырезает пробельные символы
						 "'&(quot|#34);'i",                 // Заменяет HTML-сущности
						 "'&(amp|#38);'i",
						 "'&(lt|#60);'i",
						 "'&(gt|#62);'i",
						 "'&(nbsp|#160);'i",
						 "'&(iexcl|#161);'i",
						 "'&(cent|#162);'i",
						 "'&(pound|#163);'i",
						 "'&(copy|#169);'i",
						 "'&#(\d+);'",
						 "'&(frasl|#8260);'i");             // интерпретировать как php-код
		//"'&#(\d+);'e",
		$replace = array ("",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "_");
		
		$slug_str = preg_replace($search, $replace, $slug_str);
		//$slug_str = preg_replace_callback($search, $replace, $slug_str);
		//if PHP >= 5.4.0
		//$slug_str = preg_replace_callback($search, function($matches) {return $this->check_module($matches[1], $matches[2]);}, $slug_str);
		
		//$slug_str = preg_replace("~[^-0-9A-Z_]~isU","",$slug_str);
		
		if($slug_str{0} == " ")
		{
			$slug_str = substr($slug_str, 1);
		}
		
		if (substr($slug_str, -1) == " ")
		{
			$slug_str = substr($slug_str, 0, -1);
		}
		
		$slug_name = explode ( " ", $slug_str );
			
		if (count($slug_name) > 1)
		{
			$id_slug=0;
			unset ($s_name);
			$s_name = array ();
			
			foreach ($slug_name as $snm)
			{
				$s_name[$id_slug] =  translitString($snm);
				$id_slug = $id_slug + 1;
			}
				
			$data['slug'] = implode("_", $s_name);
		}
		else
		{
			$data['slug'] =  translitString($data['name']);
		}
		if (empty ($data['slug']) or $data['slug'] == "")
		{
			$data['slug'] = $data['name'];
		}
		
		$search2 = array ("'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript
						 "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги
						 "'([\r\n])[\s]+'",                 // Вырезает пробельные символы
						 "'&(quot|#34);'i",                 // Заменяет HTML-сущности
						 "'&(amp|#38);'i",
						 "'&(lt|#60);'i",
						 "'&(gt|#62);'i",
						 "'&(nbsp|#160);'i",
						 "'&(iexcl|#161);'i",
						 "'&(cent|#162);'i",
						 "'&(pound|#163);'i",
						 "'&(copy|#169);'i",
						 "'&#(\d+);'",
						 "'\"'i",
						 "'•'i",
						 "'&#149;'i",
						 "'<br'i",
						 "'\/>'i");                    // интерпретировать как php-код
		//"'&#(\d+);'e",
		$replace2 = array ("",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "",
						  "");
		
		$key_meta = preg_replace($search2, $replace2, $data['description']);
		//$key_meta = preg_replace_callback($search2, $replace2, $data['description']);
		
		$data['metakey'] = str_replace(" ", ", ", $key_meta);
		$data['metadesc'] = $data['full_name'];//$data['description'];
		$data['metarobot'] = "";
		$data['metaauthor'] = str_replace("\n", "", $username);
	}
	$data['category_id'] = 0;
	$rows_sub_Count = sqlloadResult("SELECT category_id FROM #__".$dba['category_to_1c_db']." where `c_category_id` = '" . $db->getEscaped($data['category_1c_id'][0]) . "'", $db);
	//$log->addEntry ( array ('comment' => 'Этап 4.1.2) Категория товара '.$rows_sub_Count ) );
	
	if(isset ( $rows_sub_Count )) 
	{
		$data['category_id'] = $rows_sub_Count;
	}
		else
	{
		$data['category_id'] = 0;
	}
	
	//Проверка битых ключей product_id в передаточной таблице 
	checkBrockenProductUid();
	
	$data['current_id'] = checkExistProductUid();
	$data['hash'] = getHashData();
	
	//$log->addEntry ( array ('comment' => '$data[current_id] - '.$data['current_id']) );
	if((int)$data['current_id'] > 0){
		
		//Обновляем товар
		if ($data['status'] == 'Удален')
		{
			//$log->addEntry ( array ('comment' => 'Этап 4.1.2) Пытаюсь удалить товар' ) );
			deleteNewProduct();
		}
		else
		{
			//$log->addEntry ( array ('comment' => 'Этап 4.1.2) Пытаюсь исправить товар' ) );
			//$data['hash']
			$data['hash_db'] = sqlloadResult("SELECT hash FROM #__".$dba['product_to_1c_db']." where `product_id` = '" . (int)$data['current_id'] . "'", $db);
			if($data['hash_db'] != $data['hash']){
				modifyNewProduct();
			}
			//modifyNewProduct();
		}
	}
	elseif(intval($data['current_id']) <= 0){
		//$log->addEntry ( array ('comment' => 'Этап 4.1.2) Пытаюсь добавить новый товар' ) );
		addNewProduct();
	}
	else{
		//$log->addEntry ( array ('comment' => 'Этап 4.1.2) Пытаюсь добавить новый товар' ) );
		//addNewProduct();
	}
	
	addProductProperty();
	searchImageForEdge($data);
}
function addNewProduct(){
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
		$data['current_id'] = 0;
		//Добавляем новый товар
		if ($data['status'] != 'Удален' and $data['category_id'] > 0){
			//$log->addEntry ( array ('comment' => '--------------Добавляем товар: '.$data['name'].'--------------' ) );
			$logs_http[] = "<strong>Загрузка товара</strong> - --------------Добавляем товар: <strong>".$data['name']."</strong>--------------";
			
			if (VM_VERVM == '2')
			{
				$product_special = '0';
			}
			else
			{
				$product_special = 'N';
			}
			
			$ins = new stdClass ();
			
			$ins->product_parent_id = '0';//
			$ins->product_sku = (string)$data['art'];//							//!!!!!!!!!!!!!!!!!
			$ins->product_weight = (int)$data['ves'];//								//!!!!!!!!!!!!!!!!!
			$ins->product_packaging = (float)$data['packaging'];//
			$ins->product_params =  'min_order_level="'.(float)$data['min_order_level'].'"|max_order_level=""|product_box=""|';//
			$ins->product_weight_uom = 'KG';//
			$ins->product_length = "";//
			$ins->product_width = "";//
			$ins->product_height = "";//
			$ins->product_lwh_uom = "";//
			$ins->product_url = "";//
			$ins->product_in_stock = "0";//
			$ins->product_special = $product_special;//
			$ins->ship_code_id = NULL;
			$ins->product_sales = "0";//
			if (! isset ( $data['baz_ed'] )) {
				$ins->product_unit = 'piece';//
			}
			else 
			{
				$ins->product_unit = $data['baz_ed'].".";//
			}
			
			if(VM_VERVM == '2')
			{
				$ins->virtuemart_product_id = NULL;
				$ins->virtuemart_vendor_id = '1';//
				$ins->product_ordered = '0';//
				$ins->low_stock_notification = '5';//
				$ins->hits = '0';//
				$ins->intnotes = NULL;//
				$ins->metarobot = (string)$data['metarobot'];//
				$ins->metaauthor = (string)$data['metaauthor'];//
				$ins->layout = '0';//
				$ins->published = '1';//
				$ins->created_on = date ('Y-m-d H:i:s');//
				$ins->created_by = $id_admin;//
				$ins->modified_on = date ('Y-m-d H:i:s');//
				$ins->modified_by = $id_admin;//
				if (VM_POSTAVKA_E == 'yes')
				{
					$available_date = time() + VM_POSTAVKA_TIME;
					$available_date = date ('Y-m-d H:i:s', $available_date);
					
					$ins->product_available_date = $available_date;		//						//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					$ins->product_availability = VM_POSTAVKA;		//			//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				}
				else
				{
					$ins->product_available_date = date ('Y-m-d H:i:s');	//							//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					$ins->product_availability = "on-order.gif";			//		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				}
			}
			else
			{
				
				$ins->product_id = NULL;
				$ins->vendor_id = '1';
				$ins->product_thumb_image = $system_data['small_img'];
				if (substr ( $data['image'], - 4 ) == 'jpeg')
				{
					$ins->product_full_image = str_replace(".jpeg", "", $data['image']).".".VM_JPG_S;
				}
				else
				{
					$ins->product_full_image = $data['image'];
				}
				$ins->product_publish = "Y";
				$ins->product_discount_id = "";
				$ins->cdate = time ();
				$ins->mdate = time ();	
				$ins->attribute = "";
				$ins->custom_attribute = "";
				$ins->product_tax_id = (int)$data['nds'];							//!!!!!!!!!!!!!!!!!
				$ins->child_options = "N,N,N,N,N,N,20%,10%,";
				$ins->quantity_options = "none,0,0,1";
				$ins->child_option_ids = "";
				$ins->product_order_levels = "1,10";	
				
				if (VM_POSTAVKA_E == 'yes')
				{
					$ins->product_available_date = time() + VM_POSTAVKA_TIME;								//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					$ins->product_availability = VM_POSTAVKA;					//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				}
				else
				{
					$ins->product_available_date = time();								//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					$ins->product_availability = "on-order.gif";					//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				}
			}
			$table = getString("#__", (string)$dba['product_db']);
			
			sqlinsertObject( $table, $ins, "`".$dba['pristavka']."product_id`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_db']."</strong>" );
			
			//LANG
			$data['current_id'] = $sqlObjectID;
			
			if((int)$data['current_id'] <= 0) {
				$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: ИД товара = '.$data['current_id'].' ERROR - '.$sqlObject ) );
				break;
			}
			
			$ins = new stdClass ();
			$ins->virtuemart_product_id = $data['current_id'];
			if (VM_VERVM_S != 'F')
			{
				$ins->product_s_desc = (string)$data['s_description'];						//!!!!!!!!!!!!!!!!!
				$ins->product_desc = (string)$data['description'];					//!!!!!!!!!!!!!!!!!
				$ins->product_name = (string)$data['name'];							//!!!!!!!!!!!!!!!!!
			}
			else
			{
				$ins->product_desc = (string)$data['description'];
				$ins->product_s_desc = (string)$data['s_description'];
				$ins->product_name = (string)$data['name'];
				$slug = $data['slug']."_pid_".$data['current_id'];
				$ins->slug = (string)$slug;
			}
			if (VM_VERVM_S != 'F')
			{
				$slug = $data['slug']."_pid_".$data['current_id'];
				$ins->slug = (string)$data['slug'];
				$ins->metadesc = (string)$data['metadesc'];
				$ins->metakey = (string)$data['metakey'];
			}		
			
			$table = getString("#__", (string)$dba['product_ln_db']);
			$product_ln = sqlloadResult("SELECT ". $dba['pristavka'] ."product_id FROM ". $table ." where `".$dba['pristavka']."product_id` ='" . (int) $data['current_id'] . "'", $db);
			
			if(intval($product_ln)<=0 || $product_ln == NULL || !isset($product_ln)){
				$product_ln = sqlloadResult("SELECT ". $dba['pristavka'] ."product_id FROM ". $table ." where `product_name` LIKE '%" . $data['name'] . "%'", $db);
				//$log->addEntry ( array ('comment' => '$product_ln='.$product_ln." "."SELECT ". $dba['pristavka'] ."product_id FROM ". $table ." where `product_name` LIKE '%" . $data['name'] . "%'" ) );
			}
			//$log->addEntry ( array ('comment' => '$product_ln='.$product_ln ) );
			if(intval($product_ln) > 0 && $product_ln != NULL){
				$sql = "DELETE FROM ".$table." WHERE `".$dba['pristavka']."product_id` = '".(int)$data['current_id']."'";
				if (!sqlQueryOK($sql, $db)){
					$log->addEntry ( array ('comment' => 'Этап 4.1.3) Не могу удалить '.$table.'  $data[current_id]- '.$data['current_id'] ) );
				} else {
					sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для продукта - <strong>".$data['name']."</strong>" );
				}
			} else {
				sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для продукта - <strong>".$data['name']."</strong>" );
			}
			
			if(VM_VERVM == '2' and VM_VERVM_S != 'F')
			{
				sqlQueryOK("UPDATE #__".$dba['product_db']." SET `slug` = '".$data['slug']."_pid_".$data['current_id']."' where `virtuemart_product_id`='".$data['current_id']."'", $db);
			}
			
			$ins = new stdClass ();
			if (VM_VERVM == '2')
			{
				$ins->virtuemart_product_id  = ( int )$data['current_id'];
				$ins->virtuemart_category_id = ( int )$data['category_id'];
				$ins->ordering   = NULL;
			}
			else
			{
				$ins->category_id = ( int )$data['category_id'];
				$ins->product_id  = ( int )$data['current_id'];
				$ins->product_list   = '1';
			}
			$table = getString("#__", (string)$dba['product_category_xref_db']);
			sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_category_xref_db']."</strong>" );
			
			//Добавляем производителя / Add manufacture
			
			$ins = new stdClass ();
				
				if (VM_VERVM == '2'){
					$ins->virtuemart_product_id  = ( int )$data['current_id'];
					$ins->virtuemart_manufacturer_id = ( int )$data['manufacturer_id'];
					$ins->ordering   = NULL;
				}
				else{
					$ins->manufacturer_id = ( int )$data['manufacturer_id'];
					$ins->product_id  = ( int )$data['current_id'];
					$ins->product_list   = '1';
				}
			$table = getString("#__", (string)$dba['product_mf_xref_db']);
			$product_mf = sqlloadResult("SELECT ". $dba['pristavka'] ."product_id FROM ". $table ." where `".$dba['pristavka']."product_id` ='" . (int) $data['current_id'] . "'", $db);
			
			if(intval($product_mf) > 0 && $product_mf != NULL){
				$sql = "DELETE FROM ".$table." WHERE `".$dba['pristavka']."product_id` = '".(int)$data['current_id']."'";
				if (!sqlQueryOK($sql, $db)){
					$log->addEntry ( array ('comment' => 'Этап 4.1.3) Не могу удалить '.$table.'  $data[current_id] - '.$data['current_id'] ) );
				} else {
					sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для продукта - <strong>".$data['name']."</strong>" );
				}
			} else {
				sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для продукта - <strong>".$data['name']."</strong>" );
			}
			//KIMKARUS.RU
			//Проверка категорий продукта
					$id_virtuemart_product = sqlloadResult("SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($data['id']) . "'", $db);
					if(count($data['category_1c_id']) > 0)
					{
						for($x=0; $x < count($data['category_1c_id']); $x++)
						{
							$strUid = strval($data['category_1c_id'][$x]);
							$strUid2 = $db->getEscaped($strUid);
							$rows_sub_Count = sqlloadResult("SELECT category_id FROM #__".$dba['category_to_1c_db']." where `c_category_id` = '".$strUid2."'", $db);
							$id_category1 = $rows_sub_Count;
							//$log->addEntry ( array ('comment' => 'Этап 4.1.3) ЗАПРОС - '.$sql ) );
							$rows = sqlloadAssocList("SELECT id, virtuemart_category_id FROM #__".$dba['product_category_xref_db']." WHERE `virtuemart_product_id` = '".$id_virtuemart_product."'", $db);
							$count_check_id = 0;
							$id_selected_category = 0;
							$id_selected_category0 = 0;
							//$log->addEntry ( array ('comment' => 'Этап 4.1.3) Ношлось то что надо 1 - '.$id_category1.' для - '.$data['name'] ) );
							
							//Проверка на задвоения
							foreach($rows as $row)
							{
								$id_row = $row['id'];
								$id_category2 = $row['virtuemart_category_id'];
								//Проверка чего не хватает
								if($id_category1 == $id_category2)
								{
									$count_check_id = $count_check_id + 1;
								}
								//Если она задвоена или пустая
								if($count_check_id > 1)
								{
									$id_selected_category = $id_row;
									
									$query = "DELETE FROM `#__".$dba['product_category_xref_db']."` WHERE `id` = '".$id_selected_category."'";
									if (sqlQueryOK($query, $db))
									{
										$logs_http[] = "<strong>Загрузка товара (ПРОВЕРКА)</strong> - Выполнен запрос № 2-1: (<strong>".$query."</strong>)";
										//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Выполнен запрос (ПРОВЕРКА) № 0: ('.$query.')') );
									}
									else
									{
										$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса (ПРОВЕРКА) № 0: (<strong>".$query."</strong>)";
										$log->addEntry ( array ('comment' => 'Этап 4.1.1) Неудача (ПРОВЕРКА): Ошибка запроса № 0: ('.$query.')') );
									}
								}
							}
							//Удаляем пустые обозначения
							foreach($rows as $row)
							{
								$id_row = $row['id'];
								$id_category2 = $row['virtuemart_category_id'];
								//Если пустая категория
								if($id_category2 <= 0)
								{
									$id_selected_category0 = $id_row;
									
									$query = "DELETE FROM `#__".$dba['product_category_xref_db']."` WHERE `id` = '".$id_selected_category0."'";
									if (sqlQueryOK($query, $db))
									{
										$logs_http[] = "<strong>Загрузка товара (ПРОВЕРКА)</strong> - Выполнен запрос № 2-3: (<strong>".$query."</strong>)";
										//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Выполнен запрос (ПРОВЕРКА) № 0: ('.$query.')') );
									}
									else
									{
										$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса (ПРОВЕРКА) № 0: (<strong>".$query."</strong>)";
										//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Неудача (ПРОВЕРКА): Ошибка запроса № 0: ('.$query.')') );
									}
								}
								//
							}

							$ins = new stdClass ();
							//Если ее еще нет
							if($count_check_id < 1)
							{

								if (VM_VERVM == '2')
								{
									$ins->virtuemart_product_id  = ( int )$id_virtuemart_product;
									$ins->virtuemart_category_id = ( int )$id_category1;
									$ins->ordering   = NULL;
								}
								else
								{
									$ins->category_id = ( int )$id_category1;
									$ins->product_id  = ( int )$id_virtuemart_product;
									$ins->product_list   = '1';
								}
								$table = getString("#__", (string)$dba['product_category_xref_db']);
								sqlinsertObject ( $table, $ins, "", $db, "" );
								
							}
						}
					}

					elseif (count($data['category_1c_id']) <= 0)
					{
					//Проверяем назначенные группы и убираем их
						$rows = sqlloadAssocList("SELECT id, virtuemart_category_id FROM #__".$dba['product_category_xref_db']." WHERE `virtuemart_product_id` = '".$id_virtuemart_product."'", $db);
						if(isset($rows) || count($rows)){
							foreach($rows as $row)
							{
								$id_row = $row['id'];								
								$query = "DELETE FROM `#__".$dba['product_category_xref_db']."` WHERE `id` = '".$id_row."'";
								if (sqlQueryOK($query, $db))
								{
									$logs_http[] = "<strong>Загрузка товара (ПРОВЕРКА)</strong> - Выполнен запрос № 2-4: (<strong>".$query."</strong>)";
									//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Выполнен запрос (ПРОВЕРКА) № 0: ('.$query.')') );
								}
								else
								{
									$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса (ПРОВЕРКА) № 0: (<strong>".$query."</strong>)";
									//$log->addEntry ( array ('comment' => 'Этап 4.1.1) Неудача (ПРОВЕРКА): Ошибка запроса № 0: ('.$query.')') );
								}
							}
						}
					}
					else
					{
					}
		//else
		//{
			createProductUid($data['current_id'], $data['id'], $data['nds'], $data['hash']);
			
			//Производитель / Manufacture
				$ins = new stdClass ();
				
				if (VM_VERVM == '2')
				{
					$ins->virtuemart_product_id  = ( int )$data['current_id'];
					$ins->virtuemart_manufacturer_id = ( int )$data['manufacturer_id'];
					$ins->ordering   = NULL;
				}
				else
				{
					$ins->manufacturer_id = ( int )$data['manufacturer_id'];
					$ins->product_id  = ( int )$data['current_id'];
					$ins->product_list   = '1';
				}
				$table = getString("#__", (string)$dba['product_mf_xref_db']);
				$product_mf = sqlloadResult("SELECT ". $dba['pristavka'] ."product_id FROM ". $table ." where `".$dba['pristavka']."product_id` ='" . (int) $data['current_id'] . "'", $db);
			
				if(intval($product_mf) > 0 && $product_mf != NULL){
					$sql = "DELETE FROM ".$table." WHERE `".$dba['pristavka']."product_id` = '".(int)$data['current_id']."'";
					if (!sqlQueryOK($sql, $db)){
						$log->addEntry ( array ('comment' => 'Этап 4.1.3) Не могу удалить '.$table.'  $data[current_id] - '.$data['current_id'] ) );
					} else {
						sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для продукта - <strong>".$data['name']."</strong>" );
					}
				} else {
					sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для продукта - <strong>".$data['name']."</strong>" );
				}
	
			
			if (VM_VERVM == '2' and !empty($data['image']) and $data['image'] <> '')
			{
				$ins = new stdClass ();
				$ins->virtuemart_media_id = NULL;
				$ins->virtuemart_vendor_id = '1';
				$ins->file_title = (string)$data['name'];
				$ins->file_description = (string)$data['description'];
				$ins->file_meta = '';
				$ins->file_mimetype = 'image/'.$system_data['mimetype'];
				$ins->file_type = 'product';
				if (substr ( $data['image'], - 4 ) == 'jpeg')
				{
					$ins->file_url = JPATH_PICTURE.DS.str_replace(".jpeg", "", $data['image']).".".VM_JPG_S;
				}
				else
				{
					$ins->file_url = JPATH_PICTURE.DS.$data['image'];
				}
				$ins->file_url_thumb = JPATH_PICTURE.DS.$system_data['small_img'];
				$ins->file_is_product_image = '1';
				$ins->file_is_downloadable = '0';
				$ins->file_is_forSale = '0';
				$ins->file_params = '';
				$ins->ordering = NULL;
				$ins->shared = '0';
				$ins->published = '1';
				$ins->created_on = date ('Y-m-d H:i:s');
				$ins->created_by = $id_admin;
				$ins->modified_on = date ('Y-m-d H:i:s');
				$ins->modified_by = $id_admin;
				
				$table = getString("#__", DBBASE.'_medias');
				sqlinsertObject ( $table, $ins, "`virtuemart_media_id`", $db,  "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_medias</strong>");
				
				$data['media_id'] = ( int ) $sqlObjectID;			
				
				$ins = new stdClass ();
				$ins->id = NULL;
				$ins->virtuemart_product_id = ( int )$data['current_id'];
				$ins->virtuemart_media_id = (int)$data['media_id'];
				$ins->ordering = '0';
				
				$table = getString("#__", DBBASE.'_product_medias');
				sqlinsertObject ( $table, $ins, "", $db,  "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_product_medias</strong>");
					
			}
			
			if (VM_XML_VERS == "203" and isset($custom_id) and $custom_id != '')
			{
				makecustoms($data,$data['current_id'],$custom_id,$harakt);
			}
			
			if(isset($data['product_image']))
			{
				foreach ($data['product_image'] as $img )
				{
					$data['file'] = substr ( $img, 16 );
					if(substr ( $data['file'], -4 ) == 'jpeg')
					{
						$system_data['tbn_img'] = str_replace(".jpeg", "", $data['file']);
						$system_data['small_img'] = "resized".DS.$system_data['tbn_img']."_".VM_TBN_H."x".VM_TBN_W.".".VM_JPG_S;
						$system_data['meta_img'] = "jpeg";
						$system_data['mimetype'] = $system_data['meta_img'];
					}
					else
					{
						$system_data['meta_img'] = substr ( $data['file'], - 3 );
						$system_data['tbn_img'] = str_replace(".".$system_data['meta_img'], "", $data['file']);
						$system_data['small_img'] = "resized".DS.$system_data['tbn_img']."_".VM_TBN_H."x".VM_TBN_W.".".$system_data['meta_img'];
						if ($system_data['meta_img'] == 'jpg')
						{
							$system_data['mimetype'] = 'jpeg';
						}
						else
						{
							$system_data['mimetype'] = $system_data['meta_img'];
						}
					}
					
					if (VM_VERVM == '2')
					{
						$ins = new stdClass ();
						$ins->virtuemart_media_id = NULL;
						$ins->virtuemart_vendor_id = '1';
						$ins->file_title = (string)$data['name'];
						$ins->file_description = (string)$data['description'];
						$ins->file_meta = '';
						$ins->file_mimetype = 'image/'.$system_data['mimetype'];
						$ins->file_type = 'product';
						if (substr ( $data['file'], - 4 ) == 'jpeg')
						{
							$ins->file_url = JPATH_BASE_PICTURE.DS.str_replace(".jpeg", "", $data['file']).".".VM_JPG_S;
						}
						else
						{
							$ins->file_url = JPATH_BASE_PICTURE.DS.$data['file'];
						}
						$ins->file_url_thumb = JPATH_BASE_PICTURE.DS.$system_data['small_img'];
						$ins->file_is_product_image = '1';
						$ins->file_is_downloadable = '0';
						$ins->file_is_forSale = '0';
						$ins->file_params = '';
						$ins->ordering = NULL;
						$ins->shared = '0';
						$ins->published = 1;
						$ins->created_on = date ('Y-m-d H:i:s');
						$ins->created_by = $id_admin;
						$ins->modified_on = date ('Y-m-d H:i:s');
						$ins->modified_by = $id_admin;
						
						$table = getString("#__", DBBASE.'_medias');
						sqlinsertObject ( $table, $ins, "`virtuemart_media_id`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_medias</strong>" );
									
						$data['media_id'] = ( int ) $sqlObjectID;	
						
						$ins = new stdClass ();
						$ins->id = NULL;
						$ins->virtuemart_product_id = ( int )$data['current_id'];
						$ins->virtuemart_media_id = (int)$data['media_id'];
						$ins->ordering = '0';
						
						$table = getString("#__", DBBASE.'_product_medias');
						sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_product_medias</strong>" );
							
					}
					else
					{
						$ins = new stdClass ();
						$ins->file_id = NULL;
						$ins->file_product_id = ( int )$data['current_id'];
						$ins->file_name = $system_data['small_img'];
						$ins->file_title = (string)$data['name'];
						$ins->file_description = "";
						$ins->file_extension = $system_data['meta_img'];
						$ins->file_mimetype = 'image/'.$system_data['meta_img'];
						if (substr ( $data['file'], - 4 ) == 'jpeg')
						{
							$ins->file_url = 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS .str_replace(".jpeg", "", $data['file']).".".VM_JPG_S;
						}
						else
						{
							$ins->file_url = 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS . $data['file'];
						}
						$ins->file_published = '1';
						$ins->file_is_image = '1';
						$ins->file_image_height = '';
						$ins->file_image_width = '';
						$ins->file_image_thumb_height = VM_TBN_H;
						$ins->file_image_thumb_width = VM_TBN_W;
													
						$table = getString("#__", (string)$dba['product_files_db']);
						sqlinsertObject ( $table, $ins, "`file_id`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_files_db']."</strong>" );
						 
					}
				}
			}
			
			$logs_http[] = "<strong>Загрузка товара</strong> - Товар - <strong>".$data['name']."</strong> добавлен";
			$log->addEntry ( array ('comment' => 'Этап 4.1.3) Товар - ' . $data['name'] . ' добавлен') );
		}
}
function deleteNewProduct(){
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
//Удаляем помеченный товар
			$table_del = array();
			
			$table_del[1] = $dba['product_db'];
			$table_del[2] = $dba['product_ln_db'];
			$table_del[3] = $dba['product_category_xref_db'];
			$table_del[4] = $dba['product_mf_xref_db'];
			$table_del[5] = DBBASE."_product_medias";
			$table_del[6] = $dba['product_price_db'];
			$table_del[7] = $dba['customfields_db'];
			$table_del[7] = $dba['customfields_plg_db'];
			
			$query = "DELETE FROM `#__".$dba['product_to_1c_db']."` WHERE `product_id` = '".(int)$data['current_id']."'";
			if (sqlQueryOK($query, $db)){
				$logs_http[] = "<strong>Загрузка товара</strong> - Выполнен запрос № 0: (<strong>".$query."</strong>)";
				$log->addEntry ( array ('comment' => 'Этап 4.1.1) Выполнен запрос № 0: ('.$query.')') );
			}else{
				$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса № 0: (<strong>".$query."</strong>)";
				$log->addEntry ( array ('comment' => 'Этап 4.1.1) Неудача: Ошибка запроса № 0: ('.$query.')') );
			}
			
			foreach($table_del as $key => $table_del_sql)
			{
				$sql = "DELETE FROM `#__".$table_del_sql."` WHERE `".$dba['pristavka']."product_id` = '".(int)$data['current_id']."'";
				if (sqlQueryOK($query, $db)){
					$logs_http[] = "<strong>Загрузка товара</strong> - Выполнен запрос № ".$key.": (<strong>".$sql."</strong>)";
					$log->addEntry ( array ('comment' => 'Этап 4.1.1) Выполнен запрос № '.$key.': ('.$sql.')') );
				}else{
					$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса № ".$key.": (<strong>".$sql."</strong>)";
					$log->addEntry ( array ('comment' => 'Этап 4.1.1) Неудача: Ошибка запроса № '.$key.': ('.$sql.')') );
				}
			}

			$logs_http[] = "<strong>Загрузка товара</strong> - Товар id - <strong>".(int)$data['current_id']."</strong> удален";
			$log->addEntry ( array ('comment' => 'Этап 4.1.3) Товар id - ' . (int)$data['current_id'] . ' удален') );
}
function modifyNewProduct(){
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
			$rows = sqlloadObject("SELECT * FROM #__".$dba['product_db']." where `".$dba['pristavka']."product_id` = '" . (int)$data['current_id'] . "'", $db);
			$update = array();
			$update_ln = array();
			$update_hash = array();
			if($rows){
				$product_sku = $rows->product_sku;
				$product_weight = $rows->product_weight;
				$product_packaging = $rows->product_packaging;
				$product_params = $rows->product_params;
				$product_published = $rows->published;
				//$min_order_level=$row->min_order_level;
				if (VM_VERVM == '1'){
					$product_tax_id = $rows->product_tax_id;
					$product_thumb_image = $rows->product_thumb_image;
					$product_full_image = $rows->product_full_image;
					$product_s_desc = $rows->product_s_desc;
					$product_desc = $rows->product_desc;
					$product_name = $rows->product_name;
					
					$slug = "";
					$metadesc = "";
					$metakey = "";
					$metarobot = "";
					$metaauthor = "";
				}
				elseif (VM_VERVM == '2' and VM_VERVM_S != 'F'){
					$slug = $rows->slug;
					$metadesc = $rows->metadesc;
					$metakey = $rows->metakey;
					$metarobot = $rows->metarobot;
					$metaauthor = $rows->metaauthor;
					
					$product_s_desc = $rows->product_s_desc;
					$product_desc = $rows->product_desc;
					$product_name = $rows->product_name;
					$product_unit = $rows->product_unit;
					
					$product_tax_id = "";
					$product_thumb_image = "";
					$product_full_image = "";
				}
				elseif (VM_VERVM == '2' and VM_VERVM_S == 'F'){
					$rows_ln = sqlloadObject("SELECT * FROM #__".$dba['product_ln_db']." where `".$dba['pristavka']."product_id` = '" . (int)$data['current_id'] . "'", $db);
					
					$slug = $rows_ln->slug;
					$metadesc = $rows_ln->metadesc;
					$metakey = $rows_ln->metakey;
					$product_s_desc = $rows_ln->product_s_desc;
					$product_desc = $rows_ln->product_desc;
					$product_name = $rows_ln->product_name;
					$product_unit = $rows->product_unit;
					
					$metarobot = $rows->metarobot;
					$metaauthor = $rows->metaauthor;
					
					$product_tax_id = "";
					$product_thumb_image = "";
					$product_full_image = "";
				}
				
				if($product_unit != $data['baz_ed'])
				{
					$update['product_unit'] = "`product_unit`='".(string)$data['baz_ed']."'";
				}
				if ($product_sku != $data['art'])
				{
					$update['sku'] = "`product_sku`='".(string)$data['art']."'";
				}
				
				if ($product_s_desc != $data['s_description'])
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['s_desc'] = "`product_s_desc`='".(string)$data['s_description']."'";
					}
					else
					{
						$update_ln['s_desc'] = "`product_s_desc`='".(string)$data['s_description']."'";
					}
				}
				
				if ($product_name != $data['name'])
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['name'] = "`product_name`='".(string)$data['name']."'";
					}
					else
					{
						$update_ln['name'] = "`product_name`='".(string)$data['name']."'";
					}
				}
				
				if ($product_desc != $data['description'])
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['desc'] = "`product_desc`='".(string)$data['description']."'";
					}
					else
					{
						$update_ln['desc'] = "`product_desc`='".(string)$data['description']."'";
					}
				}
				
				if ($product_weight != $data['ves'])
				{
					$update['weight'] = "`product_weight`='".(string)$data['ves']."'";
				}
				if (intval($product_packaging) != intval($data['packaging']))
				{
					$update['packaging'] = "`product_packaging`='".(string)$data['packaging']."'";
					$update['product_params'] = "`product_params` = 'min_order_level=".(float)$data['min_order_level']."|max_order_level=|product_box=|'";//
				}
			
				if ($product_tax_id != $data['nds'] and VM_VERVM == '1')
				{
					$update['tax'] = "`product_tax_id`='".(int)$data['nds']."'";
				}
				
				if ($product_full_image != $data['image'] and isset($system_data['change']) and $system_data['change'] == true and VM_VERVM == '1')
				{
					if (substr ( $data['image'], - 4 ) == 'jpeg')
					{
						$update['full_image'] = "`product_full_image`='".str_replace(".jpeg", "", $data['image']).".".VM_JPG_S."'";
					}
					else
					{
						$update['full_image'] = "`product_full_image`='".$data['image']."'";
					}
				}
				
				if ($product_thumb_image != $system_data['small_img'] and isset($system_data['change']) and $system_data['change'] == true and VM_VERVM == '1')
				{
					$update['thumb_image'] = "`product_thumb_image`='".$system_data['small_img']."'";
				}
				
				if ($slug != $data['slug'] and VM_VERVM == '2' and !empty($data['slug']) and $data['slug'] != "")
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['slug'] = "`slug`='".(string)$data['slug']."'";
					}
					else
					{
						$update_ln['slug'] = "`slug`='".(string)$data['slug']."'";
					}
				}
				
				if ($metadesc != $data['metadesc'] and VM_VERVM == '2')
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['metadesc'] = "`metadesc`='".(string)$data['metadesc']."'";
					}
					else
					{
						$update_ln['metadesc'] = "`metadesc`='".(string)$data['metadesc']."'";
					}
				}
				
				if ($metakey != $data['metakey'] and VM_VERVM == '2')
				{
					if (VM_VERVM_S == 'F')
					{
						$update_ln['metakey'] = "`metakey`='".(string)$data['metakey']."'";
					}
					else
					{
						$update_ln['metakey'] = "`metakey`='".(string)$data['metakey']."'";
					}
				}
				
				if ($metaauthor != $data['metaauthor'] and VM_VERVM == '2')
				{
					$update['metaauthor'] = "`metaauthor`='".(string)$data['metaauthor']."'";
				}
				/*if ($product_published != $data['product_published'] and VM_VERVM == '2')
				{
					$update['published'] = "`published`='".(int)$data['product_published']."'";
				}*/
				$update_hash['hash'] = "`hash`='".(string)$data['hash']."'";
				$count_upd = 0;
				$count = 0;
				if(!empty($update))
				{
					$sql_upd = "";
					$count_upd = count($update);
					$count = 0;
					foreach($update as $upd )
					{
						$count = $count + 1;
						if($count < $count_upd){
							$sql_upd .= $upd.", ";
						} else {
							$sql_upd .= $upd;
						}
					}
					
					$sql = "UPDATE #__".$dba['product_db']." SET ".$sql_upd." where `".$dba['pristavka']."product_id`='".(int)$data['current_id']."'";
					if (!sqlQueryOK($sql, $db))
					{
						$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно обновить продукт id - ' . (int)$data['current_id'] ) );
						$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql ) );
						if(!defined( 'VM_SITE' ))
						{
							echo 'failure\n';
							echo 'error mysql update\n';
							echo $sql;
						}
						else
						{
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить продукт id - <strong>".(int)$data['current_id']."</strong>";
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql."</strong>";
						}
						if(!defined( 'VM_SITE' ))
						{
							die;
						}
						else
						{
							$die = true;
						}
					}
				}
				$count_upd = 0;
				$count = 0;
				if(!empty($update_ln) and VM_VERVM_S == 'F')
				{
					$sql_upd = "";
					
					$count_upd = count($update_ln);
					$count = 0;
					foreach($update_ln as $upd )
					{
						$count = $count + 1;
						if($count < $count_upd){
							$sql_upd .= $upd.", ";
						} else {
							$sql_upd .= $upd;
						}
					}
					
					$sql = "UPDATE #__".$dba['product_ln_db']." SET ".$sql_upd." where `".$dba['pristavka']."product_id`='".(int)$data['current_id']."'";
					if (!sqlQueryOK($sql, $db))
					{
						$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно обновить продукт id - ' . (int)$data['current_id'] ) );
						$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql ) );
						if(!defined( 'VM_SITE' ))
						{
							echo 'failure\n';
							echo 'error mysql update\n';
							echo $sql;
						}
						else
						{
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить продукт id - <strong>".(int)$data['current_id']."</strong>";
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql."</strong>";
						}
						if(!defined( 'VM_SITE' ))
						{
							die;
						}
						else
						{
							$die = true;
						}
					}
				}
				$count_upd = 0;
				$count = 0;
				if(!empty($update_hash)){
					$sql_upd = "";
					
					$count_upd = count($update_hash);
					$count = 0;
					foreach($update_hash as $upd )
					{
						$count = $count + 1;
						if($count < $count_upd){
							$sql_upd .= $upd.", ";
						} else {
							$sql_upd .= $upd;
						}
					}
					
					$sql = "UPDATE #__".$dba['product_to_1c_db']." SET ".$sql_upd." where `product_id`='".(int)$data['current_id']."'";
					if (!sqlQueryOK($sql, $db))
					{
						$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно обновить продукт id - ' . (int)$data['current_id'] ) );
						$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql ) );
						if(!defined( 'VM_SITE' ))
						{
							echo 'failure\n';
							echo 'error mysql update\n';
							echo $sql;
						}
						else
						{
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить продукт id - <strong>".(int)$data['current_id']."</strong>";
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql."</strong>";
						}
						if(!defined( 'VM_SITE' ))
						{
							die;
						}
						else
						{
							$die = true;
						}
					}
				}
				if (VM_VERVM == '2' and $system_data['change'] == true)
				{
					$sql = "SELECT * FROM `#__".DBBASE."_product_medias` where `virtuemart_product_id` = '" . (int)$data['current_id'] . "'";
					$rows = sqlloadObjectList($sql, $db);
					foreach ( $rows as $row ) 
					{
						$sql_2 = "DELETE FROM `#__".DBBASE."_medias` WHERE `virtuemart_media_id` = '".$row->virtuemart_media_id."'";
						if (!sqlQueryOK($sql_2, $db)){
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно обновить основную картинку id - ' . (int)$data['current_id'] ) );
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql_2 ) );
							if(!defined( 'VM_SITE' )){
								echo 'failure\n';
								echo 'error mysql update\n';
								echo $sql_2;
							}else{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить основную картинку id - <strong>".(int)$data['current_id']."</strong>";
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql_2."</strong>";
							}
							if(!defined( 'VM_SITE' )){	die;}
							else{$die = true;}
						}
						
						$sql_3 = "DELETE FROM `#__".DBBASE."_product_medias` WHERE `id` = '".$row->id."'";
						if (!sqlQueryOK($sql_3, $db))
						{
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно обновить основную картинку id - ' . (int)$data['current_id'] ) );
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql_3 ) );
							if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
								echo $sql_3;
							}
							else
							{
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить основную картинку id - <strong>".(int)$data['current_id']."</strong>";
								$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql_3."</strong>";
							}
							if(!defined( 'VM_SITE' ))
							{
								die;
							}
							else
							{
								$die = true;
							}
						}
					}
					
					if ($system_data['del_img'] == false)
					{
						$ins = new stdClass ();
						$ins->virtuemart_media_id = NULL;
						$ins->virtuemart_vendor_id = '1';
						$ins->file_title = (string)$data['name'];
						$ins->file_description = (string)$data['description'];
						$ins->file_meta = '';
						$ins->file_mimetype = 'image/'.$system_data['mimetype'];
						$ins->file_type = 'product';
						if (substr ( $data['image'], - 4 ) == 'jpeg')
						{
							$ins->file_url = JPATH_PICTURE.DS.str_replace(".jpeg", "", $data['image']).".".VM_JPG_S;
						}
						else
						{
							$ins->file_url = JPATH_PICTURE.DS.$data['image'];
						}
						$ins->file_url_thumb = JPATH_PICTURE.DS.$system_data['small_img'];
						$ins->file_is_product_image = '1';
						$ins->file_is_downloadable = '0';
						$ins->file_is_forSale = '0';
						$ins->file_params = '';
						$ins->ordering = NULL;
						$ins->shared = '0';
						$ins->published = '1';
						$ins->created_on = date ('Y-m-d H:i:s');
						$ins->created_by = $id_admin;
						$ins->modified_on = date ('Y-m-d H:i:s');
						$ins->modified_by = $id_admin;
					
						$table = getString("#__", DBBASE.'_medias');
						sqlinsertObject ( $table, $ins, "`virtuemart_media_id`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_medias</strong>" );
											
						$data['media_id'] = ( int ) $sqlObjectID;
						
						
						$ins = new stdClass ();
						$ins->id = NULL;
						$ins->virtuemart_product_id = (int)$data['current_id'];
						$ins->virtuemart_media_id = (int)$data['media_id'];
						$ins->ordering = '0';
						
						$table = getString("#__", DBBASE.'_product_medias');
						
						sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_product_medias</strong>" );
						
					}
				}
				//KIMKARUS.RU
				//Проверка производителя / Check manufacture
				
				//$log->addEntry ( array ('comment' => 'Этап 4.1.23) Проверяем производителя для товара - '.$data['current_id'].' и производителя - '.$data['manufacturer_id'] ) );			
				$rows = sqlloadObject("SELECT * FROM #__".$dba['product_mf_xref_db']." where `".$dba['pristavka']."product_id` = '" . ( int )$data['current_id'] . "'", $db);
				if(count($rows) > 0 /*&& $data['manufacturer_id'] > 0*/){
					$current_manuf_id = $product_sku = $rows->id;
					$current_product_id = $product_sku = $rows->virtuemart_product_id;
					$current_manuf_id = $product_sku = $rows->virtuemart_manufacturer_id;
					
					if($current_manuf_id != $data['manufacturer_id'])
					{
						$sql = "UPDATE #__".$dba['product_mf_xref_db']." SET `".$dba['pristavka']."manufacturer_id` = '".$data['manufacturer_id']."' where `".$dba['pristavka']."product_id`='".$data['current_id']."'";
						if (!sqlQueryOK($sql, $db))
						{
							//$log->addEntry ( array ('comment' => 'Этап 4.1.23) Не могу обновить производителя для товара - '.$data['current_id'] ) );
							if(!defined( 'VM_SITE' ))
							{
								echo 'failure\n';
								echo 'error mysql update\n';
									echo $sql;
							}
							else
							{
								$logs_http[] = "<strong>Не могу обновить производителя для id - <strong>".$data['current_id']."</strong>";

							}
							if(!defined( 'VM_SITE' ))
							{
								die;
							}
							else
							{
								$die = true;
							}
						}
						else
						{
							//$log->addEntry ( array ('comment' => 'Этап 4.1.23) Успешно обновил производителя для товара - '.$data['current_id'] ) );
						}
					}
				}
				else{
					$ins = new stdClass ();
					
					if (VM_VERVM == '2')
					{
						$ins->virtuemart_product_id  = ( int )$data['current_id'];
						$ins->virtuemart_manufacturer_id = ( int )$data['manufacturer_id'];
						$ins->ordering   = NULL;
					}
					else
					{
						$ins->manufacturer_id = ( int )$data['manufacturer_id'];
						$ins->product_id  = ( int )$data['current_id'];
						$ins->product_list   = '1';
					}
					
					$table = getString("#__", (string)$dba['product_mf_xref_db']);
					
					sqlinsertObject ( $table, $ins, "", $db, "" );
					
					$product_mf = sqlloadResult("SELECT ". $dba['pristavka'] ."product_id FROM ". $table ." where `".$dba['pristavka']."product_id` ='" . (int) $data['current_id'] . "'", $db);
			
					if(intval($product_mf) > 0 && $product_mf != NULL){
						$sql = "DELETE FROM ".$table." WHERE `".$dba['pristavka']."product_id` = '".(int)$data['current_id']."'";
						if (!sqlQueryOK($sql, $db)){
							$log->addEntry ( array ('comment' => 'Этап 4.1.3) Не могу удалить '.$table.'  $data[current_id] - '.$data['current_id'] ) );
						} else {
							sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для продукта - <strong>".$data['name']."</strong>" );
						}
					} else {
						sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись для продукта - <strong>".$data['name']."</strong>" );
					}
				}
					
				//KIMKARUS.RU
				//Проверка категорий продукта
				$id_virtuemart_product = sqlloadResult("SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($data['id']) . "'", $db);
				//$log->addEntry ( array ('comment' => 'Этап Категории - 0.1) Колдичество катериогрий: ('.count($data['category_1c_id']).')') );
				$count_category_1c_id = count($data['category_1c_id']);
				if($count_category_1c_id > 0){
					//$log->addEntry ( array ('comment' => 'Этап Категории - 0.1) Начало проверки: ('.count($data['category_1c_id']).')') );
					for($x=0; $x < $count_category_1c_id; $x++)
					{
						$strUid = strval($data['category_1c_id'][$x]);
						
						$strUid2 = $db->getEscaped($strUid);
						
						//$log->addEntry ( array ('comment' => '$strUid='.$strUid.' $strUid2='.$strUid2) );
						
						$id_category1 = sqlloadResult("SELECT category_id FROM #__".$dba['category_to_1c_db']." where `c_category_id` = '".$strUid2."'", $db);
						$rows = sqlloadAssocList("SELECT id, virtuemart_category_id FROM #__".$dba['product_category_xref_db']." WHERE `virtuemart_product_id` = '".$id_virtuemart_product."'", $db);
						$count_check_id = 0;
						$id_selected_category = 0;
						$id_selected_category0 = 0;
								
						//Проверка на задвоения
						foreach($rows as $row)
						{
							$id_row = $row['id'];
							$id_category2 = $row['virtuemart_category_id'];
							//Проверка чего не хватает
							if($id_category1 == $id_category2)
							{
								$count_check_id = $count_check_id + 1;
							}
							//Если она задвоена или пустая
							if($count_check_id > 1)	{
								$id_selected_category = $id_row;
									
								$query = "DELETE FROM `#__".$dba['product_category_xref_db']."` WHERE `id` = '".$id_selected_category."'";
								if (sqlQueryOK($query, $db)){
									$logs_http[] = "<strong>Загрузка товара (ПРОВЕРКА)</strong> - Выполнен запрос № 1-1: (<strong>".$query."</strong>)";
									//$log->addEntry ( array ('comment' => 'Этап Категории - 1.1) Выполнен запрос (ПРОВЕРКА) № 0: ('.$query.')') );
								}
								else{
									$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса (ПРОВЕРКА) № 0: (<strong>".$query."</strong>)";
									$log->addEntry ( array ('comment' => 'Этап Категории - 1.2) Неудача (ПРОВЕРКА): Ошибка запроса № 0: ('.$query.')') );
								}
							}
						}
						
						//Удаляем пустые обозначения
						foreach($rows as $row)
						{
							$id_row = $row['id'];
							$id_category2 = $row['virtuemart_category_id'];
							//Если пустая категория
							if($id_category2 <= 0)
							{
								$id_selected_category0 = $id_row;
								
								$query = "DELETE FROM `#__".$dba['product_category_xref_db']."` WHERE `id` = '".$id_selected_category0."'";
								if (sqlQueryOK($query, $db))
								{
									$logs_http[] = "<strong>Загрузка товара (ПРОВЕРКА)</strong> - Выполнен запрос № 1-3: (<strong>".$query."</strong>)";
									//$log->addEntry ( array ('comment' => 'Этап Категории - 2.1 Выполнен запрос (ПРОВЕРКА) № 0: ('.$query.')') );
								}
								else
								{
									$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса (ПРОВЕРКА) № 0: (<strong>".$query."</strong>)";
									//$log->addEntry ( array ('comment' => 'Этап Категории - 2.2 Неудача (ПРОВЕРКА): Ошибка запроса № 0: ('.$query.')') );
								}
							}
							//
						}

						$ins = new stdClass ();
						//Если ее еще нет
						if($count_check_id < 1)
						{
							//$log->addEntry ( array ('comment' => 'Этап Категории - 3.1 Ношлось то что надо 1 - '.$id_category1.' для - '.$data['name'].' = '.$id_virtuemart_product ) );
							if (VM_VERVM == '2')
							{
								$ins->virtuemart_product_id  = ( int )$id_virtuemart_product;
								$ins->virtuemart_category_id = ( int )$id_category1;
								$ins->ordering   = NULL;
							}
							else
							{
								$ins->category_id = ( int )$id_category1;
								$ins->product_id  = ( int )$id_virtuemart_product;
								$ins->product_list   = '1';
							}
							
							$table = getString("#__", (string)$dba['product_category_xref_db']);
							
							sqlinsertObject ( $table, $ins, "", $db, "" );
						}
					}
				}

				if(count($data['category_1c_id']) <= 0)	{
				//KIMKARUS.RU
				//Проверяем назначенные группы и убираем их
					$rows = sqlloadAssocList("SELECT id, virtuemart_category_id FROM #__".$dba['product_category_xref_db']." WHERE `virtuemart_product_id` = '".$id_virtuemart_product."'", $db);
					foreach($rows as $row)
					{
						$id_row = $row['id'];								
						$query = "DELETE FROM `#__".$dba['product_category_xref_db']."` WHERE `id` = '".$id_row."'";
						if (sqlQueryOK($query, $db))
						{
							$logs_http[] = "<strong>Загрузка товара (ПРОВЕРКА)</strong> - Выполнен запрос № 1-4: (<strong>".$query."</strong>)";
							//$log->addEntry ( array ('comment' => 'Этап Категории - 5.1 Выполнен запрос (ПРОВЕРКА) № 0: ('.$query.')') );
						}
						else
						{
							$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка запроса (ПРОВЕРКА) № 0: (<strong>".$query."</strong>)";
							//$log->addEntry ( array ('comment' => 'Этап Категории - 5.2 Неудача (ПРОВЕРКА): Ошибка запроса № 0: ('.$query.')') );
						}
					}
				}
				else
				{
				}
					if(isset($data['product_image']) and $system_data['change'] == true)
					{
						foreach ($data['product_image'] as $img )
						{
							$data['file'] = substr ( $img, 16 );
							if(substr ( $data['file'], -4 ) == 'jpeg')
							{
								$system_data['tbn_img'] = str_replace(".jpeg", "", $data['file']);
								$system_data['small_img'] = "resized".DS.$system_data['tbn_img']."_".VM_TBN_H."x".VM_TBN_W.".".VM_JPG_S;
								$system_data['meta_img'] = "jpeg";
								$system_data['mimetype'] = $system_data['meta_img'];
							}
							else
							{
								$system_data['meta_img'] = substr ( $data['file'], - 3 );
								$system_data['tbn_img'] = str_replace(".".$system_data['meta_img'], "", $data['file']);
								$system_data['small_img'] = "resized".DS.$system_data['tbn_img']."_".VM_TBN_H."x".VM_TBN_W.".".$system_data['meta_img'];
								if ($system_data['meta_img'] == 'jpg')
								{
									$system_data['mimetype'] = 'jpeg';
								}
								else
								{
									$system_data['mimetype'] = $system_data['meta_img'];
								}
							}
							
							if (VM_VERVM == '2')
							{
								$ins = new stdClass ();
								$ins->virtuemart_media_id = NULL;
								$ins->virtuemart_vendor_id = '1';
								$ins->file_title = (string)$data['name'];
								$ins->file_description = (string)$data['description'];
								$ins->file_meta = '';
								$ins->file_mimetype = 'image/'.$system_data['mimetype'];
								$ins->file_type = 'product';
								if (substr ( $data['file'], - 4 ) == 'jpeg')
								{
									$ins->file_url = JPATH_PICTURE.DS.str_replace(".jpeg", "", $data['file']).".".VM_JPG_S;
								}
								else
								{
									$ins->file_url = JPATH_PICTURE.DS.$data['file'];
								}
								$ins->file_url_thumb = JPATH_PICTURE.DS.$system_data['small_img'];
								$ins->file_is_product_image = '1';
								$ins->file_is_downloadable = '0';
								$ins->file_is_forSale = '0';
								$ins->file_params = '';
								$ins->ordering = NULL;
								$ins->shared = '0';
								$ins->published = '1';
								$ins->created_on = date ('Y-m-d H:i:s');
								$ins->created_by = $id_admin;
								$ins->modified_on = date ('Y-m-d H:i:s');
								$ins->modified_by = $id_admin;
								
								$table = getString("#__", DBBASE.'_medias');
								
								sqlinsertObject ( $table, $ins, "`virtuemart_media_id`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_medias</strong>" );
																
								$data['media_id'] = ( int ) $sqlObjectID;
								
								$ins = new stdClass ();
								$ins->id = NULL;
								$ins->virtuemart_product_id = (int)$data['current_id'];
								$ins->virtuemart_media_id = (int)$data['media_id'];
								$ins->ordering = '0';
								
								$table = getString("#__", DBBASE.'_product_medias');
								
								sqlinsertObject ( $table, $ins, "", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".DBBASE."_product_medias</strong>" );
								
							}
							else
							{
								$rows = sqlloadObject("SELECT * FROM #__".DBBASE."_product_files where `file_product_id` = '" . (int)$data['current_id'] . "'", $db);$
								$update = array();
								if($rows) 
								{
									$file_id = $rows->file_id;
									$file_name = $rows->file_name;
									$file_title = $rows->file_title;
									$file_extension = $rows->file_extension;
									$file_mimetype = $rows->file_mimetype;
									$file_url = $rows->file_url;
									$file_image_thumb_height = $rows->file_image_thumb_height;
									$file_image_thumb_width = $rows->file_image_thumb_width;
									
									if ($file_name != $system_data['small_img'])
									{
										$update['file_name'] = "`file_name`='".(string)$system_data['small_img']."'";
									}
									if ($file_title != $data['name'])
									{
										$update['file_title'] = "`file_title`='".(string)$data['name']."'";
									}
									if ($file_extension != $system_data['meta_img'])
									{
										$update['file_extension'] = "`file_extension`='".(string)$system_data['meta_img']."'";
									}
									if ($file_mimetype != 'image/'.$system_data['meta_img'])
									{
										$update['file_mimetype'] = "`file_mimetype`='image/".$system_data['meta_img']."'";
									}
									
									
									if (substr ( $data['file'], - 4 ) == 'jpeg')
									{
										if ($file_url != 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS.str_replace(".jpeg", "", $data['file']).".".VM_JPG_S)
										{
											$update['file_url'] = "`file_url`='components".DS."com_virtuemart".DS."shop_image".DS."product".DS.str_replace(".jpeg", "", $data['file']).".".VM_JPG_S."'";
										}
									}
									else
									{
										if ($file_url != 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS.$data['file'])
										{
											$update['file_url'] = "`file_url`='components".DS."com_virtuemart".DS."shop_image".DS."product".DS.$data['file']."'";
										}
									}
									
									if ($file_image_thumb_height != VM_TBN_H)
									{
										$update['file_image_thumb_height'] = "`file_image_thumb_height`='".(int)VM_TBN_H."'";
									}
									if ($file_image_thumb_width != VM_TBN_W)
									{
										$update['file_image_thumb_width'] = "`file_image_thumb_width`='".(int)VM_TBN_W."'";
									}
									
									if(!empty($update))
									{
										$sql_upd = "";
										
										foreach($update as $upd )
										{
											$sql_upd .= $upd.", ";
										}
										
										$sql = "UPDATE #__".DBBASE."_product_files SET ".$sql_upd."`file_is_image`=1 where `file_id`='".$file_id."'";
										if (!sqlQueryOK($sql, $db))
										{
											$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Невозможно обновить дополнительную картинку id - ' . $file_id ) );
											$log->addEntry ( array ('comment' => 'Этап 4.1.3) ' . $sql ) );
											if(!defined( 'VM_SITE' ))
											{
												echo 'failure\n';
												echo 'error mysql update\n';
												echo $sql;
											}
											else
											{
												$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно обновить дополнительную картинку id - <strong>".$file_id."</strong>";
												$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибочный запрос - <strong>".$sql."</strong>";
											}
											if(!defined( 'VM_SITE' ))
											{
												die;
											}
											else
											{
												$die = true;
											}
										}
									}
								}
								else
								{
									$ins = new stdClass ();
									$ins->file_id = NULL;
									$ins->file_product_id = ( int )$data['current_id'];
									$ins->file_name = $system_data['small_img'];
									$ins->file_title = (string)$data['name'];
									$ins->file_description = '';
									$ins->file_extension = $system_data['meta_img'];
									$ins->file_mimetype = 'image/'.$system_data['meta_img'];
									if (substr ( $data['file'], - 4 ) == 'jpeg')
									{
										$ins->file_url = 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS .str_replace(".jpeg", "", $data['file']).".".VM_JPG_S;
									}
									else
									{
										$ins->file_url = 'components'.DS.'com_virtuemart'.DS.'shop_image'.DS.'product'.DS . $data['file'];
									}
									$ins->file_published = '1';
									$ins->file_is_image = '1';
									$ins->file_image_height = '';
									$ins->file_image_width = '';
									$ins->file_image_thumb_height = VM_TBN_H;
									$ins->file_image_thumb_width = VM_TBN_W;
									
									$table = getString("#__", DBBASE.'_product_files');
									
									sqlinsertObject ( $table, $ins, "`file_id`", $db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>vm_product_files</strong>" );
									
								}
							}
						}
					}
				}
			else
			{
				if((int)$data['current_id'] > 0)
				{
					$log->addEntry ( array ('comment' => 'Этап 4.1.3) Неудача: Нет данных по продукту id - ' . (int)$data['current_id'] ) );
					if(!defined( 'VM_SITE' ))
					{
						echo 'failure\n';
						echo 'error mysql\n';
					}
					else
					{
						$logs_http[] = "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Нет данных (2) по продукту id - <strong>".(int)$data['current_id']."</strong>";
					}
					if(!defined( 'VM_SITE' ))
					{
						die;
					}
					else
					{
						$die = true;
					}
				}
			}
}
function createProductUid($product_id, $product_uid, $product_nds, $product_hash){
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
	//
	if($product_id < 1) return;
	$ins = new stdClass ();
	$ins->product_id = ( int )$product_id;
	$ins->c_id = (string)$product_uid;
	$ins->tax_id = (int)$product_nds;
	$ins->hash = (string)$product_hash;
	
	$table = getString("#__", (string)$dba['product_to_1c_db']);
	sqlinsertObject ( $table, $ins,"",$db, "<strong>Загрузка товара</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['product_to_1c_db']."</strong>");
}
function checkBrockenProductUid(){
	
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
	
	//Проверка битого ключа, очищение битого ключа
	$rows = sqlloadObject("SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($data['id']) . "'", $db);
	$rows_id = 0;
	
	//$log->addEntry ( array ('comment' => '$rows - ' . count($rows) . ' добавлен') );
	
	if(count($rows) < 1)
	{
		$res = sqlloadResult("DELETE FROM `#__".$dba['product_to_1c_db']."` where `c_id` = '" . $db->getEscaped($data['id']) . "'", $db);
		if($res != '') $rows_id = $res;
	} 
	else
	{
		if(intval($rows_id) < 1)
		{
			$res = sqlloadResult("DELETE FROM `#__".$dba['product_to_1c_db']."` where `c_id` = '" . $db->getEscaped($data['id']) . "'", $db);
		}
	}


	$res = sqlloadResult("DELETE FROM `#__".$dba['product_to_1c_db']."` where `product_id` = '0'", $db);
}
function checkExistProductUid(){
	
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
	$rows_uid = 0;
	//Проверка на существование
	$rows_uid = sqlloadObject("SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($data['id']) . "'", $db);
	//
	if($rows_uid == '')
	{
		//если нет, ищим по наименованию
		$rows_uid = sqlloadResult("SELECT ". $dba['pristavka'] ."product_id FROM #__". $dba['product_ln_db'] ." where `product_name` LIKE '%" . strval($data['name']) . "%'", $db);
		//
		if($rows_uid != '')
		{
			//добавляем найденный ид в передаточную таблицу
			createProductUid($rows_uid, $data['id'], $data['nds'], $data['hash']);
			//$data['current_id'] = $rows_name;
			//$isExist = true;
		} else { $rows_uid = 0; }
	}
	//
	return $rows_uid;
}
function getUid($id, $table){
	
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
	$got_id = true;
	//Проверка на существование
	$rows_uid = sqlloadResult("SELECT product_id FROM #__".$dba['product_to_1c_db']." where `c_id` = '" . $db->getEscaped($data['id']) . "'", $db);
	//
	if($rows_uid == '')
	{
		//если нет, ищим по наименованию
		$rows_name = sqlloadResult("SELECT ". $dba['pristavka'] ."product_id FROM #__". $dba['product_ln_db'] ." where `product_name` LIKE '%" . strval($data['name']) . "%'", $db);
		//
		if($rows_name != '')
		{
			//добавляем найденный ид в передаточную таблицу
			createProductUid($rows_name, $data['id'], $data['nds'], $data['hash']);
			$data['current_id'] = $rows_name;
			$got_id = true;
		} else { $got_id = false; }
	} else {
		$data['current_id'] = $rows_uid;
		$got_id = true; }
	//
	return $got_id;
}
function deleteProductMirror(){
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http, $die;
	}
}
function sqlloadAssocList($sql="", $db_f){
	
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	$listSql = NULL;
	if($sql != ""){
		$db->setQuery ( $sql );
	}
	if(!$db->query()){
		$log->addEntry ( array ('comment' => 'sqlloadAssocList - '.$sql.' ERROR - '.$db->stderr() ) );
	} else {
		$listSql = $db->loadAssocList();
	}
	
	return $listSql;
}
function sqlloadObjectList($sql="", $db_f){
	
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	$listSql = NULL;
	if($sql != ""){
		$db->setQuery ( $sql );
	}
	if(!$db->query()){
		$log->addEntry ( array ('comment' => 'sqlloadObjectList - '.$sql.' ERROR - '.$db->stderr() ) );
	} else {
		$listSql = $db->loadObjectList ();
	}
	return $listSql;
}
function sqlloadResult($sql="", $db_f){
	
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	$listSql = NULL;
	if($sql != ""){
		$db->setQuery ( $sql );
	}
	if(!$db->query()){
		$log->addEntry ( array ('comment' => 'sqlloadResult - '.$sql.' ERROR - '.$db->stderr() ) );
	} else {
		$listSql = $db->loadResult();
	}
	return $listSql;
}
function sqlQueryOK($sql="", $db_f){
	
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;

	if($sql != ""){
		$db->setQuery ( $sql );
		if ($db->query ()){
			return TRUE;
		} else {
			$log->addEntry ( array ('comment' => 'sqlQueryOK - '.$sql.' ERROR - '.$db->stderr() ) );
			return FALSE;
		}
	}
}
function sqlloadObject($sql="", $db_f){
	
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	$listSql = NULL;
	if($sql != ""){
		$db->setQuery ( $sql );
	}
	if(!$db->query()){
		$log->addEntry ( array ('comment' => 'sqlloadObject - '.$sql.' ERROR - '.$db->stderr() ) );
	} else {
		$listSql = $db->loadObject();
	}
	
	return $listSql;
}
function sqlinsertObject($sqlTable="", $sqlIns=NULL, $keyColumn="", $db_f=NULL, $comment=""){

	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	$sqlReply = false;
	$sql_error = "";

	if(strlen($sqlTable) <= 0 || $sqlIns == NULL){
		$sql_error = $db->stderr();
		$sqlReply = false;
	}
	else {
		if(strlen($keyColumn) <= 0){
			
			if(! $db->insertObject ( $sqlTable, $sqlIns )){
				$sql_error = $db->stderr();
			} else {
				
				$sqlReply = true;
			}
		} else {
			if (! $db->insertObject ( $sqlTable, $sqlIns, $keyColumn )){
				$sql_error = $db->stderr();
			} else {

				$sqlReply = true;
			}
		}
	}
	if($sqlReply) {
		$sqlObjectID = $db->insertid();
	}
	if(!$sqlReply){
		$log->addEntry ( array ('comment' => 'sqlinsertObject - '.$sqlTable.' column ='.$keyColumn.' - ERROR - '.$sql_error ) );
		if(!defined( 'VM_SITE' )){
			echo 'failure\n';
			echo 'error mysql\n';
		}
		else{
			$logs_http[] = $comment;
		}
		if(!defined( 'VM_SITE' )){
			die;
		}
		else{
			$die = true;
		}
	}
	return $sqlReply;
}
function getHashData(){
	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	$hash = array_md5($data);
	//$log->addEntry ( array ('comment' => 'Hash - '.$hash ) );
	return $hash;
}
function getString($str1="",$str2=""){

	global $log, $db, $dba, $id_admin, $username, $lang_1c ,$data, $product_parametres, $system_data, $sqlObjectID;
	
	$str1=strval($str1);
	$str2=strval($str2);
	
	if(strlen($str2)<=0){
		$str = "";
	} else {
		$str = $str1.$str2;
	}
	//$log->addEntry ( array ('comment' => 'Str1 - '.$str1.' str2 - '.$str2.' str - '.$str ) );
	return $str;
}
function array_md5($array) {
    //since we're inside a function (which uses a copied array, not 
    //a referenced array), you shouldn't need to copy the array
    array_multisort($array);
    return md5(json_encode($array));
}
?>