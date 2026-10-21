<?php

/*
 * Eventchanged
 *      parameters
 *         POST data
 *             id - id of event
 *             event - json version of walk/event Ramblers-webs format
 * 
 *      url
 *         index.php?option=com_ra_eventbooking&view=Eventchanged&format=json
 * 
 * 
 */

namespace Ramblers\Component\Ra_eventbooking\Site\View\Eventchanged;

use \Ramblers\Component\Ra_eventbooking\Site\Helper\Ra_eventbookingHelper as helper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\View\JsonView as BaseJsonView;
use Joomla\CMS\Log\Log;

// use Joomla\CMS\Component\ComponentHelper;
// No direct access
defined('_JEXEC') or die;

class JsonView extends BaseJsonView {

    public function display($tpl = null) {
        try {
            $feedback = [];
            $data = helper::getPostedData();
            $ewid = $data->ewid;
            $ew = $data->ew;

            $ebRecord = helper::getEVBrecord($ewid, "Internal");
            // nothing to do (silently) if the event is not found or the data is not valid
            if ($ebRecord === null || !isset($ew->admin->id, $ew->admin->dateUpdated, $ew->basics->walkDate) || (string) $ew->admin->id !== (string) $ewid) {
                echo new JsonResponse((object) ['feedback' => $feedback]);
                return;
            }

            $oldDate = $ebRecord->event_data->getDate();
            $oldUpdated = $ebRecord->event_data->getDateUpdated();
            // only act on a genuinely newer version of the walk, this also stops
            // repeat requests (or several people viewing the walk) sending repeat emails
            if ($oldUpdated !== null && $oldUpdated !== '' && strtotime((string) $ew->admin->dateUpdated) <= strtotime((string) $oldUpdated)) {
                echo new JsonResponse((object) ['feedback' => $feedback]);
                return;
            }

            $ebRecord->createEventData($ew);
            $ebRecord->updateDatabase('Event');
            $newDate = $ebRecord->event_data->getDate();

            // do we email the people who have booked?
            $mode = $ebRecord->options->notify_event_changed;
            $send = false;
            if ($mode === 'always') {
                $send = true;
            } else if ($mode === 'date') {
                $send = ($oldDate !== '' && $oldDate !== $newDate);
            }
            $to = $ebRecord->blc->getArray();
            if ($send && count($to) > 0) {
                $attach = new \stdClass();
                $attach->data = $data->ics ?? '';
                $attach->type = 'string';
                $attach->encoding = 'base64';
                $attach->filename = 'walk.ics';
                $attach->mimeType = 'text/calendar';
                $replyTo = $ebRecord->getEventContact();
                $fields = $ebRecord->getAllEmailFields();
                helper::sendEmailsToUser($to, null, $replyTo, 'event_changed', $fields, $attach);
            }
            $record = (object) [
                        'feedback' => $feedback];
            echo new JsonResponse($record);
        } catch (Exception $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            Log::add("Exception thrown in $file on line $line: [Code $code] $message", Log::ERROR, 'com_ra_eventbooking');
            echo new JsonResponse($e);
        }
    }
}
