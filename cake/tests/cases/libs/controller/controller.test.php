<?php
/* SVN FILE: $Id$ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package			cake.tests
 * @subpackage		cake.tests.cases.libs.controller
 * @since			CakePHP(tm) v 1.2.0.5436
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
uses('controller' . DS . 'controller');

class ControllerPost extends CakeTestModel {
	var $name = 'ControllerPost';
	var $useTable = 'posts';
}
class ControllerComment extends CakeTestModel {
	var $name = 'ControllerComment';
	var $useTable = 'comments';
}
/**
 * Short description for class.
 *
 * @package    cake.tests
 * @subpackage cake.tests.cases.libs.controller
 */
class ControllerTest extends CakeTestCase {

	var $fixtures = array('core.post', 'core.comment');

	function testConstructClasses() {
		$Controller =& new Controller();
		$Controller->modelClass = 'ControllerPost';
		$Controller->passedArgs[] = '1';
		$Controller->constructClasses();
		$this->assertEqual($Controller->ControllerPost->id, 1);

		unset($Controller);

		$Controller =& new Controller();
		$Controller->uses = array('ControllerPost', 'ControllerComment');
		$Controller->passedArgs[] = '1';
		$Controller->constructClasses();
		$this->assertTrue(is_a($Controller->ControllerPost, 'ControllerPost'));
		$this->assertTrue(is_a($Controller->ControllerComment, 'ControllerComment'));

		unset($Controller);
	}

	function testPersistent() {
		$Controller =& new Controller();
		$Controller->modelClass = 'ControllerPost';
		$Controller->persistModel = true;
		$Controller->constructClasses();
		$this->assertTrue(file_exists(CACHE . 'persistent' . DS .'controllerpost.php'));
		$this->assertTrue(is_a($Controller->ControllerPost, 'ControllerPost'));
		unlink(CACHE . 'persistent' . DS . 'controllerpost.php');
		unlink(CACHE . 'persistent' . DS . 'controllerpostregistry.php');

		unset($Controller);
	}

	function testPaginate() {
		$Controller =& new Controller();
		$Controller->uses = array('ControllerPost', 'ControllerComment');
		$Controller->passedArgs[] = '1';
		$Controller->params['url'] = array();
		$Controller->constructClasses();

		$results = Set::extract($Controller->paginate('ControllerPost'), '{n}.ControllerPost.id');
		$this->assertEqual($results, array(1, 2, 3));

		$results = Set::extract($Controller->paginate('ControllerComment'), '{n}.ControllerComment.id');
		$this->assertEqual($results, array(1, 2, 3, 4, 5, 6));

		$Controller->modelClass = null;

		$Controller->uses[0] = 'Plugin.ControllerPost';
		$results = Set::extract($Controller->paginate(), '{n}.ControllerPost.id');
		$this->assertEqual($results, array(1, 2, 3));

		$Controller->passedArgs = array('page' => '-1');
		$results = Set::extract($Controller->paginate('ControllerPost'), '{n}.ControllerPost.id');
		$this->assertEqual($Controller->params['paging']['ControllerPost']['page'], 1);
		$this->assertEqual($results, array(1, 2, 3));

	}

	function testDefaultPaginateParams() {
		$Controller =& new Controller();
		$Controller->modelClass = 'ControllerPost';
		$Controller->params['url'] = array();
		$Controller->paginate = array('order' => 'ControllerPost.id DESC');
		$Controller->constructClasses();
		$results = Set::extract($Controller->paginate('ControllerPost'), '{n}.ControllerPost.id');
		$this->assertEqual($Controller->params['paging']['ControllerPost']['defaults']['order'], 'ControllerPost.id DESC');
		$this->assertEqual($Controller->params['paging']['ControllerPost']['options']['order'], 'ControllerPost.id DESC');
		$this->assertEqual($results, array(3, 2, 1));
	}

	function testFlash() {
		$Controller =& new Controller();
		$Controller->flash('this should work', '/flash');
		$result = $Controller->output;

		$expected = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<title>this should work</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style><!--
		P { text-align:center; font:bold 1.1em sans-serif }
		A { color:#444; text-decoration:none }
		A:HOVER { text-decoration: underline; color:#44E }
		--></style>
		</head>
		<body>
		<p><a href="/flash">this should work</a></p>
		</body>
		</html>';
 		$result = str_replace(array("\t", "\r\n", "\n"), "", $result);
		$expected =  str_replace(array("\t", "\r\n", "\n"), "", $expected);
		$this->assertEqual($result, $expected);
	}

	function testControllerSet() {
		$Controller =& new Controller();
		$Controller->set('variable_with_underscores', null);
		$this->assertTrue(array_key_exists('variable_with_underscores', $Controller->viewVars));

		$Controller->viewVars = array();
		$viewVars = array('ModelName' => array('id' => 1, 'name' => 'value'));
		$Controller->set($viewVars);
		$this->assertTrue(array_key_exists('modelName', $Controller->viewVars));

		$Controller->viewVars = array();
		$Controller->set('variable_with_underscores', 'value');
		$this->assertTrue(array_key_exists('variable_with_underscores', $Controller->viewVars));

		$Controller->viewVars = array();
		$viewVars = array('ModelName' => 'name');
		$Controller->set($viewVars);
		$this->assertTrue(array_key_exists('modelName', $Controller->viewVars));
	}

	function testRender() {
		Configure::write('viewPaths', array(TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'test_app' . DS . 'views'. DS, TEST_CAKE_CORE_INCLUDE_PATH . 'libs' . DS . 'view' . DS));

		$Controller =& new Controller();
		$Controller->viewPath = 'posts';

		$result = $Controller->render('index');
		$this->assertPattern('/posts index/', $result);

		$result = $Controller->render('/elements/test_element');
		$this->assertPattern('/this is the test element/', $result);

	}
}
?>