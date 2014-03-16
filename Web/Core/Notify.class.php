<?php

/**
 * Class that manages notifications that need to be displayed to the user. These are stored in session
 * and can be retrieved whenever desired.
 */
class Notify {

    const NOTIFY_STATUS_TYPE_NOTICE = 'notice';
    const NOTIFY_STATUS_TYPE_INFO = 'info';
    const NOTIFY_STATUS_TYPE_ERROR = 'error';
    const NOTIFY_STATUS_TYPE_SUCCESS = 'success';
    const SESSION_VARIABLE_NAME = "notify_messages";

    /**
     * Retrieves the list of Notification objects from the session, or creates a new list if one has not been created yet
     * @return array[Notification] - an array of Notification
     */
    private static function GetList() {
        //supress any session warnings
        $oldErrorReportingLevel = error_reporting(E_ERROR | E_PARSE);
        //put your code here
        session_start();
        if (isset($_SESSION[Notify::SESSION_VARIABLE_NAME])) {
            $messages = $_SESSION[Notify::SESSION_VARIABLE_NAME];
        } else {
            $messages = [];
        }
        //restore error reporting to its previous state
        error_reporting($oldErrorReportingLevel);

        return $messages;
    }

    /**
     * Adds a Notification object to the list of notifications that will need to be displayed to the user
     * @param String $message - the message to notify the user of
     * @param String $status - the status of the notification: 'notice', 'info', 'error', 'success' are the valid options
     */
    public static function Add($message, $status) {
        $list = Notify::GetList();
        //make sure the status is valid
        if (Notify::ValidateStatus($status)) {
            $list[] = new Notification($message, $status);
            $_SESSION[Notify::SESSION_VARIABLE_NAME] = $list;
            return true;
        } else {
            trigger_error("Unknown notification status type in Notify::AddNotification()", E_USER_NOTICE);
            return false;
        }
    }

    /**
     * Determines if the status provided is one of the valid statuses
     * @param String $status - the status of the notification: 'notice', 'info', 'error', 'success' are the valid options
     * @return boolean
     */
    public static function ValidateStatus($status) {
        if (
                $status === Notify::NOTIFY_STATUS_TYPE_ERROR ||
                $status === Notify::NOTIFY_STATUS_TYPE_NOTICE ||
                $status === Notify::NOTIFY_STATUS_TYPE_INFO ||
                $status === Notify::NOTIFY_STATUS_TYPE_SUCCESS) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Clears the list of notifications, optionally based only on a single status type
     * @param String $status - the status of the notification: 'notice', 'info', 'error', 'success' are the valid options
     * @return type
     */
    public static function ClearNotifications($status = null) {
        //fetch the list and do nothing with it, thus clearing the list
        $list = Notify::GetNotifications($status);
        return true;
    }

    /**
     * Returns the list of notifications. 
     * @param String $status - the status of the notification: 'notice', 'info', 'error', 'success' are the valid options
     * @return type
     */
    public static function GetNotifications($status = null) {
        $list = Notify::GetList();
        $returnList = [];
        $remainingList = [];
        if ($status === null) {
            $returnList = $list;
        } else {
            if (Notify::ValidateStatus($status)) {
                //loop through the list of messages and only extract the ones of the specified status
                /* @var  $notification  Notification */
                foreach ($list as $notification) {
                    if ($notification->status == $status) {
                        //return this notification
                        $returnList[] = $notification;
                    } else {
                        //save this notification for later
                        $remainingList[] = $notification;
                    }
                }
            }
        }
        //store the unviewed notifications in the session for use later
        $_SESSION[Notify::SESSION_VARIABLE_NAME] = $remainingList;
        //return the fetched notifications
        return $returnList;
    }

}

/**
 * @property String $message - the message to notify the user of
 * @property String $status - the status of the notification: 'notice', 'info', 'error', 'success' are the valid options
 */
class Notification {

    public $message;
    public $status;

}
