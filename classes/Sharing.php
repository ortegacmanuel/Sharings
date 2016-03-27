<?php
/**
 * Data class to mark notices as bookmarks
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

class Sharing extends Managed_DataObject
{
    public $__table = 'sharing'; // table name
    public $id;          // char(36) primary key not null -> UUID
    public $uri;         // varchar(191)   not 255 because utf8mb4 takes more space
    public $profile_id;  // int -> profile.id
    public $displayName;    // text
    public $summary;    // text
    public $sharing_category_id;  // int -> sharing_category.id
    public $sharing_type_id;  // int -> sharing_category.id
    public $sharing_city_id;  // int -> sharing_category.id
    public $price;
    public $created;     // datetime
    public $updated;     // datetime

    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
        return array(
            'description' => 'Per-notice sharing data for Sharings plugin',
            'fields' => array(
                'id' => array('type' => 'char', 'length' => 36, 'not null' => true, 'description' => 'UUID'),
                'uri' => array('type' => 'varchar', 'length' => 191, 'not null' => true),
                'profile_id' => array('type' => 'int'),
                'displayName' => array('type' => 'text'),
                'summary' => array('type' => 'text'),
                'sharing_category_id' => array('type' => 'int', 'not null' => true, 'description' => 'Sharing category', 'default' => 1),
                'sharing_city_id' => array('type' => 'int', 'not null' => true, 'description' => 'Sharing subcategory'),
                'sharing_type_id' => array('type' => 'int', 'not null' => true, 'description' => 'Sharing type', 'default' => 1),
                'price' => array('type' => 'int', 'not null' => true),
                'created' => array('type' => 'datetime', 'not null' => true),
                'updated' => array('type' => 'datetime', 'not null' => true),
            ),
            'primary key' => array('id'),
            'unique keys' => array(
                'sharing_uri_key' => array('uri'),
            ),
            'foreign keys' => array(
                'sharing_profile_id_fkey' => array('profile', array('profile_id' => 'id')),
                'sharing_sharing_category_id_fkey' => array('sharing_category', array('sharing_category_id' => 'id')),
                'sharing_sharing_city_id_fkey' => array('sharing_city', array('sharing_city' => 'id')),
                'sharing_sharing_type_id_fkey' => array('sharing_type', array('sharing_type_id' => 'id')),
            ),
        );
    }

    /**
     * Get a bookmark based on a notice
     *
     * @param Notice $notice Notice to check for
     *
     * @return Poll found poll or null
     */
    static function getByNotice($notice)
    {
        return self::getKV('uri', $notice->uri);
    }

    function getOptions()
    {
        return explode("\n", $this->options);
    }

    /**
     * Is this a valid selection index?
     *
     * @param numeric $selection (1-based)
     * @return boolean
     */
    function isValidSelection($selection)
    {
        if ($selection != intval($selection)) {
            return false;
        }
        if ($selection < 1 || $selection > count($this->getOptions())) {
            return false;
        }
        return true;
    }

    function getNotice()
    {
        return Notice::getKV('uri', $this->uri);
    }

    function getUrl()
    {
        return $this->getNotice()->getUrl();
    }

    function getPriceText()
    {
        if ($this->price == 0) {
            return 'de forma gratuita';
        } else {
            return 'por ' . $this->price . ' unidades (consultar moneda con el propietario de este objeto o servicio)';
        }
    }

    /**
     * Get the response of a particular user to this poll, if any.
     *
     * @param Profile $profile
     * @return Poll_response object or null
     */
    function getResponse(Profile $profile)
    {
    	$pr = Sharing_response::pkeyGet(array('sharing_id' => $this->id,
    									   'profile_id' => $profile->id));
    	return $pr;
    }

    function countResponses()
    {
        $sr = new Sharing_response();
        $sr->sharing_id = $this->id;
        $counts = $sr->count();

        return $counts;
    }

    /**
     * Save a new poll notice
     *
     * @param Profile $profile
     * @param string  $question
     * @param array   $opts (poll responses)
     *
     * @return Notice saved notice
     */
    static function saveNew($profile, $options=null)
    {

        $s = new Sharing();

        $s->id          = UUID::gen();
        $s->profile_id  = $profile->id;
        $s->displayName = $options['displayName'];
        $s->summary     = $options['summary'];
        $s->price       = $options['price'];
        $s->sharing_category_id = $options['sharing_category_id'];
        $s->sharing_type_id = $options['sharing_type_id'];
        $s->sharing_city_id = $options['sharing_city_id'];

        if (array_key_exists('created', $options)) {
            $s->created = $options['created'];
        } else {
            $s->created = common_sql_now();
        }

        if (array_key_exists('uri', $options)) {
            $s->uri = $options['uri'];
        } else {
            $s->uri = common_local_url('showsharings',
                                        array('id' => $s->id));
        }

        common_log(LOG_DEBUG, "Saving sharings: $s->id $s->uri");

        $s->insert();

        // TRANS: Notice content creating a poll.
        // TRANS: %1$s is the poll question, %2$s is a link to the poll.
        $content  = sprintf(_m('Objeto/Servicio: %1$s %2$s'),
                            $s->displayName,
                            $s->uri);
        $link = '<a href="' . htmlspecialchars($s->uri) . '">' . htmlspecialchars($s->displayName) . '</a>';
        // TRANS: Rendered version of the notice content creating a poll.
        // TRANS: %s is a link to the poll with the question as link description.
        $rendered = sprintf(_m('He compartido un nuevo objeto/servicio en %s: %s. %s %s dentro de la categoría %s'), Sharing_city::getNameById($s->sharing_city_id), $link, Sharing_type::getNameById($s->sharing_type_id), $s->getPriceText(), Sharing_category::getNameById($s->sharing_category_id));

        $tags    = array('sharings');
        $replies = array();

        $options = array_merge(array('urls' => array(),
                                     'rendered' => $rendered,
                                     'tags' => $tags,
                                     'replies' => $replies,
                                     'object_type' => SharingsPlugin::SHARINGS_OBJECT),
                               $options);

        if (!array_key_exists('uri', $options)) {
            $options['uri'] = $s->uri;
        }

        $saved = Notice::saveNew($profile->id,
                                 $content,
                                 array_key_exists('source', $options) ?
                                 $options['source'] : 'web',
                                 $options);

        return $saved;
    }
}
