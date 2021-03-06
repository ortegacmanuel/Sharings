<?php
/**
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * Respond to a Poll
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
 * Respond to a Poll
 *
 * @category  Poll
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2010 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */
class RespondSharingsAction extends Action
{
    protected $user        = null;
    protected $error       = null;
    protected $complete    = null;

    protected $sharing        = null;

    /**
     * Returns the title of the action
     *
     * @return string Action title
     */
    function title()
    {
        // TRANS: Page title for poll response.
        return _m('Sharing response');
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
        
        if ($this->boolean('ajax')) {
            GNUsocial::setApi(true);
        }

        $this->user = common_current_user();

        if (empty($this->user)) {
            // TRANS: Client exception thrown trying to respond to a poll while not logged in.
            throw new ClientException(_m('You must be logged in to respond to a sharing.'),
                                      403);
        }

        if ($this->isPost()) {
            $this->checkSessionToken();
        }

        $id = $this->trimmed('id');
        $this->sharing = Sharing::getKV('id', $id);
        if (empty($this->sharing)) {
            // TRANS: Client exception thrown trying to respond to a non-existing poll.
            throw new ClientException(_m('Invalid or missing sharing.'), 404);
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
            $this->respondSharing();
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
    function respondSharing()
    {
        try {
            $notice = Sharing_response::saveNew($this->user->getProfile(),
                                             $this->sharing
                                             );
        } catch (ClientException $ce) {
            $this->error = $ce->getMessage();
            $this->showPage();
            return;
        }


        if ($this->boolean('ajax')) {
            $this->startHTML('text/xml;charset=utf-8');
            $this->elementStart('head');
            // TRANS: Page title after sending a poll response.
            $this->element('title', null, _m('Detalle del objeto o servicio'));
            $this->elementEnd('head');
            $this->elementStart('body');
            $form = new SharingsResultForm($this->sharing, $this);
            $form->show();
            $this->elementEnd('body');
            $this->endHTML();
        } else {
            $profile = $this->sharing->getNotice()->getProfile();
            common_redirect(common_local_url('newnotice', array('replyto' => $profile->id), array('inreplyto' => $this->sharing->getNotice()->id)), 303);
        }

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

        $form = new SharingsResponseForm($this->sharing, $this);

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
