<?php


if ( !defined( 'VM_1CEXPORT' ) )
{
	echo "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

// Выводим форму логина

if (!isset($_SERVER['PHP_AUTH_USER'])) 
{
    header('WWW-Authenticate: Basic realm="Access denied"');
    header('HTTP/1.0 401 Unauthorized');
    $templ .= '<strong>Доступ запрещен! 3 раза будет введен неправильно логин/пароль, то будет запрещен доступ к панели!</strong>';
    exit;
} 

?>