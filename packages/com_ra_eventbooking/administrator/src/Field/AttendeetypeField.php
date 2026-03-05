<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Ra_eventbooking
 * @author     Chris Vaughan  <ruby.tuesday@ramblers-webs.org.uk>
 * @copyright  2025 Ruby Tuesday
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_eventbooking\Administrator\Field;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\ListField;
/**
 * Class SubmitField
 *
 * @since  1.0.0
 */


class AttendeetypeField extends ListField {

    protected $type = 'attendeetype';

    /**
     * Build the options for the select list.
     */
    protected function getOptions(): array {
        $options = [];

        $options[] = (object) ['value' => '1', 'text' => 'Members Only'];
        $options[] = (object) ['value' => '2', 'text' => 'Non members welcome'];

        // Merge with any <option> tags from XML
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
