<?php
/**
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2010, StatusNet, Inc.
 *
 * An item in a notice list
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
 * @category  Widget
 * @package   StatusNet
 * @author    Evan Prodromou <evan@status.net>
 * @copyright 2010 StatusNet, Inc.
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html AGPL 3.0
 * @link      http://status.net/
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * widget for displaying a single notice
 *
 * This widget has the core smarts for showing a single notice: what to display,
 * where, and under which circumstances. Its key method is show(); this is a recipe
 * that calls all the other show*() methods to build up a single notice. The
 * ProfileNoticeListItem subclass, for example, overrides showAuthor() to skip
 * author info (since that's implicit by the data in the page).
 *
 * @category UI
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link     http://status.net/
 * @see      NoticeList
 * @see      ProfileNoticeListItem
 */
class SharingsListItem extends Widget
{
    /** The notice this item will show. */
    var $notice = null;

    /** The notice that was repeated. */
    var $repeat = null;

    /** The profile of the author of the notice, extracted once for convenience. */
    var $profile = null;

    protected $addressees = true;
    protected $attachments = true;
    protected $id_prefix = null;
    protected $options = true;
    protected $maxchars = 0;   // if <= 0 it means use full posts
    protected $item_tag = 'li';
    protected $pa = null;

    /**
     * constructor
     *
     * Also initializes the profile attribute.
     *
     * @param Notice $notice The notice we'll display
     */
    function __construct(Sharing $notice, Action $out=null, array $prefs=array())
    {
        parent::__construct($out);

        $this->notice = $notice;
        
        // integer preferences
        foreach(array('maxchars') as $key) {
            if (array_key_exists($key, $prefs)) {
                $this->$key = (int)$prefs[$key];
            }
        }
        // boolean preferences
        foreach(array('addressees', 'attachments', 'options') as $key) {
            if (array_key_exists($key, $prefs)) {
                $this->$key = (bool)$prefs[$key];
            }
        }
        // string preferences
        foreach(array('id_prefix', 'item_tag') as $key) {
            if (array_key_exists($key, $prefs)) {
                $this->$key = $prefs[$key];
            }
        }
    }

    /**
     * recipe function for displaying a single notice.
     *
     * This uses all the other methods to correctly display a notice. Override
     * it or one of the others to fine-tune the output.
     *
     * @return void
     */
    function show()
    {
        if (empty($this->notice)) {
            common_log(LOG_WARNING, "Trying to show missing sharing; skipping.");
            return;
        }

        $this->showStart();
        $this->showNotice();
        $this->showEnd();

    }

    /**
     * start a single notice.
     *
     * @return void
     */
    function showStart()
    {

        $id = $this->notice->id;
        $class = 'h-entry notice sharing';

        $id_prefix = (strlen($this->id_prefix) ? $this->id_prefix . '-' : '');
        $this->out->elementStart($this->item_tag, array('class' => $class,
                                             'id' => "${id_prefix}sharing-${id}"));
    
    }

    function showNotice()
    {

        //$this->showNoticeHeaders();
        // FIXME: URL, image, video, audio

        $this->out->elementStart('a', array('class' => 'h-card p-author'));
        $this->showAvatar($this->notice->getNotice()->getProfile());
        $this->out->elementEnd('a');

        $this->out->elementStart('article', array('class' => 'e-content'));

        $sharing = $this->notice;
        $out = $this->out;
    
        $form = new SharingsDirectoryForm($sharing, $out);
        $form->show();


        $this->out->elementEnd('article');
    }


    /**
     * finish the notice
     *
     * Close the last elements in the notice list item
     *
     * @return void
     */
    function showEnd()
    {
        $this->out->elementEnd('li');
    }

}
