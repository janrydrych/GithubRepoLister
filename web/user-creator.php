<?php
/**
 * This file is part of the GithubRepoLister project
 */

/**
 * Users to create
 * @var array $newUsers
 */
$newUsers = array(
	array('username' => 'admin', 'password' => 'admin', 'email' => 'admin@dummymail.cz'),
);


require_once '../vendor/autoload.php';

use Delight\Auth\Auth;
use GRL\Configuration\Configuration;
use GRL\Configuration\Services;
use GRL\DIC;
use GRL\Page\Page;


class InitPage extends Page {}

$initPage = new InitPage(new DIC(new Configuration(), new Services()));

$dic = $initPage->getDIC();

$apConfig = $dic->get('authProvider');
$auth = new Auth(new PDO($apConfig['DSN'], $apConfig['username'], $apConfig['password'], $apConfig['options']));

foreach ($newUsers as $user) {
	$userId = $auth->admin()->createUser($user['email'], $user['password'], $user['username']);
	echo 'User ' . $user['username'] . ' has been created...<br>';
}

