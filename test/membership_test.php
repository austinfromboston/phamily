<?php

#require_once( '../lib/simpletest/autorun.php');
require_once( 'test_helper.php');

class TestOfMembership extends UnitTestCase {
    function setUp( ){
        $this->membership = ORM::factory( 'membership');
        $this->mock_membership = new MockMember_Model( );
    }

    function test_find_or_create_by_supporter_key_and_groups_key_makes_a_membership_record( ) {
        $this->membership->db_proxy = $this->mock_membership;
        $this->mock_membership->expectAt( 0, '__set', array( 'supporter_key', '123'));
        $this->mock_membership->expectAt( 1, '__set', array( 'groups_key', 1));
        $this->mock_membership->expectOnce( 'save' );
        $this->membership->find_or_create_by_supporter_key_and_groups_key( '123', 1);
    }

}
?>
