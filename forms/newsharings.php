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
class NewSharingsForm extends Form
{
    protected $sharing_category_id = null;
    protected $sharing_type_id = null;
    protected $sharing_city_id = null;
    protected $displayName    = null;
    protected $summary     = null;
    protected $price    = null;

    protected $kategori = array();
    protected $urbi = array();
    protected $tipi = array();


    /**
     * Construct a new poll form
     *
     * @param HTMLOutputter $out         output channel
     *
     * @return void
     */
    function __construct($out=null, $displayName=null, $summary=null, $price=null, $sharing_category_id=null,
                         $sharing_type_id=null, $sharing_city_id=null)
    {
        parent::__construct($out);

        $this->displayName = $displayName;
        $this->summary = $summary;
        $this->price = $price;
        $this->sharing_category_id = $sharing_category_id;
        $this->sharing_type_id = $sharing_type_id;
        $this->sharing_city_id = $sharing_city_id;

        $kategori = new Sharing_category();

        $kategori->find();

        $this->kategori[0] = _m('Selecciona una categoría');

        while ($kategori->fetch()) {
            $this->kategori[$kategori->id] = _m(sprintf('%s', $kategori->name));
        }

        $urbi = new Sharing_city();

        $urbi->orderBy('name ASC');

        $urbi->find();

        $this->urbi[0] = _m('Selecciona una ciudad');

        while ($urbi->fetch()) {
            $this->urbi[$urbi->id] = $urbi->name;
        }

        $tipi = new Sharing_type();

        $this->tipi[0] = _m('Selecciona Oferta o Demanda');

        $tipi->find();

        while ($tipi->fetch()) {
            $this->tipi[$tipi->id] = _m(sprintf('%s', $tipi->name));
        }

        if (common_config('attachments', 'uploads')) {
            $this->enctype = 'multipart/form-data';
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
        return common_local_url('newsharings');
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

        $this->dropdown('sharing_category_id', _m('Categoría'),
                     // TRANS: Tooltip for dropdown list label in form for profile settings.
                        $this->kategori, _m('Por favor, selecciona la categoría en la que quieres publicar'),
                        true, 0);

        $this->unli();

        $this->li();

        $this->dropdown('sharing_type_id', _m('Tipo'),
                     // TRANS: Tooltip for dropdown list label in form for profile settings.
                        $this->tipi, _m('Por favor, indica si estas publicando una oferta o una demanda'),
                        true, 0);

        $this->unli();

        $this->li();

        $this->out->input('displayName',
                          // TRANS: Field label on the page to create a poll.
                          _m('Nombre'),
                          $this->displayName,
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
                          $this->summary,
                          _m('Detalle del objeto o servicio que se quiere compartir'),
                          'summary',
                          true);   // HTML5 "required" attribute for 2 options
        $this->unli();

        $this->li();
        $this->out->input('price',
                          // TRANS: Field label for an answer option on the page to create a poll.
                          // TRANS: %d is the option number.
                          _m('Precio'),
                          $this->price,
                          _m('Indica el precio asociado a este producto o servicio. Asignale 0 - cero - en caso de querer compartirlo de forma gratuita'),
                          'price',
                          true);   // HTML5 "required" attribute for 2 options
        $this->unli();

        $this->li();

        $this->dropdown('sharing_city_id', _m('Ciudad'),
                     // TRANS: Tooltip for dropdown list label in form for profile settings.
                        $this->urbi, _m('Por favor, selecciona una ciudad. Si tu ciudad no está en el listado puedes no indicar la ciudad ahora, agregar el objeto o servicio y pedir añadir tu ciudad en http://git.lasindias.club/manuel/Sharings/issues'),
                        true, 0);

        $this->unli();

        $this->li();

        if (common_config('attachments', 'uploads')) {
            $this->out->hidden('MAX_FILE_SIZE', common_config('attachments', 'file_quota'));
            $this->out->element('label', array('class' => 'notice_data-attach',
                                               'for'   => $this->id().'-notice_data-attach'),
                                // TRANS: Input label in notice form for adding an attachment.
                                _('Attach'));
            // The actual input element tends to be hidden with CSS.
            $this->out->element('input', array('class' => 'notice_data-attach',
                                               'type' => 'file',
                                               'name' => 'attach',
                                               'id' => $this->id().'-notice_data-attach',
                                               // TRANS: Title for input field to attach a file to a notice.
                                               'title' => _('Attach a file.')));
        }

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
        $this->out->submit('sharings-submit', _m('BUTTON', 'Compartir'), 'submit', 'submit');
    }
}
