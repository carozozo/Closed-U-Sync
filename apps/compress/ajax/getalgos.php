<?php

/**
* ownCloud - Compress plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

require_once('../../../lib/base.php');
$l=new OC_L10N('compress');
OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('compress');

$k = Array();
if(in_array('zip', get_loaded_extensions())){
	$k[] = '<option value="zip">'.$l -> t('Compress to').' zip</option>';
}

OC_JSON::encodedPrint($k);