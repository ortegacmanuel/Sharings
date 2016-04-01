#!/usr/bin/env php
<?php
/*
 * StatusNet - a distributed open-source microblogging tool
 * Copyright (C) 2008, 2009, StatusNet, Inc.
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
 */

define('INSTALLDIR', realpath(dirname(__FILE__) . '/../../../..'));

define('SCRIPTSDIR', realpath(dirname(__FILE__) ));

$helptext = <<<END_OF_DELETEUSER_HELP
Adding categories, subcategories and types to Sharings tables

END_OF_DELETEUSER_HELP;

require_once INSTALLDIR.'/scripts/commandline.inc';

$srcfile = SCRIPTSDIR . '/categories.json';

$data = json_decode(file_get_contents($srcfile));

$category_id = 1;

foreach ($data as $row) {

  $obj = new Sharing_category();

  if(!$obj->get($category_id)) {
      $obj->id        = $category_id;
      $obj->name        = $row->name;
      $obj->slug        = $row->slug;
      $obj->description = $row->description;
      $obj->created     = common_sql_now();
      $obj->insert();
  } else {
      $obj->name        = $row->name;
      $obj->slug        = $row->slug;
      $obj->description = $row->description;
      $obj->update();
  }
  $category_id++;
}

$srcfile = SCRIPTSDIR . '/types.json';

$data = json_decode(file_get_contents($srcfile));

$type_id = 1;

foreach ($data as $row) {
  $obj = new Sharing_type();

  if(!$obj->get($type_id)) {
      $obj->id          = $type_id;
      $obj->name        = $row->name;
      $obj->slug        = $row->slug;
      $obj->description = $row->description;
      $obj->created     = common_sql_now();
      $obj->insert();
  } else {
      $obj->name        = $row->name;
      $obj->slug        = $row->slug;
      $obj->description = $row->description;
      $obj->update();
  }
  $type_id++;
}

$srcfile = SCRIPTSDIR . '/cities.json';

$data = json_decode(file_get_contents($srcfile));

$city_id = 1;

foreach ($data as $row) {

  $obj = new Sharing_city();

  if(!$obj->get($city_id)) {
      $obj->id = $city_id;
      $obj->name = $row->city;
      $obj->lat = $row->lat;
      $obj->long = $row->lon;
      $obj->created     = common_sql_now();
      $obj->insert();
  } else {
      $obj->name = $row->city;
      $obj->lat = $row->lat;
      $obj->long = $row->lon;
      $obj->update();
  }
  $city_id++;
}
