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
class MySharingsForm extends Form
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
        return common_local_url('editsharings', array('id' => $this->sharing->id));
    }

    /**
     * Data elements of the form
     *
     * @return void
     */
    function formData()
    {
        $sharing = $this->sharing;
        $counts = $sharing->countResponses();
        $out = $this->out;
        $id = "poll-" . $sharing->id;
        
        $out->element('h3', 'sharing-title', _m('He compartido'));
        $out->element('p', 'sharing-category', sprintf(_m('Categoría: %s'), Sharing_category::getNameById($sharing->sharing_category_id)));
        $out->element('p', 'sharing-type', sprintf(_m('Tipo: %s'), Sharing_type::getNameById($sharing->sharing_type_id)));
        $out->element('p', 'sharing-displayName', sprintf(_m('Nombre: %s'), $sharing->displayName));
        $out->element('p', 'sharing-summary', sprintf(_m('Detalle: %s'), $sharing->summary));
        $out->element('p', 'sharing-price', $sharing->getPriceText());
        $out->element('p', 'sharing-city', sprintf(_m('En %s'), Sharing_city::getNameById($sharing->sharing_city_id)));

        $out->element('br');

        $out->element('p', 'sharings-summary', sprintf(_m('Tu objeto / servicio le interesa a %d usuarios'), $counts));

        $out->element('br');

        $this->out->element('a',
            array('href' => common_local_url('editsharings', array('id' => $sharing->id)), 'class' => 'sharings-edit-submit'),
            // TRANS: Link description for link to list of users subscribed to a tag.
            _m('Editar'));

        $this->out->element('a',
            array('href' => common_local_url('deletesharings', array('id' => $sharing->id)), 'class' => 'sharings-edit-submit'),
            // TRANS: Link description for link to list of users subscribed to a tag.
            _m('Dar de baja'));
    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {

    }
}
