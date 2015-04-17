<?php
/**
 * ownCloud - Quota Bar plugin
 *
 * @author Simon Kainz
 * @copyright 2012 Simon Kainz simon@familiekainz.at
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
$appId = 'quotabar';
$l = new OC_L10N($appId);

OC_Util::addScript($appId, 'quotabar');
OC_Util::addStyle($appId, 'quotabar');
//左邊選單放在分類為「mySpace」底下(分類設定放在/lib/template.php)
OC_App::addNavigationEntry(array(
    'id' => 'quotabar',
    'order' => 74,
    'href' => '#',
    'icon' => OC_Helper::imagePath('quotabar', 'hdd.png'),
    'name' => '<div id="quotabarDiv">'.$l -> t('Calculate Quota').'</div>',
), 'mySpace');
