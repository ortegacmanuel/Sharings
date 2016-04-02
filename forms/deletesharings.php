<?php
/**
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * Form for adding a new poll
 *
 * PHP version 5
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  PollPlugin
 * @package   StatusNet
 * @author    Brion Vibber <brion@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/**
 * Form to add a new poll thingy
 *
 * @category  PollPlugin
 * @package   StatusNet
 * @author    Brion Vibber <brion@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */
class DeleteSharingsForm extends Form
{
    protected $sharing;

    /**
     * Construct a new poll form
     *
     * @param Poll $poll
     * @param HTMLOutputter $out         output channel
     *
     * @return void
     */
    function __construct(Sharing $sharing, HTMLOutputter $out)
    {
        parent::__construct($out);
        $this->sharing = $sharing;
    }

    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        return 'pollresponse-form';
    }

    /**
     * class of the form
     *
     * @return string class of the form
     */
    function formClass()
    {
        return 'form_settings';
    }

    /**
     * Action of the form
     *
     * @return string URL of the action
     */
    function action()
    {
        return common_local_url('deletesharings', array('id' => $this->sharing->id));
    }

    /**
     * Data elements of the form
     *
     * @return void
     */
    function formData()
    {
        $sharing = $this->sharing;
        $out = $this->out;
        $id = "sharing-" . $sharing->id;
        
        $out->element('h3', 'sharing-title', _m('¿Estás seguro que quieres eliminar este objeto/servicio?'));
        $out->element('p', 'sharing-displayName', sprintf(_m('Nombre: %s'), $sharing->displayName));
        $out->element('p', 'sharing-summary', sprintf(_('Detalle: %s'), $sharing->summary));

        $this->out->element('a',
            array('href' => $sharing->uri, 'class' => 'sharings-edit-submit'),
            // TRANS: Link description for link to list of users subscribed to a tag.
            _m('Cancelar'));

    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {
        // TRANS: Button text for saving a new poll.
        $this->out->submit(_m('BUTTON', 'Confirmar'), _m('BUTTON', 'Confirmar'), 'sharings-edit-submit');
    }
}
