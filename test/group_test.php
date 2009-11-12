<?php

#require_once( '../lib/simpletest/autorun.php');
require_once( 'test_helper.php');

class TestOfGroup extends UnitTestCase {
    function setUp( ){
        $this->group = new Group( 1 );
        $this->dia = new MockDemocracyInAction_API( );
        $this->orm = new MockORM( );
        $this->membership = new MockMembership_Model( );
    }

    function testGetDiaSupporterKeysCallsDia( ) {
        $this->group->dia = $this->dia;
        $this->dia->expectOnce( 'get_objects', array( 'supporter_groups', array( 'condition' => 'groups_KEY=1' )));
        $this->group->get_dia_supporter_keys( );
    }

    function testGetDiaSupporterKeysReturnsSupporterKeys( ){
        $this->dia->setReturnValue( 'get_objects', array( array('supporter_KEY'=>123,'email'=>'bob@test.org'), array('First_Name' => 'Sally', 'supporter_KEY'=>456)));  ;
        $this->group->dia = $this->dia;
        $expected_result = array( '123', '456');
        $this->assertEqual( $expected_result, $this->group->get_dia_supporter_keys( ));
    }

    function setup_group_testable_db( ){
        if( !class_exists( 'GroupTestableDB')) {
            Mock::generatePartial( 'Group', 'GroupTestableDB', array( 'db'));
        }
        if( get_class( $this->group ) != 'GroupTestableDB') {
            $this->group = new GroupTestableDB( $this );
            $this->group->__construct( 1 );

        }
    }

    function setup_membership_db( ) {
        $this->setup_group_testable_db( );
        $this->group->setReturnReference( 'db', $this->membership, array( 'membership' ));

        $this->membership->setReturnReference( 'where', $this->membership );

        $this->membership->setReturnValue( '__get', 888, array( 'groups_key' ) );
        $this->membership->setReturnValue( '__get', 123, array( 'supporter_key' ) );
        $this->membership->setReturnValue( 'find_all', array( $this->membership ));

    }

    function testGetDatabaseSupporterKeysCallsORMSearch( ) {
        $this->setup_membership_db( );
        $this->membership->expectOnce( 'where', array( 'groups_key', 1));
        $this->membership->expectOnce( 'find_all');
        $this->membership->setReturnValue( '__get', 123, array( 'supporter_key'));
        $membership_group = array( $this->membership );
        $this->membership->setReturnReference( 'find_all', $membership_group );

        $this->group->get_db_supporter_keys( );
    }

    function testGetDatabaseSupporterKeysReturnsKeys( ) {
        $this->setup_membership_db( );
        $this->assertEqual( $this->group->get_db_supporter_keys( ), array(123 ));

    }

    function setup_member_db( ) {
        $this->setup_group_testable_db( );
        $this->supporter = new MockMember_Model( );
        $this->group->setReturnReference( 'db', $this->supporter, array( 'member' ) );
        $this->supporter->setReturnReference( 'where', $this->supporter );
    }

    function testAddSupportersChecksForAnExistingDbRecord( ) {
        $this->setup_member_db( );
        $this->setup_membership_db( );

        $this->supporter->expectOnce( 'find_or_create_by_supporter_key', array( '123') );
        $this->group->add_supporters( array( '123') );
    }

    function example_dia_result( ) {
        return array( 
            'First_Name'    => 'Example',
            'Last_Name'     => 'Ex',
            'Email'         => 'test@example.com',
            'Last_Modified' => 'Wed Dec 17 2008 12:35:46 GMT-0500 (EST)',
            'supporter_KEY' => '123'
            );
    }

    function testAddSupportersMakesAMembershipRecord( ) {
        $this->setup_member_db( );
        $this->setup_membership_db( );
        $this->supporter->setReturnReference( 'find_or_create_by_supporter_key', $this->supporter );
        $this->membership->setReturnReference( 'find_or_create_by_supporter_key_and_groups_key', $this->membership );
        $this->supporter->setReturnValue( '__get', 456, array( 'id'));
        $this->membership->expectOnce( '__set', array( 'member_id', 456 ));
        $this->membership->expectOnce( 'save' );
        $this->group->add_supporters(array( '123') );
    }

}


?>
