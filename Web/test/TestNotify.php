<?php

require_once(dirname(__FILE__) . '/../core/Notify.class.php');

class TestNotify extends UnitTestCase {

    function setUp() {
        
    }

    function testValidateStatus() {
        $this->assertTrue(Notify::ValidateStatus(Notify::NOTIFY_STATUS_TYPE_SUCCESS));
        $this->assertTrue(Notify::ValidateStatus(Notify::NOTIFY_STATUS_TYPE_INFO));
        $this->assertTrue(Notify::ValidateStatus(Notify::NOTIFY_STATUS_TYPE_NOTICE));
        $this->assertTrue(Notify::ValidateStatus(Notify::NOTIFY_STATUS_TYPE_ERROR));
    }

    function testAdd() {
        $this->assertTrue(Notify::Add("Some message", Notify::NOTIFY_STATUS_TYPE_SUCCESS));
        $this->assertTrue(Notify::Add("Some message", Notify::NOTIFY_STATUS_TYPE_INFO));
        $this->assertTrue(Notify::Add("Some message", Notify::NOTIFY_STATUS_TYPE_NOTICE));
        $this->assertTrue(Notify::Add("Some message", Notify::NOTIFY_STATUS_TYPE_ERROR));
    }

}
