<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Ra_eventbooking
 * @author     Chris Vaughan  <ruby.tuesday@ramblers-webs.org.uk>
 * @copyright  2025 Ruby Tuesday
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Ramblers\Component\Ra_eventbooking\Site\Helper\Ra_eventbookingHelper as helper;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
//use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Ramblers\Component\Ra_eventbooking\Site\Helper\Ra_eventbookingHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
        ->useScript('form.validate');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_ra_eventbooking', JPATH_SITE);

$user = Factory::getApplication()->getIdentity();
$canEdit = Ra_eventbookingHelper::canUserEdit($this->item, $user);
?>

<div class="eventsetting-edit front-end-edit" >

    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
        </div>
    <?php endif; ?>
    <?php if (!$canEdit) : ?>
        <h3>
            <?php throw new \Exception(Text::_('COM_RA_EVENTBOOKING_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
        </h3>
    <?php else : ?>
        <?php if (!empty($this->item->id)): ?>
            <h1><?php // echo Text::sprintf('COM_RA_EVENTBOOKING_EDIT_ITEM_TITLE', $this->item->id);      ?></h1>
        <?php else: ?>
            <h1><?php echo Text::_('COM_RA_EVENTBOOKING_ADD_ITEM_TITLE'); ?></h1>
        <?php endif; ?>

        <form id="form-eventsetting"
              action="<?php echo Route::_('index.php?option=com_ra_eventbooking&task=eventsettingform.save'); ?>"
              method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

            <input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />

            <input type="hidden" name="jform[state]" value="<?php echo isset($this->item->state) ? $this->item->state : ''; ?>" />
            <div class="main-card">

                <?php
                echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'basic'));

                echo HTMLHelper::_('uitab.addTab', 'myTab', 'basic', Text::_('Basic Info'));

                echo $this->form->renderField('event_id');
                echo $this->form->renderField('total_places', 'params');
                echo $this->form->renderField('attendeetype', 'params');
                echo HTMLHelper::_('uitab.endTab');

                echo HTMLHelper::_('uitab.addTab', 'myTab', 'User access', Text::_('User access'));
                echo $this->form->renderField('maxattendees', 'params');
                echo $this->form->renderField('waitinglist', 'params');
                echo $this->form->renderField('userlistvisibletousers', 'params');
                echo HTMLHelper::_('uitab.endTab');

                echo HTMLHelper::_('uitab.addTab', 'myTab', 'guest', Text::_('Guest access'));
                echo $this->form->renderField('guest', 'params');
                echo $this->form->renderField('telephone_required', 'params');
                echo $this->form->renderField('maxguestattendees', 'params');
                echo $this->form->renderField('userlistvisibletoguests', 'params');
                echo HTMLHelper::_('uitab.endTab');
                echo HTMLHelper::_('uitab.addTab', 'myTab', 'payments', Text::_('Payments'));
                echo $this->form->renderField('payment_required', 'params');
                echo $this->form->renderField('payment_details', 'params');
                echo HTMLHelper::_('uitab.endTab');
                echo HTMLHelper::_('uitab.addTab', 'myTab', 'comms', Text::_('Comms'));
                echo '<fieldset class="options-form mb-4" style="border:1px solid #ccc;border-radius:6px;padding:12px 16px 4px 16px;margin-bottom:20px;"><legend style="float:none;width:auto;font-size:1.15rem;font-weight:600;padding:0 8px;">Booking contact</legend><p class="text-muted">Who is the contact for this event.</p>';
                echo $this->form->renderField('booking_contact_id', 'params');
                echo '</fieldset>';
                echo '<fieldset class="options-form mb-4" style="border:1px solid #ccc;border-radius:6px;padding:12px 16px 4px 16px;margin-bottom:20px;"><legend style="float:none;width:auto;font-size:1.15rem;font-weight:600;padding:0 8px;">Copies of booking emails</legend><p class="text-muted">Copies of the emails sent when someone books, cancels or joins the waiting list.</p>';
                echo $this->form->renderField('email_booking', 'params');
                echo $this->form->renderField('notrecommended', 'params');
                echo $this->form->renderField('email_waiting', 'params');
                echo $this->form->renderField('copy_group_contact', 'params');
                echo '</fieldset>';
                echo '<fieldset class="options-form mb-4" style="border:1px solid #ccc;border-radius:6px;padding:12px 16px 4px 16px;margin-bottom:20px;"><legend style="float:none;width:auto;font-size:1.15rem;font-weight:600;padding:0 8px;">Closing and final booking list</legend><p class="text-muted">When bookings close, and who is sent the final booking list. It is always sent to the booking contact and the group contact.</p>';
                echo $this->form->renderField('closingoption', 'params');
                echo $this->form->renderField('customclosingdate', 'params');
                echo $this->form->renderField('send_booking_list_onclosed', 'params');
                if (!helper::isClosingTaskEnabled()) {
                    echo $this->form->renderField('task_comment', 'params');
                }
                echo $this->form->renderField('walk_leader_id', 'params');
                echo $this->form->renderField('final_list_sent', 'params');
                echo '</fieldset>';
                echo '<fieldset class="options-form mb-4" style="border:1px solid #ccc;border-radius:6px;padding:12px 16px 4px 16px;margin-bottom:20px;"><legend style="float:none;width:auto;font-size:1.15rem;font-weight:600;padding:0 8px;">Emails to people who have booked</legend><p class="text-muted">Emails sent to the people who have booked places.</p>';
                echo $this->form->renderField('notify_event_changed', 'params');
                echo $this->form->renderField('bookingemailtextrequired', 'params');
                echo $this->form->renderField('bookingemailtext', 'params');
                echo '</fieldset>';

                echo HTMLHelper::_('uitab.endTab');

                echo HTMLHelper::_('uitab.addTab', 'myTab', 'event', Text::_('Event Details'));
                if ($this->item->event_id !== null) {
                    $ebRecord = helper::getEVB($this->item, 'Internal');
                    echo $ebRecord->displayEventData();
                    echo $ebRecord->displayBookingTable();
                    echo $ebRecord->displayWaitingTable();
                } else {
                    echo '<h3>Event data not available</h3>';
                }
                echo HTMLHelper::_('uitab.endTab');

                echo HTMLHelper::_('uitab.endTabSet');
                ?>
                <div class="control-group">
                    <div class="controls">

                        <?php if ($this->canSave): ?>
                            <button type="submit" class="validate btn btn-primary">
                                <span class="fas fa-check" aria-hidden="true"></span>
                                <?php echo Text::_('JSUBMIT'); ?>
                            </button>
                        <?php endif; ?>
                        <a class="btn btn-danger link-button granite"
                           href="<?php echo Route::_('index.php?option=com_ra_eventbooking&task=eventsettingform.cancel'); ?>"
                           title="<?php echo Text::_('JCANCEL'); ?>">
                            <span class="fas fa-times" aria-hidden="true"></span>
                            <?php echo Text::_('JCANCEL'); ?>
                        </a>
                    </div>
                </div>

                <input type="hidden" name="option" value="com_ra_eventbooking"/>
                <input type="hidden" name="task"
                       value="eventsettingform.save"/>
                       <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    <?php endif; ?>
</div>
