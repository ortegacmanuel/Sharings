<?php
/*
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2008, 2009, StatusNet, Inc.
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
 * along with this program.     If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Table Definition for file_to_post
 */

class File_to_sharing extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'file_to_sharing';                    // table name
    public $file_id;                         // int(4)  primary_key not_null
    public $sharing_id;                      // char(36) primary key not null -> UUID
    public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            'fields' => array(
                'file_id' => array('type' => 'int', 'not null' => true, 'description' => 'id of URL/file'),
                'sharing_id' => array('type' => 'char', 'length' => 36, 'not null' => true, 'description' => 'UUID'),
                'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),
            ),
            'primary key' => array('file_id', 'sharing_id'),
            'foreign keys' => array(
                'file_to_sharing_file_id_fkey' => array('file', array('file_id' => 'id')),
                'file_to_sharing_sharing_id_fkey' => array('sharing', array('sharing_id' => 'id')),
            ),
            'indexes' => array(
                'file_id_idx' => array('file_id'),
                'sharing_id_idx' => array('sharing_id'),
            ),
        );
    }

    static function processNew(File $file, Sharing $sharing) {
        static $seen = array();

        $file_id = $file->getID();
        $sharing_id = $sharing->id;
        if (!array_key_exists($sharing_id, $seen)) {
            $seen[$sharing_id] = array();
        }

        if (empty($seen[$sharing_id]) || !in_array($file_id, $seen[$sharing_id])) {
            try {
                $f2p = File_to_sharing::getByPK(array('sharing_id' => $sharing_id,
                                                   'file_id' => $file_id));
            } catch (NoResultException $e) {
                $f2p = new File_to_sharing;
                $f2p->file_id = $file_id;
                $f2p->sharing_id = $sharing_id;
                $f2p->insert();
                
                $file->blowCache();
            }

            $seen[$sharing_id][] = $file_id;
        }
    }

    static function getNoticeIDsByFile(File $file)
    {
        $f2p = new File_to_post();

        $f2p->selectAdd();
        $f2p->selectAdd('post_id');

        $f2p->file_id = $file->getID();

        $ids = array();

        if (!$f2p->find()) {
            throw new NoResultException($f2p);
        }

        return $f2p->fetchAll('post_id');
    }

    function delete($useWhere=false)
    {
        try {
            $f = File::getByID($this->file_id);
            $f->blowCache();
        } catch (NoResultException $e) {
            // ...alright, that's weird, but no File to delete anyway.
        }

        return parent::delete($useWhere);
    }

    static function getImageUrl($sharing)
    {
        $f2s = File_to_sharing::getKV('sharing_id', $sharing->id);

        if($f2s instanceof File_to_sharing){
            $f = File::getByID($f2s->file_id);

            if($f instanceof File){
                return $f->url;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    static function removeImage($sharing)
    {
        $f2s = File_to_sharing::getKV('sharing_id', $sharing->id);

        if($f2s instanceof File_to_sharing){
            $f = File::getByID($f2s->file_id);

            if($f instanceof File){
                $f->delete();
            }
            
            $f2s->delete();           
        }
    }
}
