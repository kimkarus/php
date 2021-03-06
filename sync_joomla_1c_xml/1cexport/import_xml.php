<?php


if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

$logs_http[] = "<strong>Загрузка товара</strong> - Проверка базы данных совместимости 1с и VMSHOP";
$log->addEntry ( array ('comment' => 'Этап 4.1.1) Проверка базы данных совместимости 1с и VMSHOP') );

$res = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['product_to_1c_db'].'"' );

if( !$db->query($res)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['product_to_1c_db'].'` ( 
			`product_id` int(10) unsigned NOT NULL,
			`c_id` varchar(255) NOT NULL,
			`tax_id` int(10) unsigned NOT NULL,
			KEY (`product_id`),
			KEY `c_id` (`c_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();
	
	$logs_http[] = "<strong>Загрузка товара</strong> - База product_to_1c создана";
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База product_to_1c создана') );			
}
else
{
	$logs_http[] = "<strong>Загрузка товара</strong> - База product_to_1c уже существует";
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База product_to_1c уже существует') );	
}

$sql = 'SELECT `tax_id` FROM `#__'.$dba['product_to_1c_db'].'` WHERE 0';
$db->setQuery ( $sql );
$res = $db->query ();
if(!$res)
{
	$sql = 'ALTER TABLE `#__'.$dba['product_to_1c_db'].'` ADD `tax_id` INT( 10 ) unsigned NOT NULL'; 
	$db->setQuery ( $sql );
	$db->query ();
	
	$logs_http[] = "<strong>Загрузка товара</strong> - Недостоющие поля таблицы product_to_1c созданы";
}

$res = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['category_to_1c_db'].'"' );

if( !$db->query($res)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['category_to_1c_db'].'` ( 
			`category_id` int(10) unsigned NOT NULL,
 			`c_category_id` varchar(255) NOT NULL,
 			KEY (`category_id`),
 			KEY `c_id` (`c_category_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();	
	
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База category_to_1c создана') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База category_to_1c создана";			
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База category_to_1c существует') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База category_to_1c существует";	
}

$res = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['manufacturer_to_1c_db'].'"' );

if( !$db->query($res)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['manufacturer_to_1c_db'].'` ( 
			`manufacturer_id` int(10) unsigned NOT NULL,
 			`c_manufacturer_id` varchar(255) NOT NULL,
 			KEY (`manufacturer_id`),
 			KEY `c_id` (`c_manufacturer_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();	
	
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База manufacturer_to_1c создана') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База manufacturer_to_1c создана";			
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База manufacturer_to_1c существует') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База manufacturer_to_1c существует";	
}
//currencies
$res = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['currencies_to_1c_db'].'"' );

if( !$db->query($res)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['currencies_to_1c_db'].'` ( 
			`currency_id` int(10) unsigned NOT NULL,
 			`c_currency_id` varchar(255) NOT NULL,
 			KEY (`currency_id`),
 			KEY `c_id` (`c_currency_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();	
	
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База currencies_to_1c создана') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База currencies_to_1c создана";			
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База currencies_to_1c существует') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База currencies_to_1c существует";	
}

//customs
$res = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['customs_to_1c_db'].'"' );

if( !$db->query($res)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['customs_to_1c_db'].'` ( 
			`custom_id` int(10) unsigned NOT NULL,
 			`c_custom_id` varchar(255) NOT NULL,
 			KEY (`custom_id`),
 			KEY `c_id` (`c_custom_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();	
	
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База customs_to_1c создана') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База customs_to_1c создана";			
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База customs_to_1c существует') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База customs_to_1c существует";	
}

//$dba['custom_param_plg_to_1c_db']
$res = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['custom_param_plg_to_1c_db'].'"' );

if( !$db->query($res)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['custom_param_plg_to_1c_db'].'` ( 
			`custom_id` int(10) unsigned NOT NULL,
			`value` text  NOT NULL,
 			`c_custom_id` varchar(255) NOT NULL,
			`c_product_id` varchar(255) NOT NULL,
 			KEY (`custom_id`),
 			KEY `c_id` (`c_custom_id`)
			KEY `p_id` (`c_product_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();	
	
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База custom_param_plg_to_1c создана') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База custom_param_plg_to_1c создана";			
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База customs_to_1c существует') );
	$logs_http[] = "<strong>Загрузка товара</strong> - База customs_to_1c существует";	
}

$importFile = JPATH_BASE_PICTURE. DS . $filename;

$reader = new XMLReader();
$reader->open($importFile);

$product = new XMLReader();

$base = new XMLReader();
$base->open($importFile);


if(!$reader and !$base)
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) Неудача: Ошибка открытия XML') );	
	$logs_http[] = "<strong><font color='red'>Неудача:</font></strong> Ошибка открытия XML";
	
	if(!defined( 'VM_SITE' ))
	{
		echo 'failure\n';
	}
	die();
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) XML import.xml загружен') );
	$logs_http[] = "<strong>Загрузка товара</strong> - XML <strong>import.xml</strong> загружен";
}

$data = array();

$CAT = array();

$log->addEntry ( array ('comment' => 'Этап 4.1.1) Базы созданы, переходим к процесу отчистки') );

$logs_http[] = "<strong>Загрузка товара</strong> - Все базы созданы, переходим к процесу отчистки";

while($base->read()) 
{
	if($base->nodeType == XMLReader::ELEMENT) 
	{
		switch($base->name) 
		{
			case 'Каталог':
				require_once(JPATH_BASE_1C .DS.'system'.DS.'clearbase.php');
				clearBase($base->getAttribute("СодержитТолькоИзменения"),'1');
				$modif = $base->getAttribute("СодержитТолькоИзменения");
				
				//$base->next();
				break;
				
			case 'КоммерческаяИнформация':
				$vers_xml = $base->getAttribute("ВерсияСхемы");
				if (substr($vers_xml, 0, 4) == '2.04')
				{
					define ( 'VM_XML_VERS', '204' );
				}
				else
				{
					define ( 'VM_XML_VERS', '203' );
				}
				$log->addEntry ( array ('comment' => 'Этап 4.1.1) Версия схемы XML '.$vers_xml. ' VM_XML_VERS = '.VM_XML_VERS) );
				$logs_http[] = '<strong>Загрузка товара</strong> - Версия схемы XML '.$vers_xml. ' VM_XML_VERS = '.VM_XML_VERS;
				
				//$base->next();
				break;
								
		}
	}
}
$base->close();

if ($modif == 'false')
{
	$log->addEntry ( array ('comment' => 'Этап 4.1.2) Базы очищены, переходим к процесу создания категорий') );

	$logs_http[] = "<strong>Загрузка товара</strong> - Все базы созданы, переходим к процесу создания категорий";
}

while($reader->read()) 
{
	if($reader->nodeType == XMLReader::ELEMENT) 
	{
		switch($reader->name) 
		{
			case 'Группы':
				// Подочернее добавление групп
				$log->addEntry ( array ('comment' => 'Читаем группы') );
				require_once(JPATH_BASE_1C .DS.'system'.DS.'category.php');
				inserCategory($reader->readOuterXML());
				$reader->next();
				break;
			case 'Производители':
				// Подочернее добавление производителей
				$log->addEntry ( array ('comment' => 'Читаем производителей') );
				$read_manufes = $reader->readOuterXML();
				require_once(JPATH_BASE_1C .DS.'system'.DS.'manufacture.php');
				inserManufactures($read_manufes);
				$reader->next();
				//
				break;				
			case 'Свойства':
				// Подочернее добавление свойств
				$log->addEntry ( array ('comment' => 'Читаем свойства') );
				require_once(JPATH_BASE_1C .DS.'system'.DS.'properties.php');
				inserProperties($reader->readOuterXML());
				$reader->next();
				break;
				
			case 'Товары':
				// Подочернее добавление товара
				$log->addEntry ( array ('comment' => 'Читаем товары') );
				require_once(JPATH_BASE_1C .DS.'system'.DS.'product.php');
				inserProducts($reader->readOuterXML(),$modif);
				$reader->next();
				break;
		}
	}
	//$logs_http[] = $reader->name;
}
//Обновить значения свойств для плагина
//require_once(JPATH_BASE_1C .DS.'system'.DS.'properties.php');
//updateParametresProperties();
//

//if (VM_CAT_IMG == 'yes' and VM_VERVM == '2')
if (VM_CAT_IMG == 'yes')
{
	require_once(JPATH_BASE_1C .DS.'system'.DS.'cat_img.php');
	Ins_cat_img();
}

if(!defined( 'VM_SITE' ))
{
	echo "success\n";
}

$log->addEntry ( array ('comment' => 'Этап 4.1.5) Все товары добавленны (обновленны)') );
$logs_http[] = "<strong>Загрузка товара</strong> - Все товары добавленны (обновленны)";
$reader->close();

if (unlink ( JPATH_BASE_PICTURE.DS.$filename ))
{
	$logs_http[] = "<strong>Финал</strong> - ---------------- import.xml удален ----------------";	
}
?>