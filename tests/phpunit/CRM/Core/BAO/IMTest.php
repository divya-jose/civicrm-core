<?php
require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';

/**
 * Class CRM_Core_BAO_IMTest
 */
class CRM_Core_BAO_IMTest extends CiviUnitTestCase {
  /**
   * @return array
   */
  function get_info() {
    return array(
      'name' => 'IM BAOs',
      'description' => 'Test all Core_BAO_IM methods.',
      'group' => 'CiviCRM BAO Tests',
    );
  }

  function setUp() {
    parent::setUp();
  }

  /**
   * add() method (create and update modes)
   */
  function testAdd() {
    $contactId = Contact::createIndividual();

    $params = array();
    $params = array(
      'name' => 'jane.doe',
      'provider_id' => 1,
      'is_primary' => 1,
      'location_type_id' => 1,
      'contact_id' => $contactId,
    );

    CRM_Core_BAO_IM::add($params);

    $imId = $this->assertDBNotNull('CRM_Core_DAO_IM', 'jane.doe', 'id', 'name',
      'Database check for created IM name.'
    );

    // Now call add() to modify an existing IM

    $params = array();
    $params = array(
      'id' => $imId,
      'contact_id' => $contactId,
      'provider_id' => 3,
      'name' => 'doe.jane',
    );

    CRM_Core_BAO_IM::add($params);

    $isEditIM = $this->assertDBNotNull('CRM_Core_DAO_IM', $imId, 'provider_id', 'id', 'Database check on updated IM provider_name record.');
    $this->assertEquals($isEditIM, 3, 'Verify IM provider_id value is 3.');
    $isEditIM = $this->assertDBNotNull('CRM_Core_DAO_IM', $imId, 'name', 'id', 'Database check on updated IM name record.');
    $this->assertEquals($isEditIM, 'doe.jane', 'Verify IM provider_id value is doe.jane.');

    Contact::delete($contactId);
  }

  /**
   * AllIMs() method - get all IMs for our contact, with primary IM first
   */
  function testAllIMs() {
    $op = new PHPUnit_Extensions_Database_Operation_Insert;
    $op->execute(
      $this->_dbconn,
      $this->createFlatXMLDataSet(dirname(__FILE__) . '/dataset/im_test.xml')
    );

    $contactId = 69;
    $IMs = CRM_Core_BAO_IM::allIMs($contactId);

    $this->assertEquals(count($IMs), 3, 'Checking number of returned IMs.');

    $firstIMValue = array_slice($IMs, 0, 1);

    $this->assertEquals('alan1.smith1', $firstIMValue[0]['name'], 'Confirm primary IM value.');
    $this->assertEquals(1, $firstIMValue[0]['is_primary'], 'Confirm first IM is primary.');

    Contact::delete($contactId);
  }
}


