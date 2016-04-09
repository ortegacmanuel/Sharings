<?php
/**
 * GNU social - a federating social network
 *
 * Sharings - A plugin to transform GNU social into a Sharing Economy platform
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
 * @category  Plugin
 * @package   GNUsocial
 * @author    Manuel Ortega <manuel@lasindias.coop>
 * @copyright 2014 Free Software Foundation http://fsf.org
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      https://www.gnu.org/software/social/
 */

if (!defined('GNUSOCIAL')) { exit(1); }


class SharingsPlugin extends MicroAppPlugin
{
    const VERSION         = '0.1';

    const SHARINGS_OBJECT          = 'http://activitystrea.ms/head/activity-schema.html#product';
    const SHARINGS_RESPONSE_OBJECT = 'http://activitystrea.ms/head/activity-schema.html#product-response';

    var $oldSaveNew = true;

    /**
     * Database schema setup
     *
     * @see Schema
     * @see ColumnDef
     *
     * @return boolean hook value; true means continue processing, false means stop.
     */
    function onCheckSchema()
    {
        $schema = Schema::get();
        $schema->ensureTable('sharing', Sharing::schemaDef());
        $schema->ensureTable('sharing_response', Sharing_response::schemaDef());
        $schema->ensureTable('sharing_notice', Sharing_notice::schemaDef());
            
        // Categorías y ciudades
 
        $schema->ensureTable('sharing_category', Sharing_category::schemaDef());
        $schema->ensureTable('sharing_city', Sharing_city::schemaDef());

        // Tipos
        $schema->ensureTable('sharing_type', Sharing_type::schemaDef());

        // Pendiente para cuando implementemos la configuración del plugin por los admin's
        //$schema->ensureTable('user_sharings_prefs', User_sharings_prefs::schemaDef());

        // Para la imagen asociada a un objeto o servicio
        $schema->ensureTable('file_to_sharing', File_to_sharing::schemaDef());

        return true;
    }

    /**
     * Show the CSS necessary for this plugin
     *
     * @param Action $action the action being run
     *
     * @return boolean hook value
     */
    function onEndShowStyles($action)
    {
        $action->cssLink($this->path('css/sharings.css'));
        return true;
    }

    function onQvitterEndShowScripts($action)
    {
        print '<script type="text/javascript" src="'.$this->path('js/sharings.js').'"></script>'."\n";
    }

    /**
     * Map URLs to actions
     *
     * @param URLMapper $m path-to-action mapper
     *
     * @return boolean hook value; true means continue processing, false means stop.
     */
    public function onRouterInitialized(URLMapper $m)
    {

        // only if not already mapped (by qvitter)
        try {
            $m->match('sharings/notices');
        } catch (Exception $e) {
            $m->connect('sharings/notices',
                            array('action' => 'sharingsnotices'));
        }

        $m->connect('main/sharings/new',
                    array('action' => 'newsharings'));

        $m->connect('main/sharings/:id/edit',
                    array('action' => 'editsharings'),
                    array('id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'));

        $m->connect('main/sharings/:id/delete',
                    array('action' => 'deletesharings'),
                    array('id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'));

        $m->connect('main/sharings/:id',
                    array('action' => 'showsharings'),
                    array('id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'));

        
        $m->connect('main/sharings/response/:id',
                    array('action' => 'showsharingsresponse'),
                    array('id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'));

        $m->connect('main/sharings/:id/respond',
                    array('action' => 'respondsharings'),
                    array('id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'));
        
        // pendiente para cuando implementemos la configuración del plugin
        //$m->connect('settings/sharings',
        //            array('action' => 'sharingssettings'));

        $m->connect('api/sharings/notices.json',
                        array('action' => 'apisharingsnotices'));

        $m->connect(
            'sharings/directory',
            array('action' => 'sharingsdirectory')
        );

        $m->connect(':nickname/sharings',
                    array('action' => 'mysharings'),
                    array('nickname' => Nickname::DISPLAY_FMT));

        return true;
    }

    public function onQvitterHijackUI(URLMapper $m)
    {

        $m->connect('sharings/notices',
                        array('action' => 'qvitter'));

        return true;
    }

    function types()
    {
        return array(self::SHARINGS_OBJECT, self::SHARINGS_RESPONSE_OBJECT);
    }

    public function verbs() {
        return array(ActivityVerb::POST, ActivityVerb::UPDATE, ActivityVerb::DELETE);
    }

    /**
     * When a notice is deleted, delete the related Sharing
     *
     * @param Notice $notice Notice being deleted
     *
     * @return boolean hook value
     */
    function deleteRelated(Notice $notice)
    {
        $s = Sharing::getByNotice($notice);

        if (!empty($s)) {
            $s->delete();
        }

        return true;
    }

    /**
     * Save a sharing from an activity
     *
     * @param Profile  $profile  Profile to use as author
     * @param Activity $activity Activity to save
     * @param array    $options  Options to pass to bookmark-saving code
     *
     * @return Notice resulting notice
     */
    function saveNoticeFromActivity(Activity $activity, Profile $profile, array $options=array())
    {
        // @fixme
        common_log(LOG_DEBUG, "XXX activity: " . var_export($activity, true));
        common_log(LOG_DEBUG, "XXX profile: " . var_export($profile, true));
        common_log(LOG_DEBUG, "XXX options: " . var_export($options, true));

        // Ok for now, we can grab stuff from the XML entry directly.
        // This won't work when reading from JSON source
        if ($activity->entry) {
            $sharingElements = $activity->entry->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'sharings');
            $responseElements = $activity->entry->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'response');
            $updateElements = $activity->entry->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'update');
            $deleteElements = $activity->entry->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'delete');
            if ($sharingElements->length) {

                $options['displayName'] = '';
                $options['summary'] = '';
                $options['price'] = '';
                $options['sharing_category_id'] = '';
                $options['sharing_type_id'] = '';
                $options['sharing_city_id'] = '';

                $data = $sharingElements->item(0);
                foreach ($data->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'displayName') as $node) {
                    $options['displayName'] = $node->textContent;
                }
                foreach ($data->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'summary') as $node) {
                    $options['summary'] = $node->textContent;
                }
                foreach ($data->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'price') as $node) {
                    $options['price'] = $node->textContent;
                }
                foreach ($data->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'category_id') as $node) {
                    $options['sharing_category_id'] = $node->textContent;
                }
                foreach ($data->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'type_id') as $node) {
                    $options['sharing_type_id'] = $node->textContent;
                }
                foreach ($data->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'city_id') as $node) {
                    $options['sharing_city_id'] = $node->textContent;
                }
                foreach ($data->getElementsByTagNameNS(self::SHARINGS_OBJECT, 'image') as $node) {
                    $image_url = $node->textContent;
                }

                try {
                    $notice = Sharing::saveNew($profile, $options);
                    common_log(LOG_DEBUG, "Saved sharing from ActivityStream data ok: notice id " . $notice->id);

                    $image_url = File_redirection::_canonUrl($image_url);
                    $redir = File_redirection::where($image_url);
                    $file = $redir->getFile();

                    $sharing = Sharing::getByNotice($notice);

                    if ($file instanceof File || !empty($file->id)) {
                        File_to_sharing::processNew($file, $sharing);
                    }

                    return $notice;
                } catch (Exception $e) {
                    common_log(LOG_DEBUG, "Sharing save from ActivityStream data failed: " . $e->getMessage());
                }
            } else if ($responseElements->length) {
                $data = $responseElements->item(0);
                $sharingUri = $data->getAttribute('sharing');
                $profile_id = $data->getAttribute('profile_id');

                if (!$sharingUri) {
                    // TRANS: Exception thrown trying to respond to a sharing without a sharing reference.
                    throw new Exception(_m('Invalid sharing response: No sharing reference.'));
                }
                $sharing = Sharing::getKV('uri', $sharingUri);
                if (!$sharing) {
                    // TRANS: Exception thrown trying to respond to a non-existing sharing.
                    throw new Exception(_m('Invalid sharing response: Sharing is unknown.'));
                }
                try {
                    $notice = Sharing_response::saveNew($profile, $sharing, $options);
                    common_log(LOG_DEBUG, "Saved Sharing_response ok, notice id: " . $notice->id);
                    return $notice;
                } catch (Exception $e) {
                    common_log(LOG_DEBUG, "Sharing response  save fail: " . $e->getMessage());
                }
            } else if ($updateElements->length) {
                $data = $updateElements->item(0);
                $sharingUri = $data->getAttribute('sharing');

                $options['verb'] = ActivityVerb::UPDATE;

                $options['displayName'] = $data->getAttribute('displayName');
                $options['summary'] = $data->getAttribute('summary');
                $options['price'] = $data->getAttribute('price');
                $options['sharing_category_id'] = $data->getAttribute('category_id');
                $options['sharing_type_id'] = $data->getAttribute('type_id');
                $options['sharing_city_id'] = $data->getAttribute('city_id');

                if (!$sharingUri) {
                    // TRANS: Exception thrown trying to respond to a sharing without a sharing reference.
                    throw new Exception(_m('Invalid sharing response: No sharing reference.'));
                }
                $sharing = Sharing::getKV('uri', $sharingUri);
                if (!$sharing) {
                    // TRANS: Exception thrown trying to respond to a non-existing sharing.
                    throw new Exception(_m('Invalid sharing response: Sharing is unknown.'));
                }
                try {
                    $notice = Sharing_notice::saveNew($profile, $sharing, $options);
                    common_log(LOG_DEBUG, "Saved Sharing_notice ok, notice id: " . $notice->id);
                    return $notice;
                } catch (Exception $e) {
                    common_log(LOG_DEBUG, "Sharing response  save fail: " . $e->getMessage());
                }
            } else if ($deleteElements->length) {
                $data = $deleteElements->item(0);
                $sharingUri = $data->getAttribute('sharing');

                $options['verb'] = ActivityVerb::DELETE;

                if (!$sharingUri) {
                    // TRANS: Exception thrown trying to respond to a sharing without a sharing reference.
                    throw new Exception(_m('Invalid sharing response: No sharing reference.'));
                }
                $sharing = Sharing::getKV('uri', $sharingUri);
                if (!$sharing) {
                    // TRANS: Exception thrown trying to respond to a non-existing sharing.
                    throw new Exception(_m('Invalid sharing response: Sharing is unknown.'));
                }
                try {
                    $notice = Sharing_notice::saveNew($profile, $sharing, $options);
                    common_log(LOG_DEBUG, "Saved Sharing_notice ok, notice id: " . $notice->id);
                    return $notice;
                } catch (Exception $e) {
                    common_log(LOG_DEBUG, "Sharing response  save fail: " . $e->getMessage());
                }
            } else {
                common_log(LOG_DEBUG, "YYY no sharing data");
            }
        }
    }

    function activityObjectFromNotice(Notice $notice)
    {
        assert($this->isMyNotice($notice));

        switch ($notice->object_type) {
        case self::SHARINGS_OBJECT:
            switch ($notice->verb) {
                case ActivityVerb::POST:
                    return $this->activityObjectFromNoticeSharing($notice);
                case ActivityVerb::UPDATE:
                    return $this->activityObjectFromNoticeSharingUpdate($notice);
                case ActivityVerb::DELETE:
                    return $this->activityObjectFromNoticeSharingDelete($notice);
                default:
                    // TRANS: Exception thrown when performing an unexpected action on a sharing.
                    // TRANS: %s is the unexpected object type.
                    throw new Exception(sprintf(_m('Unexpected verb for sharings plugin: %s.'), $notice->object_type));
                }
        case self::SHARINGS_RESPONSE_OBJECT:
            return $this->activityObjectFromNoticeSharingResponse($notice);
        default:
            // TRANS: Exception thrown when performing an unexpected action on a sharing.
            // TRANS: %s is the unexpected object type.
            throw new Exception(sprintf(_m('Unexpected type for sharings plugin: %s.'), $notice->object_type));
        }
    }

    function activityObjectFromNoticeSharingResponse(Notice $notice)
    {
        $object = new ActivityObject();
        $object->id      = $notice->uri;
        $object->type    = self::SHARINGS_RESPONSE_OBJECT;
        $object->title   = $notice->content;
        $object->summary = $notice->content;
        $object->link    = $notice->getUrl();

        $response = Sharing_response::getByNotice($notice);
        if ($response) {
            $sharing = $response->getSharing();
            if ($sharing) {
                // Stash data to be formatted later by
                // $this->activityObjectOutputAtom() or
                // $this->activityObjectOutputJson()...
                $object->sharingsProfile_id = $notice->profile_id;
                $object->sharingsUri = $sharing->uri;
            }
        }
        return $object;
    }

    function activityObjectFromNoticeSharing(Notice $notice)
    {
        $object = new ActivityObject();
        $object->id      = $notice->uri;
        $object->type    = self::SHARINGS_OBJECT;
        $object->title   = $notice->content;
        $object->summary = $notice->content;
        $object->link    = $notice->getUrl();

        $sharing = Sharing::getByNotice($notice);
        if ($sharing) {
            // Stash data to be formatted later by
            // $this->activityObjectOutputAtom() or
            // $this->activityObjectOutputJson()...
            $object->sharingsDisplayName = $sharing->displayName;
            $object->sharingsSummary = $sharing->summary;
            $object->sharingsPrice = $sharing->price;
            $object->sharingsCategory_id = $sharing->sharing_category_id;
            $object->sharingsType_id = $sharing->sharing_type_id;
            $object->sharingsCity_id = $sharing->sharing_city_id;

            $object->sharingsImage = File_to_sharing::getImageUrl($sharing);

        }

        return $object;
    }

    function activityObjectFromNoticeSharingUpdate(Notice $notice)
    {

        $object = new ActivityObject();
        $object->id      = $notice->uri;
        $object->type    = self::SHARINGS_OBJECT;
        $object->title   = $notice->content;
        $object->summary = $notice->content;
        $object->link    = $notice->getUrl();

        $sn = Sharing_notice::getByNotice($notice);

        if ($sn) {
           $sharing = $sn->getSharing();
            if ($sharing) {
                $object->sharingsUri = $sharing->uri;
                $object->sharingsDisplayName = $sharing->displayName;
                $object->sharingsSummary = $sharing->summary;
                $object->sharingsPrice = $sharing->price;
                $object->sharingsCategory_id = $sharing->sharing_category_id;
                $object->sharingsType_id = $sharing->sharing_type_id;
                $object->sharingsCity_id = $sharing->sharing_city_id;
            }
        }

        return $object;
    }

    function activityObjectFromNoticeSharingDelete(Notice $notice)
    {

        $object = new ActivityObject();
        $object->id      = $notice->uri;
        $object->type    = self::SHARINGS_OBJECT;
        $object->title   = $notice->content;
        $object->summary = $notice->content;
        $object->link    = $notice->getUrl();

        $object->sharingsUri = $notice->uri;

        return $object;
    }

    /**
     * Called when generating Atom XML ActivityStreams output from an
     * ActivityObject belonging to this plugin. Gives the plugin
     * a chance to add custom output.
     *
     * Note that you can only add output of additional XML elements,
     * not change existing stuff here.
     *
     * If output is already handled by the base Activity classes,
     * you can leave this base implementation as a no-op.
     *
     * @param ActivityObject $obj
     * @param XMLOutputter $out to add elements at end of object
     */
    function activityObjectOutputAtom(ActivityObject $obj, XMLOutputter $out)
    {
        if (isset($obj->sharingsDisplayName) and !(isset($obj->sharingsUri))) {
            /**
              *  <sharings:sharings xmlns:sharings="http://activitystrea.ms/head/activity-schema.html#product">
              *   <sharings:displayName>Clases de Komunuma</sharings:displayName>
              *   <sharings:summary>Clases de Komunuma</sharings:summary>
              *   <sharings:price>0</sharings:price>
              *   <sharings:category_id>2</sharings:category_id>
              *   <sharings:type_id>1</sharings:type_id>
              *   <sharings:city_id>0</sharings:city_id>
              *   <sharings:image>http://example.com/image.jpg</sharings:city_id>
              *  </sharings:sharings>
             */
            $data = array('xmlns:sharings' => self::SHARINGS_OBJECT);
            $out->elementStart('sharings:sharings', $data);
            $out->element('sharings:displayName', array(), $obj->sharingsDisplayName);
            $out->element('sharings:summary', array(), $obj->sharingsSummary);
            $out->element('sharings:price', array(), $obj->sharingsPrice);
            $out->element('sharings:category_id', array(), $obj->sharingsCategory_id);
            $out->element('sharings:type_id', array(), $obj->sharingsType_id);
            $out->element('sharings:city_id', array(), $obj->sharingsCity_id);
            $out->element('sharings:image', array(), $obj->sharingsImage);

            $out->elementEnd('sharings:sharings');           
        }
        if (isset($obj->sharingsProfile_id)) {
            /**
                * <sharings:response xmlns:sharings="http://activitystrea.ms/head/activity-schema.html#product" 
                *    sharing="http://gnusocial.bilbao/main/sharings/d0cf5ecd-5638-4165-9222-844efa3bae5e" 
                *    profile_id="1">
                *  </sharings:response>
                *
             */
            $data = array('xmlns:sharings' => self::SHARINGS_OBJECT,
                          'sharing'       => $obj->sharingsUri,
                          'profile_id'  => $obj->sharingsProfile_id);

            $out->element('sharings:response', $data, '');
        }
        if (isset($obj->sharingsUri) and isset($obj->sharingsDisplayName)) {
            /**
              *  <sharings:update xmlns:sharings="http://activitystrea.ms/head/activity-schema.html#product" 
              *      sharing="http://gnusocial.local/main/sharings/3b686246-72bb-4f46-ba61-aa6630019bb6"
              *      displayName="Clases de Komunuma" 
              *      summary="Clases de Komunuma" 
              *      price="0" 
              *      category_id="2" 
              *      type_id="1" 
              *      city_id="412">
              *  </sharings:update>
            */
            $data = array('xmlns:sharings' => self::SHARINGS_OBJECT,
                          'sharing'       => $obj->sharingsUri,
                          'displayName'  => $obj->sharingsDisplayName,
                          'summary'  => $obj->sharingsSummary,
                          'price'  => $obj->sharingsPrice,
                          'category_id'  => $obj->sharingsCategory_id,
                          'type_id'  => $obj->sharingsType_id,
                          'city_id'  => $obj->sharingsCity_id);

            $out->element('sharings:update', $data, '');
        }
        if (isset($obj->id) and !(isset($obj->sharingsUri)) and !(isset($obj->sharingsDisplayName))) {
            /**
              *  <sharings:delete xmlns:sharings="http://activitystrea.ms/head/activity-schema.html#product" 
              *      sharing="de29a116-c7bc-4048-b4ff-2388ca9295f5">
              *  </sharings:delete>
             */
            $data = array('xmlns:sharings' => self::SHARINGS_OBJECT,
                          'sharing'       => $obj->id);

            $out->element('sharings:delete', $data, '');
        }
    }

    /**
     * Called when generating JSON ActivityStreams output from an
     * ActivityObject belonging to this plugin. Gives the plugin
     * a chance to add custom output.
     *
     * Modify the array contents to your heart's content, and it'll
     * all get serialized out as JSON.
     *
     * If output is already handled by the base Activity classes,
     * you can leave this base implementation as a no-op.
     *
     * @param ActivityObject $obj
     * @param array &$out JSON-targeted array which can be modified
     */
    public function activityObjectOutputJson(ActivityObject $obj, array &$out)
    {
        common_log(LOG_DEBUG, 'QQQ: ' . var_export($obj, true));
        if (isset($obj->sharingsDisplayName) and !(isset($object->sharingsUri))) {
            $data = array('displayName' => $obj->sharingsDisplayName,
                          'summary' => $obj->sharingsSummary,
                          'price' => $obj->sharingsPrice,
                          'category_id' => $obj->sharingsCategory_id,
                          'type_id' => $obj->sharingsType_id,
                          'city_id' => $obj->sharingsCity_id,
                          'image' => $obj->sharingsImage); 
            $out['sharings'] = $data;
        }
        if (isset($obj->sharingsProfile_id)) {
            $data = array('sharing'       => $obj->sharingsUri,
                          'profile_id'  => $obj->sharingsProfile_id);
            $out['sharingsResponse'] = $data;
        }
        if (isset($object->sharingsUri)) {
            $data = array('sharing'       => $obj->sharingsUri,
                          'displayName'  => $obj->sharingsDisplayName,
                          'summary' => $obj->sharingsSummary,
                          'price' => $obj->sharingsPrice,
                          'category_id' => $obj->sharingsCategory_id,
                          'type_id' => $obj->sharingsType_id,
                          'city_id' => $obj->sharingsCity_id);

            $out['sharingsUpdate'] = $data;
        }
    }

    function entryForm($out)
    {
        return new NewSharingsForm($out);
    }

    // @fixme is this from parent?
    function tag()
    {
        return 'sharings';
    }

    function appTitle()
    {
        // TRANS: Application title.
        return _m('APPTITLE','Share');
    }

    function onStartAddNoticeReply($nli, $parent, $child)
    {
        // Filter out any sharing responses
        if ($parent->object_type == self::SHARINGS_OBJECT &&
            $child->object_type == self::SHARINGS_RESPONSE_OBJECT) {
            return false;
        }
        return true;
    }

    // Hide sharing responses for @chuck

    function onEndNoticeWhoGets($notice, &$ni) {
        if ($notice->object_type == self::SHARINGS_RESPONSE_OBJECT) {
            foreach ($ni as $id => $source) {
                $user = User::getKV('id', $id);
                if (!empty($user)) {
                    $pollPrefs = User_poll_prefs::getKV('user_id', $user->id);
                    if (!empty($pollPrefs) && ($pollPrefs->hide_responses)) {
                        unset($ni[$id]);
                    }
                }
            }
        }
        return true;
    }

    /**
     * Menu item for personal subscriptions/groups area
     *
     * @param Action $action action being executed
     *
     * @return boolean hook return
     */

    /*
    * Pendiente a la implementación de la configuración del plugin
    function onEndAccountSettingsNav($action)
    {
        $action_name = $action->trimmed('action');

        $action->menuItem(common_local_url('sharingssettings'),
                          // TRANS: Sharings plugin menu item on user settings page.
                          _m('MENU', 'Sharings'),
                          // TRANS: Sharings plugin tooltip for user settings menu item.
                          _m('Configure sharings behavior'),
                          $action_name === 'sharingssettings');

        return true;
    }
    */

    protected function showNoticeContent(Notice $stored, HTMLOutputter $out, Profile $scoped=null)
    {
        if ($stored->object_type == self::SHARINGS_RESPONSE_OBJECT) {
            parent::showNoticeContent($stored, $out, $scoped);
            return;
        }

        // If the stored notice is a SHARINGS_OBJECT
        $sharing = Sharing::getByNotice($stored);
        if ($sharing instanceof Sharing) {
            if (!$scoped instanceof Profile || $sharing->getResponse($scoped) instanceof Sharing_response) {
                // Either the user is not logged in or it has already responded; show the results.
                $form = new SharingsResultForm($sharing, $out);
            }else if ($sharing->profile_id == common_current_user()->getProfile()->id) { 
                $form = new MySharingsForm($sharing, $out);
            }else {
                $form = new SharingsResponseForm($sharing, $out);
            }
            $form->show();
        } else {
            // TRANS: Error text displayed if no sharing data could be found.
            //$out->text(_m('Sharing data is missing'));

            $form = new ThanksSharingsForm($out);

            $form->show();
        }
    }

    public function onEndPublicGroupNav(Menu $menu)
    {

        if (!common_config('singleuser', 'enabled')) {
                        // TRANS: Menu item in search group navigation panel.
                        $menu->out->menuItem(common_local_url('sharingsdirectory'), _m('MENU','Catálogo'),
                                             // TRANS: Menu item title in search group navigation panel.
                                             _m('Objetos y servicios compartidos en la red'), $menu->actionName == 'sharingsdirectory', 'nav_sharings_directory');
         }
    }

    function onEndPersonalGroupNav(Menu $menu, Profile $target, Profile $scoped=null)
    {
        $menu->menuItem(common_local_url('mysharings', array('nickname' => $target->getNickname())),
                          // TRANS: Menu item in sample plugin.
                          _m('Mi catálogo'),
                          // TRANS: Menu item title in sample plugin.
                          _m('Mi objetos y servicios'), false, 'nav_timeline_sharings');
        return true;
    }

    /**
     * Plugin version data
     *
     * @param array &$versions array of version data
     *
     * @return value
     */
    function onPluginVersion(array &$versions)
    {
        $versions[] = array('name' => 'Sharings',
                            'version' => self::VERSION,
                            'author' => 'Manuel Ortega',
                            'homepage' => 'http://git.lasindias.club/manuel/Sharings',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('Sharings transforma GNU social en una platoforma del compartir.'));
        return true;
    }
}
