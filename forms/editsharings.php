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
class EditSharingsForm extends Form
{
    protected $sharing = null;

    /**
     * Construct a new poll form
     *
     * @param HTMLOutputter $out         output channel
     *
     * @return void
     */
    function __construct($out=null, $sharing)
    {
        parent::__construct($out);
        $this->sharing = $sharing;

        $kategori = new Sharing_category();

        $kategori->find();

        $this->kategori[0] = "Selecciona una categoría";

        while ($kategori->fetch()) {
            $this->kategori[$kategori->id] = $kategori->name;
        }

        $urbi = new Sharing_city();

        $urbi->orderBy('name ASC');

        $urbi->find();

        $this->urbi[0] = "Selecciona una ciudad";

        while ($urbi->fetch()) {
            $this->urbi[$urbi->id] = $urbi->name;
        }

        $tipi = new Sharing_type();

        $this->tipi[0] = "Selecciona Oferta o Demanda";

        $tipi->find();

        while ($tipi->fetch()) {
            $this->tipi[$tipi->id] = $tipi->name;
        }
    }

    /**
     * ID of the form
     *
     * @return int ID of the form
     */
    function id()
    {
        return 'newsharings-form';
    }

    /**
     * class of the form
     *
     * @return string class of the form
     */
    function formClass()
    {
        return 'form_settings ajax-notice';
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
        $this->out->elementStart('fieldset', array('id' => 'newsharings-data'));
        $this->out->elementStart('ul', 'form_data');

        $this->li();

        $this->dropdown('sharing_category_id', _('Categoría'),
                     // TRANS: Tooltip for dropdown list label in form for profile settings.
                        $this->kategori, _('Por favor, selecciona la categoría en la que quieres publicar'),
                        true, $this->sharing->sharing_category_id);

        $this->unli();

        $this->li();

        $this->dropdown('sharing_type_id', _('Tipo'),
                     // TRANS: Tooltip for dropdown list label in form for profile settings.
                        $this->tipi, _('Por favor, indica si estas publicando una oferta o una demanda'),
                        true, $this->sharing->sharing_type_id);

        $this->unli();

        $this->li();

        $this->out->input('displayName',
                          // TRANS: Field label on the page to create a poll.
                          _m('Nombre'),
                          $this->sharing->displayName,
                          // TRANS: Field title on the page to create a poll.
                          _m('Nombre del objeto o servicio a compartir'),
                          'displayName',
                          true);    // HTML5 "required" attribute
        $this->unli();

        $this->li();
        $this->out->textarea('summary',
                          // TRANS: Field label for an answer option on the page to create a poll.
                          // TRANS: %d is the option number.
                          _m('Detalle'),
                          $this->sharing->summary,
                          _m('Detalle del objeto o servicio que se quiere compartir'),
                          'summary',
                          true);   // HTML5 "required" attribute for 2 options
        $this->unli();

        $this->li();
        $this->out->input('price',
                          // TRANS: Field label for an answer option on the page to create a poll.
                          // TRANS: %d is the option number.
                          _m('Precio'),
                          $this->sharing->price,
                          _m('Indica el precio asociado a este producto o servicio. Asignale 0 - cero - en caso de querer compartirlo de forma gratuita'),
                          'price',
                          true);   // HTML5 "required" attribute for 2 options
        $this->unli();

        $this->li();

        $this->dropdown('sharing_city_id', _('Ciudad'),
                     // TRANS: Tooltip for dropdown list label in form for profile settings.
                        $this->urbi, _('Por favor, selecciona una ciudad'),
                        true, $this->sharing->sharing_city_id);

        $this->unli();

        $this->out->elementEnd('ul');

        $toWidget = new ToSelector($this->out,
                                   common_current_user(),
                                   null);
        $toWidget->show();

        $this->out->elementEnd('fieldset');
    }

    /**
     * Action elements
     *
     * @return void
     */
    function formActions()
    {
        // TRANS: Button text for saving a new poll.
        $this->out->submit('sharings-submit', _m('BUTTON', 'Guardar cambios'), 'submit', 'submit');
    }
}
