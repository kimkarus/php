<?php


if ( !defined( 'VM_1CEXPORT' ) )
{
	print "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

if (VM_ZIP == 'yes')
{
	$upload = 'архив с файлами';	
}
else
{
	$upload = 'файлы без архива';
}
$log->addEntry ( array ('comment' => 'Этап 2) Выгружаем '.$upload) );	
print "zip=".VM_ZIP."\n";
print "file_limit=".VM_ZIPSIZE."\n";
$log->addEntry ( array ('comment' => 'Этап 2) Успешно') );
?>