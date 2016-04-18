<?php
/**
 * Data class to record responses to polls
 *
 * PHP version 5
 *
 * @category PollPlugin
 * @package  StatusNet
 * @author   Brion Vibber <brion@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 *
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('STATUSNET')) {
    exit(1);
}

/**
 * For storing the poll options and such
 *
 * @category PollPlugin
 * @package  StatusNet
 * @author   Brion Vibber <brion@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 *
 * @see      DB_DataObject
 */
class Sharing_response extends Managed_DataObject
{
    public $__table = 'sharing_response'; // table name
    public $id;          // char(36) primary key not null -> UUID
    public $uri;         // varchar(191)   not 255 because utf8mb4 takes more space
    public $sharing_id;     // char(36) -> poll.id UUID
    public $profile_id;  // int -> profile.id
    public $created;     // datetime

    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
        return array(
            'description' => 'Record of responses to polls',
            'fields' => array(
                'id' => array('type' => 'char', 'length' => 36, 'not null' => true, 'description' => 'UUID of the response'),
                'uri' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'UUID to the response notice'),
                'sharing_id' => array('type' => 'char', 'length' => 36, 'not null' => true, 'description' => 'UUID of poll being responded to'),
                'profile_id' => array('type' => 'int'),
                'created' => array('type' => 'datetime', 'not null' => true),
            ),
            'primary key' => array('id'),
            'indexes' => array(
                'sharing_response_profile_id_sharing_id_index' => array('profile_id', 'sharing_id'),
            )
        );
    }

    /**
     * Get a poll response based on a notice
     *
     * @param Notice $notice Notice to check for
     *
     * @return Poll_response found response or null
     */
    static function getByNotice($notice)
    {
        return self::getKV('uri', $notice->uri);
    }

    /**
     * Get the notice that belongs to this response...
     *
     * @return Notice
     */
    function getNotice()
    {
        return Notice::getKV('uri', $this->uri);
    }

    function getUrl()
    {
        return $this->getNotice()->getUrl();
    }

    /**
     *
     * @return Poll
     */
    function getSharing()
    {
        return Sharing::getKV('id', $this->sharing_id);
    }
    /**
     * Save a new poll notice
     *
     * @param Profile $profile
     * @param Poll    $poll the poll being responded to
     * @param int     $selection (1-based)
     * @param array   $opts (poll responses)
     *
     * @return Notice saved notice
     */
    static function saveNew($profile, $sharing, $options=null)
    {

        if (empty($options)) {
            $options = array();
        }

        $sr = new Sharing_response();
        $sr->id          = UUID::gen();
        $sr->profile_id  = $profile->id;
        $sr->sharing_id  = $sharing->id;

        if (array_key_exists('created', $options)) {
            $sr->created = $options['created'];
        } else {
            $sr->created = common_sql_now();
        }

        if (array_key_exists('uri', $options)) {
            $sr->uri = $options['uri'];
        } else {
            $sr->uri = common_local_url('showsharingsresponse',
                                        array('id' => $sr->id));
        }

        common_log(LOG_DEBUG, "Saving sharing response: $sr->id $sr->uri");
        $sr->insert();

        // TRANS: Notice content voting for a poll.
        // TRANS: %s is the chosen option in the poll.
        $content  = sprintf(_m('le interesa "%s"'),
                            $sharing->displayName);
        $link = '<a href="' . htmlspecialchars($sharing->uri) . '">' . htmlspecialchars($sharing->displayName) . '</a>';
        // TRANS: Rendered version of the notice content voting for a poll.
        // TRANS: %s a link to the poll with the chosen option as link description.
        $rendered = sprintf(_m('le interesa "%s"'), $link);

        $tags    = array();

        $options = array_merge(array('urls' => array(),
                                     'rendered' => $rendered,
                                     'tags' => $tags,
                                     'reply_to' => $sharing->getNotice()->id,
                                     'object_type' => SharingsPlugin::SHARINGS_RESPONSE_OBJECT),
                               $options);

        if (!array_key_exists('uri', $options)) {
            $options['uri'] = $sr->uri;
        }

        $saved = Notice::saveNew($profile->id,
                                 $content,
                                 array_key_exists('source', $options) ?
                                 $options['source'] : 'web',
                                 $options);

        return $saved;
    }
}
