<?php
/**
 * This file is part of the GithubRepoLister project
 * Copyright (c) Jan Rydrych <jan.rydrych@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
use GRL\Page\Page;


class InitPage extends Page {}

$initPage = new InitPage();
$dic = $initPage->getDIC();

$auth = new Auth(new PDO($dic->get('authDSN'), $dic->get('authUser'), $dic->get('authPassword'), $dic->get('authOptions')));

foreach ($newUsers as $user) {
	$userId = $auth->admin()->createUser($user['email'], $user['password'], $user['username']);
	echo 'User ' . $user['username'] . ' has been created...<br>';
}



