<?php
/**
 * StatusNet, the distributed open-source microblogging tool
 *
 * Output a user directory
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  Public
 * @package   StatusNet
 * @author    Zach Copley <zach@status.net>
 * @copyright 2011 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://status.net/
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * User directory
 *
 * @category Personal
 * @package  StatusNet
 * @author   Zach Copley <zach@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 */
class SharingsdirectoryAction extends ManagedAction
{
    /**
     * The page we're on
     *
     * @var integer
     */
    public $page;

    /**
     * What to filter the search results by
     *
     * @var string
     */
    public $filter;

    /**
     * Column to sort by
     *
     * @var string
     */
    public $sort;

    /**
     * How to order search results, ascending or descending
     *
     * @var string
     */
    public $reverse;

    /**
     * Query
     *
     * @var string
     */
    public $pc;

    /**
     * Query
     *
     * @var string
     */
    public $sharing_category_id;

    /**
     * Query
     *
     * @var string
     */
    public $gratuito;

    /**
     * Categorías
     *
     * @var string
     */
    public $kategori;

    /**
     * Ciudades
     *
     * @var string
     */
    public $urbi;

    /**
     * Tipos
     *
     * @var string
     */
    public $tipi;

    /**
     * Title of the page
     *
     * @return string Title of the page
     */
    function title()
    {

        return _m('Catálogo');
    }

    /**
     * Instructions for use
     *
     * @return instructions for use
     */
    function getInstructions()
    {
        // TRANS: %%site.name%% is the name of the StatusNet site.
        return _m('Busca objetos y servicios por categorías, ciudades y una palabra clave. También puedes indicar si estás buscando una oferta o una demanda.');
    }

    /**
     * Is this page read-only?
     *
     * @return boolean true
     */
    function isReadOnly($args)
    {
        return true;
    }

    protected function doPreparation()
    {
        $this->page    = ($this->arg('page')) ? ($this->arg('page') + 0) : 1;

        $this->pc = $this->trimmed('pc');
        $this->sharing_category_id = $this->trimmed('sharing_category_id');
        $this->sharing_city_id = $this->trimmed('sharing_city_id');
        $this->sharing_type_id = $this->trimmed('sharing_type_id');
        $this->gratuito = $this->trimmed('gratuito');

        $kategori = new Sharing_category();

        $kategori->find();

        $this->kategori[0] = _m('Todas');

        while ($kategori->fetch()) {
            $this->kategori[$kategori->id] = _m(sprintf('%s', $kategori->name));
        }

        $urbi = new Sharing_city();

        $urbi->orderBy('name ASC');

        $urbi->find();

        $this->urbi[0] = _m('Todas');

        while ($urbi->fetch()) {
            $this->urbi[$urbi->id] = $urbi->name;
        }

        $tipi = new Sharing_type();

        $this->tipi[0] = _m('Todos');

        $tipi->find();

        while ($tipi->fetch()) {
            $this->tipi[$tipi->id] = _m(sprintf('%s', $tipi->name));
        }


        common_set_returnto($this->selfUrl());
    }

    /**
     * Show the page notice
     *
     * Shows instructions for the page
     *
     * @return void
     */
    function showPageNotice()
    {
        $instr  = $this->getInstructions();
        $output = common_markup_to_html($instr);

        $this->elementStart('div', 'instructions');
        $this->raw($output);
        $this->elementEnd('div');
    }


    /**
     * Content area
     *
     * Shows the list of popular notices
     *
     * @return void
     */
    function showContent()
    {
        $this->showForm();

        $this->elementStart('div', array('id' => 'notices_primary'));


        $sharing = null;
        $sharing = $this->getSharings();
        $cnt     = 0;

        if (!empty($sharing)) {
            $profileList = new SharingsList(
                $sharing,
                $this
            );

            $cnt = $profileList->show();
            $sharing->free();

            if (0 == $cnt) {
                $this->showEmptyListMessage();
            }
        }

        $this->elementEnd('div');

    }

    function showForm($error=null)
    {
        $this->elementStart(
            'form',
            array(
                'method' => 'get',
                'id'     => 'form_search',
                'class'  => 'form_settings',
                'action' => common_local_url('sharingsdirectory')
            )
        );

        $this->elementStart('fieldset');

        // TRANS: Fieldset legend.
        $this->element('legend', null, _m('Search site'));
        $this->elementStart('ul', 'form_data');
        $this->elementStart('li');

        // TRANS: Field label for user directory filter.
        $this->input('pc', _m('Palabra clave'), $this->pc);

        // TRANS: Button text.
        $this->submit('search', _m('BUTTON','Buscar'));
        $this->elementEnd('li');

        $this->elementStart('li');

        $this->dropdown('sharing_category_id', _m('Categoría'),
                     // TRANS: Tooltip for dropdown list label in form for profile settings.
                        $this->kategori, _('Categoría'),
                        true, $this->sharing_category_id);

        $this->elementEnd('li');

        $this->elementStart('li');

        $this->dropdown('sharing_type_id', _m('Tipo'),
                     // TRANS: Tooltip for dropdown list label in form for profile settings.
                        $this->tipi, _('Tipo'),
                        true, $this->sharing_type_id);

        $this->elementEnd('li');

        $this->elementStart('li');

        $this->dropdown('sharing_city_id', _m('Ciudad'),
                     // TRANS: Tooltip for dropdown list label in form for profile settings.
                        $this->urbi, _('Ciudad'),
                        true, $this->sharing_city_id);

        $this->elementEnd('li');

        $this->elementStart('li');

        $this->checkbox('gratuito',
                        _m('Mostrar sólo los intercambios de forma gratuita'),
                        $this->gratuito);

        $this->elementEnd('li');

        $this->elementEnd('ul');
        $this->elementEnd('fieldset');
        $this->elementEnd('form');
    }

    /*
     * Get users filtered by the current filter, sort key,
     * sort order, and page
     */
    function getSharings()
    {
        $sharing = new Sharing();

        $sharing->whereAdd(sprintf('profile_id != %d', common_current_user()->getProfile()->id));

        $sharing->orderBy('created DESC');

        if(!empty($this->pc)) {
            $sharing->whereAdd(sprintf('(lower(displayName) LIKE "%%%s%%" OR lower(summary) LIKE "%%%s%%")', strtolower($this->pc), strtolower($this->pc)));
        }

        if($this->sharing_category_id != 0) {
            $sharing->whereAdd(sprintf('sharing_category_id = %d', $this->sharing_category_id));
        }

        if($this->sharing_city_id != 0) {
            $sharing->whereAdd(sprintf('sharing_city_id = %d', $this->sharing_city_id));
        }

        if($this->sharing_type_id != 0) {
            $sharing->whereAdd(sprintf('sharing_type_id = %d', $this->sharing_type_id));
        }

        if($this->gratuito == true) {
            $sharing->whereAdd('price = 0');
        }

        $offset = ($this->page - 1) * PROFILES_PER_PAGE;
        $limit  = PROFILES_PER_PAGE + 1;
        
        $sharing->find();

        return $sharing;
    }

    /**
     * Filter the sort parameter
     *
     * @return string   a column name for sorting
     */
    function getSortKey($def='nickname')
    {
        switch ($this->sort) {
        case 'nickname':
        case 'created':
            return $this->sort;
        default:
            return 'nickname';
        }
    }

    /**
     * Show a nice message when there's no search results
     */
    function showEmptyListMessage()
    {
        // TRANS: Empty list message for user directory.
        $this->element('p', 'error', _m('Sin resultados.'));
        // TRANS: Standard search suggestions shown when a search does not give any results.
        $message = _m("* Vuelve a intentarlo cambiando los criterios de búsqueda.");
        $message .= "\n";

        $this->elementStart('div', 'help instructions');
        $this->raw(common_markup_to_html($message));
        $this->elementEnd('div');
    
    }
}
