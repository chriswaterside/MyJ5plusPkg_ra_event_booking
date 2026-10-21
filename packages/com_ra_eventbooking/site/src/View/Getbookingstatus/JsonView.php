<?php

/*
 * get the booking information for an event
 *      parameters
 *         POST data
 *             id - id of event
 *             event - json version of walk/event Ramblers-webs format
 * 
 *      url
 *         index.php?option=com_ra_eventbooking&view=getbookingstatus&format=json
 * 
 * 
 */

namespace Ramblers\Component\Ra_eventbooking\Site\View\Getbookingstatus;

use \Ramblers\Component\Ra_eventbooking\Site\Helper\Ra_eventbookingHelper as helper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\View\JsonView as BaseJsonView;

// No direct access
defined('_JEXEC') or die;

class JsonView extends BaseJsonView {

    public function display($tpl = null) {

        try {
            $feedback = [];
            $data = helper::getPostedData();
            $ewid = $data->ewid;
            $evb = helper::getEVBrecord($ewid, "Single");
            $user = helper::getUserData();
            $user->canManage = false;
            if ($evb !== null) {
                $user->canManage = $evb->userCanManage();
                if (!$user->canManage) {
                    // never send other people's personal details to the browser
                    $listVisible = $user->id > 0 ? $evb->options->userlistvisibletousers : $evb->options->userlistvisibletoguests;
                    $evb->maskForViewer($user->email, !$listVisible);
                    // a guest who has typed their email can see the amount paid on their own booking
                    $evb->revealPaidFor($data->md5Email ?? null);
                }
            }
            $record = (object) [
                        'feedback' => $feedback,
                        'evb' => $evb,
                        'user' => $user
            ];

            echo new JsonResponse($record);
        } catch (Exception $e) {
            echo new JsonResponse($e);
        }
    }
}
