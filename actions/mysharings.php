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
class MySharingsAction extends ManagedAction
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

        return _m('Mi Catálogo');
    }

    /**
     * Instructions for use
     *
     * @return instructions for use
     */
    function getInstructions()
    {
        // TRANS: %%site.name%% is the name of the StatusNet site.
        return _m('Listado de mis objetos y servicios compartidos en la red.');
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

    /*
     * Get users filtered by the current filter, sort key,
     * sort order, and page
     */
    function getSharings()
    {
        $sharing = new Sharing();

        $sharing->whereAdd(sprintf('profile_id = %d', common_current_user()->getProfile()->id));

        $sharing->orderBy('created DESC');

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
        $this->element('p', 'error', _m('No has compartido ningún objeto o servicio en la red.'));
        // TRANS: Standard search suggestions shown when a search does not give any results.
        $message = _m("Compartiendo obtendras la medala «Mumi»");
        $message .= "\n";

        $this->elementStart('div', 'help instructions');
        $this->raw(common_markup_to_html($message));
        $this->elementEnd('div');
    
    }
}
