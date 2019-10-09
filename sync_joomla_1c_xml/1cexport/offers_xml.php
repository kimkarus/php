<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
// Модуль: offers_xml.php - Импорт содержимого файла offers.xml
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

$logs_http[] = "<strong>Загрузка цен</strong> - Проверка базы данных совместимости 1с и VMSHOP";
$log->addEntry ( array ('comment' => 'Этап 4.2.1) Проверка базы данных совместимости 1с и VMSHOP') );

$res4 = $db->setQuery ( 'SHOW COLUMNS FROM "#__'.$dba['cashgroup_to_1c_db'].'"' );

if( !$db->query($res4)) 
{
	$db->setQuery ( 
			'CREATE TABLE 
			`#__'.$dba['cashgroup_to_1c_db'].'` ( 
			`cashgroup_id` int(10) unsigned NOT NULL,
			`c_id` varchar(255) NOT NULL,
			KEY (`cashgroup_id`),
			KEY `c_id` (`c_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	 );
	$db->query ();
	
	$logs_http[] = "<strong>Загрузка цен</strong> - База cashgroup_to_1c создана";
	$log->addEntry ( array ('comment' => 'Этап 4.2.1) База cashgroup_to_1c создана') );			
}
else
{
	$logs_http[] = "<strong>Загрузка цен</strong> - База cashgroup_to_1c существует";
	$log->addEntry ( array ('comment' => 'Этап 4.1.1) База cashgroup_to_1c существует') );	
}
$logs_http[] = "<strong>Загрузка цен</strong> - Добавление цен к товарам";
$log->addEntry ( array ('comment' => 'Этап 4.2.1) Добавление цен к товарам') );

$offersFile = JPATH_BASE_PICTURE. DS . $filename;

$reader = new XMLReader();
$reader->open($offersFile);

$offer = new XMLReader();

$cash_group = new XMLReader();

$base = new XMLReader();
$base->open($offersFile);


if(!$reader and !$base)
{
	$log->addEntry ( array ('comment' => 'Этап 4.2.1) Неудача: Ошибка открытия XML') );	
	$logs_http[] = "<strong>Загрузка цен</strong> - <strong><font color='red'>Неудача:</font></strong> Ошибка открытия XML";
	if(!defined( 'VM_SITE' ))
	{
		echo 'failure\n';
	}
	die();
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 4.2.1) XML offers.xml загружен') );
	$logs_http[] = "<strong>Загрузка цен</strong> - XML <strong>offers.xml</strong> загружен";
}

$log->addEntry ( array ('comment' => 'Этап 4.2.1) Базы созданы, переходим к процесу отчистки') );

$logs_http[] = "<strong>Загрузка цен</strong> - Все базы созданы, переходим к процесу отчистки";

$data = array();

$CAT = array();

while($base->read()) 
{
	if($base->nodeType == XMLReader::ELEMENT) 
	{
		switch($base->name) 
		{
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
				
				$log->addEntry ( array ('comment' => 'Этап 4.2.1) Версия схемы XML '.$vers_xml. ' VM_XML_VERS = '.VM_XML_VERS) );
				$logs_http[] = '<strong>Загрузка цен</strong> - Версия схемы XML '.$vers_xml. ' VM_XML_VERS = '.VM_XML_VERS;
				//$base->next();
				break;
				
			case 'ПакетПредложений':
				require_once(JPATH_BASE_1C .DS.'system'.DS.'clearbase.php');
				clearBase($base->getAttribute("СодержитТолькоИзменения"),'2');
				$modif = $base->getAttribute("СодержитТолькоИзменения");
				
				//$base->next();
				break;			
		}
	}
}
$base->close();

if ($modif == 'false')
{
	$log->addEntry ( array ('comment' => 'Этап 4.2.2) Базы отчищены, переходим к процесу создания категорий') );

	$logs_http[] = "<strong>Загрузка цен</strong> - Все базы созданы, переходим к процесу создания категорий";
}

while($reader->read()) 
{
	if($reader->nodeType == XMLReader::ELEMENT) 
	{
		switch($reader->name) 
		{
			case 'ТипЦены':
				// Подочернее добавление групп
				require_once(JPATH_BASE_1C .DS.'system'.DS.'cashgroup.php');
				inserCashgroup($reader->readOuterXML());
				$reader->next();
				break;
			
			case 'Предложение':
				// Подочернее добавление товара
				require_once(JPATH_BASE_1C .DS.'system'.DS.'offers.php');
				inserOffers($reader->readOuterXML());
				//$reader->next();
				break;
		}
	}
}



$log->addEntry ( array ('comment' => 'Этап 4.2.4) Все цены добавленны (обновленны)') );
$logs_http[] = "<strong>Загрузка цен</strong> - ---------------- Все цены добавленны (обновленны) ----------------";
$reader->close();


if (isset($handle)) 
{
	fclose($handle);
	unset($handle);
}


if (unlink ( JPATH_BASE_PICTURE.DS.'offers.xml' ))
{
	$logs_http[] = "<strong>Финал</strong> - ---------------- offers.xml удален ----------------";
}
if (unlink ( JPATH_BASE_1C .DS.'login.tmp' ))
{
	$logs_http[] = "<strong>Финал</strong> - ---------------- login.tmp удален ----------------";
}

$log->addEntry ( array ('comment' => 'Этам 4.2.3 Проверка остатков в пустых и не пустных категориях') );
$logs_http[] = " ---------------- Проверяем суммарные остатки материалов по категориям ---------------- ";
$sql = "SELECT #__".$dba['product_category_db'].".virtuemart_category_id, SUM(#__".$dba['product_db'].".product_in_stock) AS category_in_stock, #__".$dba['category_db'].".published FROM 
#__".$dba['product_category_db']." LEFT JOIN 
#__".$dba['product_db']." ON 
(#__".$dba['product_db'].".virtuemart_product_id=#__".$dba['product_category_db'].".virtuemart_product_id) LEFT JOIN 
#__".$dba['category_db']." ON 
(#__".$dba['category_db'].".virtuemart_category_id=#__".$dba['product_category_db'].".virtuemart_category_id) 
GROUP BY #__".$dba['product_category_db'].".virtuemart_category_id";
//$log->addEntry ( array ('comment' => "".$sql.""));
$db->setQuery($sql);
$table = $db->loadAssocList();
//$log->addEntry ( array ('comment' => print_r($table)));

if (!empty($table))
{
	foreach($table as $item)
	{
		$hided = false;
		//$logs_http[]= $item->virtuemart_category_id;
		//$log->addEntry ( array ('comment' => "Категория - ".$item["virtuemart_category_id"]) );
		if($item["category_in_stock"] <= 0 and $item["published"] == 1)
		{
			$sql = "UPDATE #__".$dba['category_db']." SET 
				`published` = '0'
			where `virtuemart_category_id`='".$item["virtuemart_category_id"]."'";
			$db->setQuery ( $sql );
			$db->query ();
			$hided = true;
			//$logs_http[] = "Изменили категорию (закрыли) - ".$item["virtuemart_category_id"];
		}
		else if($item["category_in_stock"] > 0 and $item["published"] == 0 and !$hided)
		{
			$sql = "UPDATE #__".$dba['category_db']." SET 
				`published` = '1'
			where `virtuemart_category_id`='".$item["virtuemart_category_id"]."'";
			$db->setQuery ( $sql );
			$db->query ();
			//$logs_http[] = "Изменили категорию (открыли) - ".$item["virtuemart_category_id"];
		}
		else{}
		//$log->addEntry ( array ('comment' => "".$sql.""));
	}
}
$logs_http[] = " ---------------- Закончили проверку остатков по категориям ---------------- ";

if(!defined( 'VM_SITE' ))
{
	echo "success\n";
}
?>