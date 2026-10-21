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

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\User\UserFactoryInterface;

$user = Factory::getApplication()->getIdentity();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canCreate = $user->authorise('core.create', 'com_ra_eventbooking') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'eventsettingform.xml');
$canEdit = $user->authorise('core.edit', 'com_ra_eventbooking') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'eventsettingform.xml');
$canCheckin = $user->authorise('core.manage', 'com_ra_eventbooking');
$canChange = $user->authorise('core.edit.state', 'com_ra_eventbooking');
$canDelete = $user->authorise('core.delete', 'com_ra_eventbooking');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ra_eventbooking.list');
$wa->addInlineStyle('#eventsettingList tr.evb-unpublished > td{background-color:#e9ecef;color:#6c757d;}'
        . '#eventsettingList tr.evb-unpublished a{color:#6c757d;}'
        . '#eventsettingList .evb-badge{display:inline-block;margin-left:.5em;padding:.1em .5em;border-radius:.25rem;background:#6c757d;color:#fff;font-size:.8em;}');

/**
 * Count bookings and waiting list entries stored for an event
 * @return array [bookings, places, waiting]
 */
if (!function_exists('evbCounts')) {
function evbCounts($item) {
    $bookings = 0;
    $places = 0;
    $waiting = 0;
    if (!empty($item->booking_data)) {
        $rows = \json_decode($item->booking_data);
        if (is_array($rows)) {
            $bookings = count($rows);
            foreach ($rows as $r) {
                $places += (int) ($r->noAttendees ?? 1);
            }
        }
    }
    if (!empty($item->waiting_data)) {
        $rows = \json_decode($item->waiting_data);
        if (is_array($rows)) {
            $waiting = count($rows);
        }
    }
    return [$bookings, $places, $waiting];
}
}
?>

<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
<?php endif; ?>
<?php
// names of the booking contacts for the events listed
$contactIds = [];
$globalContactId = 0;
try {
    $globalContactId = (int) (\Ramblers\Component\Ra_eventbooking\Site\Helper\Ra_eventbookingHelper::getRawGlobals()->booking_contact_id ?? 0);
} catch (\Throwable $e) {
    $globalContactId = 0;
}
foreach ($this->items as $it) {
    $opts = \json_decode($it->params ?? '');
    $cid = (int) ($opts->booking_contact_id ?? 0);
    $it->contactId = $cid > 0 ? $cid : $globalContactId;
    if ($it->contactId > 0) {
        $contactIds[$it->contactId] = 0;
    }
}
$contactNames = [];
if ($contactIds) {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $ids = array_map('intval', array_keys($contactIds));
    $q = $db->getQuery(true)->select($db->quoteName(['id', 'name']))->from($db->quoteName('#__users'))
            ->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
    $db->setQuery($q);
    foreach ($db->loadObjectList() as $row) {
        $contactNames[(int) $row->id] = $row->name;
    }
}
?>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">
          <?php
          if (!empty($this->filterForm)) {
              echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
          }
          ?>
    <div class="table-responsive">
        <table class="table table-striped" id="eventsettingList">
            <thead>
                <tr>
                    <th >
                        <?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'COM_RA_EVENTBOOKING_EVENTSETTINGS_EVENT_ID', 'a.event_id', $listDirn, $listOrder); ?>
                    </th>
                    <th class=''>
                        <?php echo HTMLHelper::_('grid.sort', 'EVENT', 'a.event_data', $listDirn, $listOrder); ?>
                    </th>

                    <th class=''>Booking contact</th>

                    <?php if ($canEdit || $canDelete): ?>
                        <th class="center">
                            <?php echo Text::_('COM_RA_EVENTBOOKING_EVENTSETTINGS_ACTIONS'); ?>
                        </th>
                    <?php endif; ?>

                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
                        <div class="pagination">
                            <?php echo $this->pagination->getPagesLinks(); ?>
                        </div>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php if (empty($this->items)) : ?>
                    <?php
                    $noneMsg = 'No events found.';
                    if ($user->authorise('core.edit', 'com_ra_eventbooking') && (string) $this->state->get('filter.mine') !== 'all') {
                        $noneMsg .= ' You are only seeing your own events (where you are the booking contact, walk leader or creator). Change "Events shown" to "All events" in the filter options to see events for other contacts.';
                    } elseif (!empty($this->activeFilters) || (string) $this->state->get('filter.search') !== '') {
                        $noneMsg .= ' Try clearing the search or filters.';
                    }
                    ?>
                    <tr>
                        <td colspan="5"><div class="alert alert-info mb-0"><?php echo $this->escape($noneMsg); ?></div></td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php $canEdit = $user->authorise('core.edit', 'com_ra_eventbooking'); ?>
                    <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_ra_eventbooking')): ?>
                        <?php $canEdit = Factory::getApplication()->getIdentity()->id == $item->created_by; ?>
                    <?php endif; ?>

                    <?php
                    $unpublished = ($item->state != 1);
                    $tip = '';
                    if ($unpublished) {
                        [$nBook, $nPlaces, $nWait] = evbCounts($item);
                        $tip = 'Unpublished: the booking button is hidden. ';
                        if ($nBook + $nWait == 0) {
                            $tip .= 'No bookings have been made.';
                        } else {
                            $parts = [];
                            if ($nBook > 0) {
                                $parts[] = $nBook . ' booking' . ($nBook == 1 ? '' : 's') . ' (' . $nPlaces . ' place' . ($nPlaces == 1 ? '' : 's') . ')';
                            }
                            if ($nWait > 0) {
                                $parts[] = $nWait . ' on the waiting list';
                            }
                            $tip .= implode(' and ', $parts) . ' still stored until the event is deleted.';
                        }
                    }
                    ?>
                    <tr class="row<?php echo $i % 2; ?><?php echo $unpublished ? ' evb-unpublished' : ''; ?>"<?php echo $unpublished ? ' title="' . $this->escape($tip) . '"' : ''; ?>>

                        <td>
                            <?php $class = ($canChange) ? 'active' : 'disabled'; ?>
                            <a class="btn btn-micro <?php echo $class; ?>" title="<?php echo $item->state == 1 ? 'Published. Click to unpublish (hides the booking button, bookings are kept)' : 'Unpublished. Click to publish'; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_ra_eventbooking&task=eventsetting.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
                                <?php if ($item->state == 1): ?>
                                    <i class="icon-publish"></i>
                                <?php else: ?>
                                    <i class="icon-unpublish"></i>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td>
                            <?php
                            $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ra_eventbooking.' . $item->id) || $item->checked_out == Factory::getApplication()->getIdentity()->id;

                            if ($canCheckin && $item->checked_out > 0) {
                                echo ' <a href="' . Route::_('index.php?option=com_ra_eventbooking&task=eventsetting.checkin&id=' . $item->id . '&' . Session::getFormToken() . '=1') . '">';
                                echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'eventsetting.', false) . '</a>';
                            }
                            if ($canEdit) {
                                echo '<a href="' . Route::_('index.php?option=com_ra_eventbooking&task=eventsetting.edit&id=' . (int) $item->id) . '" title="Edit record">';
                                echo $this->escape($item->event_id) . '</a>';
                            } else {
                                echo $this->escape($item->event_id);
                            }
                            if ($unpublished) {
                                echo '<span class="evb-badge" title="' . $this->escape($tip) . '">Unpublished</span>';
                            }
                            ?>
                        </td>

                        <?php
                        $event = $item->event_data;
                        if ($event === "" OR $event === null) {
                            echo '<td>Not set</td>';
                        } else {
                            $data = \json_decode($event);
                            $out = $data->date;
                            $out .= '<br>' . $data->title;
                            echo '<td>' . $out . '</td>';
                        }
                        ?>


                        <td><?php echo $this->escape($contactNames[$item->contactId] ?? 'Not set'); ?></td>

                        <?php if ($canEdit || $canDelete): ?>
                            <td class="center">
                                <?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ra_eventbooking.' . $item->id) || $item->checked_out == Factory::getApplication()->getIdentity()->id; ?>

                                <?php if ($canEdit && $item->checked_out == 0): ?>
                                    <a href="<?php echo Route::_('index.php?option=com_ra_eventbooking&task=eventsetting.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="Edit record"><i class="icon-edit" ></i></a>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <a href="<?php echo Route::_('index.php?option=com_ra_eventbooking&task=eventsettingform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button" title="Delete record"><i class="icon-trash" ></i></a>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($canCreate) : ?>
        <a href="<?php echo Route::_('index.php?option=com_ra_eventbooking&task=eventsettingform.edit&id=0', false, 0); ?>"
           class="btn btn-success btn-small link-button mintcake"><i
                class="icon-plus"></i>
            <?php echo Text::_('COM_RA_EVENTBOOKING_ADD_ITEM'); ?></a>
    <?php endif; ?>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value=""/>
    <input type="hidden" name="filter_order_Dir" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php
if ($canDelete) {
    $wa->addInlineScript("
			jQuery(document).ready(function () {
				jQuery('.delete-button').click(deleteItem);
			});

			function deleteItem() {

				if (!confirm(\"" . Text::_('COM_RA_EVENTBOOKING_DELETE_MESSAGE') . "\")) {
					return false;
				}
			}
		", [], [], ["jquery"]);
}
?>