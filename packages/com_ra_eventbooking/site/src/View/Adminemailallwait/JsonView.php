<?php

/*
 * Admin email all on waiting/notification list
 *      parameters
 *         POST data
 *             id - id of event
 *             event - json version of walk/event Ramblers-webs format
 * 
 *      url
 *         index.php?option=com_ra_eventbooking&view=Adminemailallwait&format=json
 * 
 * 
 */

namespace Ramblers\Component\Ra_eventbooking\Site\View\Adminemailallwait;

use \Ramblers\Component\Ra_eventbooking\Site\Helper\Ra_eventbookingHelper as helper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\View\JsonView as BaseJsonView;

// use Joomla\CMS\Component\ComponentHelper;
// No direct access
defined('_JEXEC') or die;

class JsonView extends BaseJsonView {

    public function display($tpl = null) {
        try {
            $feedback = [];
            $data = helper::getPostedData();
            $ewid = $data->ewid;
            $ebRecord = helper::getEVBrecord($ewid, "Internal");
            
            $to = $ebRecord->wlc->getArray();
            $replyTo = $ebRecord->getEventContact();
            $copy = $replyTo;
            $title = $ebRecord->getEmailTitle('EMAIL');
            $content = helper::getEmailTemplate('emailwaiting.html', $ebRecord);
            $content = str_replace("{emailContent}", $data->emailContent, $content);
            helper::sendEmailsToUser($to, $copy, $replyTo, $title, $content);
            $feedback[] = '<h3>Emails have been sent</h3>';
            $record = (object) [
                        'feedback' => $feedback
            ];
            echo new JsonResponse($record);
        } catch (Exception $e) {
            echo new JsonResponse($e);
        }
    }
}
