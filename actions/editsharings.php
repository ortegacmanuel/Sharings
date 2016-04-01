<?php
/**
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * Add a new Poll
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
 * @category  Poll
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
 * Add a new Poll
 *
 * @category  Poll
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2010 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */
class EditSharingsAction extends Action
{
    protected $user        = null;
    protected $error       = null;
    protected $complete    = null;

    protected $displayName    = null;
    protected $sharing_id    = null;
    protected $summary     = array();

    /**
     * Returns the title of the action
     *
     * @return string Action title
     */
    function title()
    {
        // TRANS: Title for poll page.
        return _m('Editar un objeto o servicio');
    }

    /**
     * For initializing members of the class.
     *
     * @param array $argarray misc. arguments
     *
     * @return boolean true
     */
    function prepare($argarray)
    {
        parent::prepare($argarray);

        $this->user = common_current_user();

        if (empty($this->user)) {
            // TRANS: Client exception thrown trying to create a poll while not logged in.
            throw new ClientException(_m('You must be logged in to post a poll.'),
                                      403);
        }

        if ($this->isPost()) {
            $this->checkSessionToken();
        }

        $this->sharing_category_id = $this->trimmed('sharing_category_id');
        $this->sharing_type_id = $this->trimmed('sharing_type_id');
        $this->displayName = $this->trimmed('displayName');
        $this->summary = $this->trimmed('summary');
        $this->price = $this->trimmed('price');
        $this->sharing_city_id = $this->trimmed('sharing_city_id');

        $id = $this->trimmed('id');
        $this->sharing = Sharing::getKV('id', $id);
        if (empty($this->sharing)) {
            // TRANS: Client exception thrown trying to respond to a non-existing poll.
            throw new ClientException(_m('Invalid or missing poll.'), 404);
        }

        return true;
    }

    /**
     * Handler method
     *
     * @param array $argarray is ignored since it's now passed in in prepare()
     *
     * @return void
     */
    function handle($argarray=null)
    {
        parent::handle($argarray);

        if ($this->isPost()) {
            $this->editSharings();
        } else {
            $this->showPage();
        }

        return;
    }

    /**
     * Add a new Poll
     *
     * @return void
     */
    function editSharings()
    {
        if ($this->boolean('ajax')) {
            GNUsocial::setApi(true);
        }
        try {
            if (empty($this->displayName)) {
            // TRANS: Client exception thrown trying to create a poll without a question.
                throw new ClientException(_m('El objeto o servicio a compartir tiene que tener un nombre.'));
            }

            if (empty($this->summary)) {
                // TRANS: Client exception thrown trying to create a poll with fewer than two options.
                throw new ClientException(_m('Tiene que indicar información detallada sobre el objeto o servicio que quiere compartir.'));
            }

            if ($this->sharing_category_id == 0) {
            // TRANS: Client exception thrown trying to create a poll without a question.
                throw new ClientException(_m('Tiene que seleccionar una categoría.'));
            }

            if ($this->sharing_type_id == 0) {
            // TRANS: Client exception thrown trying to create a poll without a question.
                throw new ClientException(_m('Tiene que seleccionar Oferta o Demanda.'));
            }

            // Desabilitado hasta ver como evoluciona la gestión y actualización de ciudades

            //if ($this->sharing_city_id == 0) {
            // TRANS: Client exception thrown trying to create a poll without a question.
            //    throw new ClientException(_m('Tiene que seleccionar una cuidad.'));
            //}

            if (empty($this->price)) {
                if($this->price != 0){
                // TRANS: Client exception thrown trying to create a poll without a question.
                    throw new ClientException(_m('Tiene que indicar el precio para este producto o bien asinarle precio 0 - cero - para compartilo gratuitamente.'));
                }
            }

            $options['verb'] = ActivityVerb::UPDATE;

            $options['displayName'] = $this->displayName;
            $options['summary'] = $this->summary;
            $options['price'] = $this->price;
            $options['sharing_category_id'] = $this->sharing_category_id;
            $options['sharing_type_id'] = $this->sharing_type_id;
            $options['sharing_city_id'] = $this->sharing_city_id;

            $notice = Sharing_notice::saveNew($this->user->getProfile(),
                                             $this->sharing,
                                             $options);
            

        } catch (ClientException $ce) {
            $this->error = $ce->getMessage();
            $this->showPage();
            return;
        }

        if ($this->boolean('ajax')) {
            $this->startHTML('text/xml;charset=utf-8');
            $this->elementStart('head');
            // TRANS: Page title after sending a notice.
            $this->element('title', null, _m('Notice posted'));
            $this->elementEnd('head');
            $this->elementStart('body');
            $this->showNotice($saved);
            $this->elementEnd('body');
            $this->endHTML();
        } else {
            common_redirect($this->sharing->getUrl(), 303);
        }
    }

    /**
     * Output a notice
     *
     * Used to generate the notice code for Ajax results.
     *
     * @param Notice $notice Notice that was saved
     *
     * @return void
     */
    function showNotice(Notice $notice)
    {
        class_exists('NoticeList'); // @fixme hack for autoloader
        $nli = new NoticeListItem($notice, $this);
        $nli->show();
    }

    /**
     * Show the Poll form
     *
     * @return void
     */
    function showContent()
    {
        if (!empty($this->error)) {
            $this->element('p', 'error', $this->error);
        }

        $form = new EditSharingsForm($this,
                                 $this->sharing);

        $form->show();

        return;
    }

    /**
     * Return true if read only.
     *
     * MAY override
     *
     * @param array $args other arguments
     *
     * @return boolean is read only action?
     */
    function isReadOnly($args)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET' ||
            $_SERVER['REQUEST_METHOD'] == 'HEAD') {
            return true;
        } else {
            return false;
        }
    }
}
