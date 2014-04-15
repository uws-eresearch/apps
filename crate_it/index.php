<?php

/**
 * ownCloud - Cr8it App
 *
 * @author Lloyd Harischandra
 * @copyright 2014 University of Western Sydney www.uws.edu.au
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

// Check if we are a user
OCP\User::checkLoggedIn();

$user = OCP\User::getUser();

$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();

$manifestData = $bagit_manager->getManifestData();
$config = $bagit_manager->getConfig();

$description_length = empty($config['description_length']) ? 4000 : $config['description_length'];
$max_sword_mb = empty($config['max_sword_mb']) ? 0 : $config['max_sword_mb'];
$max_zip_mb = empty($config['max_zip_mb']) ? 0 : $config['max_zip_mb'];

// create a new template to show the cart
$tmpl = new OCP\Template('crate_it', 'index', 'user');
$tmpl->assign('previews', $bagit_manager->showPreviews());
$tmpl->assign('bagged_files', $bagit_manager->getBaggedFiles());
$tmpl->assign('description', $manifestData['description']);
$tmpl->assign('description_length', $description_length);
$tmpl->assign('crates', $bagit_manager->getCrateList());
$tmpl->assign('top_for', $bagit_manager->lookUpMint("", 'top'));
$tmpl->assign('selected_crate', $bagit_manager->getSelectedCrate());

if ($manifestData['creators']) {
   $tmpl->assign('creators', array_values($manifestData['creators']));
}
else {
   $tmpl->assign('creators', array());
}

if ($manifestData['activities']) {
   $tmpl->assign('activities', array_values($manifestData['activities']));
}
else {
   $tmpl->assign('activities', array());
}

$tmpl->assign('mint_status', $bagit_manager->getMintStatus());
$tmpl->assign('sword_status', $bagit_manager->getSwordStatus());
$tmpl->assign('sword_collections', $bagit_manager->getCollectionsList());
$tmpl->assign('max_sword_mb', $max_sword_mb);
$tmpl->assign('max_zip_mb', $max_zip_mb);
$tmpl->printPage();