<?php

// Database Configuration. Add your details below
// We know that "db_user" and "db_pass" are in cleartext here ;-)
// It's only for an educational purpose.

$dbOptions = array(
	'db_host' => 'localhost',
	'db_user' => 'CookieMonster',
	'db_pass' => 'WrongPassNoCookie!',
	'db_name' => 'aitec_webchat'
);

/* Database Config End */

//report everything except notice
error_reporting(E_ALL ^ E_NOTICE);

require "classes/DB.class.php";
require "classes/Chat.class.php";
require "classes/ChatBase.class.php";
require "classes/ChatLine.class.php";
require "classes/ChatUser.class.php";

session_name('webchat');
session_start();

try{
	// Connecting to the database
	DB::init($dbOptions);
	
	$response = array();
	
	// Handling the supported actions:
	switch($_GET['action']){
        case 'countUsers':
            $response = Chat::countUsers();
            break;

        case 'register':
            $response = Chat::register($_POST['name'],$_POST['email']);
            break;

	    case 'login':
			$response = Chat::login($_POST['name'],$_POST['email']);
		    break;
		
		case 'checkLogged':
			$response = Chat::checkLogged();
		    break;
		
		case 'logout':
			$response = Chat::logout();
		    break;
		
		case 'submitChat':
			$response = Chat::submitChat($_POST['chatText']);
		    break;
		
		case 'getUsers':
			$response = Chat::getUsers();
		    break;

        case 'userIsAdmin':
            $response = Chat::userIsAdmin();
            break;

        case 'getBlockedUsers':
            $response = Chat::getBlockedUsers();
            break;

		case 'getChats':
			$response = Chat::getChats($_GET['lastID']);
		    break;

        case 'blockUser':
            $response = Chat::blockUser($_GET['userName']);
            break;

        case 'unBlockUser':
            $response = Chat::unBlockUser($_GET['userName']);
            break;

        case 'getStatus':
            $response = Chat::getStatus();
            break;

		default:
			throw new Exception('Wrong action');
	}
	
	echo json_encode($response);
}
catch(Exception $e){
	die(json_encode(array('error' => $e->getMessage())));
}

?>