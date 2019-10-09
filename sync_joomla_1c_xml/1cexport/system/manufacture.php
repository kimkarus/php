<?php


if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

$manuf = new XMLReader();

function inserManufactures($xml) 
{
	//$read_manuf = new XMLReader();
	//$read_manuf->XML($xml);
	inserManufacture($xml);
	/*while($read_manuf->read())
	{
		if($read_manuf->nodeType == XMLReader::ELEMENT) 
		{
			switch($read_manuf->name)
			{
			case 'Производитель':		
				inserManufacture($read_manuf->readOuterXML());
				break;
			}
		}

	}*/
}
function inserManufacture($xml)
{
	global $log, $db, $dba, $id_admin, $manuf, $data;
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
					
	$data = array();

	$data['id'] = "";
	$data['name'] = "";
	$data['manuf'] = "";
	$data['published'] = '';
	$manuf = new XMLReader();
	$manuf->XML($xml);			
	while($manuf->read()) 
	{
		if($manuf->nodeType == XMLReader::ELEMENT ) 
		{
			switch($manuf->name) 
			{
							
				case 'Ид': 
					$data['id'] = $manuf->readString();
					//$logs_http[] = $data['id'];
					break;
									
				case 'Наименование':
					$data['name'] = trim($manuf->readString());
					$data['manuf'] = trim($manuf->readString());
					$data['published'] = "1";
					break;
				//case 'ВариантыЗначений': 					
				/*case 'Наименование': 
					$xml_man = $manuf->readOuterXML();
					$xml_man = simplexml_load_string($xml_man);
					$data['manuf'] = $xml_man->Значение;	
					
					unset($xml_man);
					
					$manuf->next();
					break;*/
				case 'Публичный':
					$str = trim($manuf->readString());
					if($str=="true") $data['published'] = "1";
					else $data['published'] = "0";
					break;
			}
		}
	}
	//$log->addEntry ( array ('comment' => 'Этап 4.1.3) Идем добавлять производителя - '.$data['manuf'] ) );
	makeManufacture($data);
}


function makeManufacture($data) 
{
	global global $log, $db, $dba, $id_admin, $manuf, $data;
	
	
	if (defined( 'VM_SITE' ))
	{
		global $logs_http;
	}
	
	if (isset($data['name']) and $data['name'] != "" and isset($data['manuf']) and $data['manuf'] != "")
	{
		/*if (VM_VERVM == '1')
		{
			$sql = "SELECT ".$dba['pristavka']."manufacturer_id FROM #__".$dba['manufacturer_db']." where `c_id` = '" . $data['id'] . "'";
		}
		else
		{
			$sql = "SELECT ".$dba['pristavka']."manufacturer_id 
				FROM #__".$dba['manufacturer_db']." as man 
				LEFT JOIN #__".$dba['manufacturer_ln_db']." as man_ln 
				ON (man.".$dba['pristavka']."manufacturer_id=man_ln.".$dba['pristavka']."manufacturer_id) 
				WHERE man_ln.mf_name = '".$data['manuf']."'";
		}*/
		//$log->addEntry ( array ('comment' => 'Этап 4.1.3) Ищим производителя по базе - '.$data['manuf'] ) );
		$sql = "SELECT manufacturer_id FROM #__".$dba['manufacturer_to_1c_db']." where `c_manufacturer_id` = '" . $data['id'] . "'";
		$db->setQuery ( $sql );
		$rows_sub_Count = $db->loadResult ();
		
		if (isset($rows_sub_Count) and $rows_sub_Count != '')
		{
			//Обновляем
		}
		else
		{
			//$log->addEntry ( array ('comment' => 'Этап 4.1.3) Такого нет добавляем - '.$data['manuf'] ) );
			//Добавляем
			$ins = new stdClass ();
			if (VM_VERVM == '2')
			{
				$ins->virtuemart_manufacturer_id = NULL;
				$ins->virtuemart_manufacturercategories_id = '0';
				$ins->hits = '0';
				$ins->published = intval($data['published']);
				$ins->created_on = date ('Y-m-d H:i:s');
				$ins->created_by = $id_admin;
				$ins->modified_on = date ('Y-m-d H:i:s');
				$ins->modified_by = $id_admin;
			}
			else
			{
				$ins->manufacturer_id = NULL;
				$ins->mf_name = $data['manuf'];
				$ins->mf_email = '';
				$ins->mf_desc = $data['manuf'];
				$ins->mf_category_id = '0';
				$ins->mf_url = '';
			}
			//$logs_http[] = $db->insertObject ( '#__'.$dba['manufacturer_db'], $ins, $dba['pristavka']."manufacturer_id" );
			//echo $data['manuf'];
			if (! $db->insertObject ( '#__'.$dba['manufacturer_db'], $ins, $dba['pristavka']."manufacturer_id" )) 
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['manufacturer_db'] ) );
				
				if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql';
				}
				else
				{
					$logs_http[] = "<strong>Производители</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['manufacturer_db']."</strong>";
				}
				die;
			}
			else
			{
				//$log->addEntry ( array ('comment' => 'Этап 4.1.3) Добавили в базу - '.$data['manuf'] ) );
			}
			
			if (VM_VERVM == '2')
			{
				$man_id = ( int ) $ins->virtuemart_manufacturer_id;
				
				$slug_str = str_replace("(", "", $data['manuf']);
				$slug_str = str_replace(")", "", $slug_str);
				$slug_str = str_replace(".", "_", $slug_str);
				$slug_str = str_replace("/", "_", $slug_str);
				$slug_str = str_replace("-", "_", $slug_str);
				$slug_str = str_replace("+", "_", $slug_str);
				$slug_str = str_replace("=", "_", $slug_str);
				$slug_str = str_replace("&plusmn;", "_", $slug_str);
				$slug_str = str_replace(",", "", $slug_str);
				$slug_str = str_replace("&frasl;", "_", $slug_str);
				$slug_str = str_replace("&#8260;", "_", $slug_str);
				$slug_str = str_replace(":", "_", $slug_str);
				$slug_str = strtr($slug_str,"&frasl;", "_");
				$slug_str = strtr($slug_str,"&#8260;", "_");
				$slug_str = strtr($slug_str,":", "_");
				
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
					
					$slug = implode("_", $s_name);
				}
				else
				{
					$slug =  translitString($data['manuf']);
				}
				
				if (empty ($slug) or $slug == "")
				{
					$slug = $name;
				}
				
				$slug = str_replace("/", "_", $slug);
				$slug = str_replace("-", "_", $slug);
				
				$ins = new stdClass ();
				$ins->virtuemart_manufacturer_id = (int)$man_id;
				$ins->mf_name = (string)$data['manuf'];
				$ins->mf_email = '';
				$ins->mf_desc = (string)$data['manuf'];
				$ins->mf_url = '';
				$ins->slug = (string)$slug."_".$man_id;
				
				//$logs_http[] = defined( 'VM_SITE' );
				
				if (! $db->insertObject ( '#__'.$dba['manufacturer_ln_db'], $ins )) 
				{
					$log->addEntry ( array ('comment' => 'Этап 4.1.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['manufacturer_ln_db'] ) );
					
					if(!defined( 'VM_SITE' ))
					{
						echo 'failure\n';
						echo 'error mysql';
					}
					else
					{
						$logs_http[] = "<strong>Производители</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['manufacturer_ln_db']."</strong>";
					}
					die;
				}
				
			}
			else
			{
				$man_id = ( int ) $ins->manufacturer_id;
			}
			
			$ins = new stdClass ();
			$ins->manufacturer_id = $man_id;
			$ins->c_manufacturer_id = $data['id'];
			
			if (! $db->insertObject ( '#__'.$dba['manufacturer_to_1c_db'], $ins )) 
			{
				$log->addEntry ( array ('comment' => 'Этап 4.1.2) Неудача: Невозможно вставить запись в таблицу - '.$dba['manufacturer_to_1c_db'] ) );
					
				if(!defined( 'VM_SITE' ))
				{
					echo 'failure\n';
					echo 'error mysql';
				}
				else
				{
					$logs_http[] = "<strong>Производители</strong> - <strong><font color='red'>Неудача:</font></strong> Невозможно вставить запись в таблицу - <strong>".$dba['manufacturer_to_1c_db']."</strong>";
				}
				die;
			}
		
			$log->addEntry ( array ('comment' => 'Этап 4.1.2) Производитель '.$data['manuf'].' создан' ) );
			$logs_http[] = "<strong>Производители</strong> - Производитель <strong>".$data['manuf']."</strong> создан";
		
		}
	}

}
?>