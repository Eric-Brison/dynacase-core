<?php
// ---------------------------------------------------------------
// $Id: CORE.app,v 1.16 2006/07/27 16:04:19 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/App/Core/CORE.app,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ------------------------------------------------------
global $app_desc, $action_desc;

$app_desc = array(
    "name" => "CORE", //Name
    "short_name" => N_("Core"), //Short name
    "description" => N_("Core Application Manager"), //long description
    "icon" => "core.png", //Icon
    "displayable" => "N", //Should be displayed on an app list
    "with_frame" => "Y", //Use multiframe ? (Y,N)
    "iorder" => 0, // install order first
    "tag" => "CORE SYSTEM"
);

$action_desc = array(
    array(
        "name" => "WELCOME",
        "root" => "Y"
    ),
    array(
        "name" => "INVALID",
        "desc" => N_("Message to indicate invalid configuration")
    ),
    array(
        "name" => "BLANK"
    ),
    array(
        "name" => "GENCSS",
        "layout" => "core.css"
    ),
    array(
        "name" => "SYSTEMCSS",
        "short_name" => N_("concat stylecss+sizecss")
    ),
    array(
        "name" => "CORE_CSS",
        "layout" => "core.css"
    ),
    array(
        "name" => "SETACTPAR"
    ),
    array(
        "name" => "ERROR",
    ),
    array(
        "name" => "CHANGE_USER_PASSWORD",
        "short_name" => N_("change user password"),
        "root" => "N"
    )
);
