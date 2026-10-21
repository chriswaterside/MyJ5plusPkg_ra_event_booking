<?php

/*
 * Admin email all bookers
 *      parameters
 *         POST data
 *             id - id of event
 *             event - json version of walk/event Ramblers-webs format
 * 
 *      url
 *         index.php?option=com_ra_eventbooking&view=Adminemailallbook&format=json
 * 
 * 
 */

namespace Ramblers\Component\Ra_eventbooking\Site\View\Adminemailallbook;

use \Ramblers\Component\Ra_eventbooking\Site\Helper\Ra_eventbookingHelper as helper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\View\JsonView as BaseJsonView;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserFactoryInterface;

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
            helper::requireManager($ebRecord);
            $to = $ebRecord->blc->getArray();

            $juser = Factory::getApplication()->getIdentity(); // sender is always the logged in user
            $from = new \stdClass();
            $from->email = $juser->email;
            $from->name = $juser->name;
            $mailTemplate = 'email_bookers';
            $fields = $ebRecord->getAllEmailFields();
            $fields['EMAILCONTENT'] = $data->emailContent;
            helper::sendEmailsToUser($to, null, $from, $mailTemplate, $fields);
            //  send copy to sender
            helper::sendEmailsToUser([$from], null, null, $mailTemplate, $fields);
            // send copy to group contact
            $contact = $ebRecord->getEventContact();
            if ($contact->email !== $from->email) {
                helper::sendEmailsToUser([$contact], null, $from, $mailTemplate, $fields);
            }
            $feedback[] = "<h3>Emails have been sent</h3>";
            $record = (object) [
                        'feedback' => $feedback
            ];
            echo new JsonResponse($record);
        } catch (Exception $e) {
            echo new JsonResponse($e);
        }
    }
}
