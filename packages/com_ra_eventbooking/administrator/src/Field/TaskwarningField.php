<?php

/**
 * @package    Com_Ra_eventbooking
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_eventbooking\Administrator\Field;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\Form\FormField;
use \Ramblers\Component\Ra_eventbooking\Site\Helper\Ra_eventbookingHelper as helper;

/**
 * Shows a red warning only if the scheduled task that emails the final
 * booking list is not enabled. Shows nothing when the task is enabled.
 */
class TaskwarningField extends FormField {

    protected $type = 'taskwarning';

    #[\Override]
    protected function getInput() {
        return '<div class="alert alert-danger"><strong>Scheduled task not enabled.</strong> '
                . 'Final booking lists will NOT be sent until the Event Booking email task is enabled in System - Scheduled Tasks, see Users Guide for details.</div>';
    }

    #[\Override]
    protected function getLabel() {
        return '';
    }

    #[\Override]
    public function renderField($options = []) {
        if (helper::isClosingTaskEnabled()) {
            return '';
        }
        $options['hiddenLabel'] = true;
        return parent::renderField($options);
    }
}
