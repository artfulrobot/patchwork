<?php

require_once 'patchwork.civix.php';
use CRM_Patchwork_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function patchwork_civicrm_config(&$config) {
  _patchwork_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function patchwork_civicrm_xmlMenu(&$files) {
  _patchwork_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function patchwork_civicrm_install() {
  // Need to create patches dir.
  $patches_dir = Civi::paths()->getPath('[civicrm.files]/patchwork/');
  CRM_Utils_File::createDir($patches_dir);
  _patchwork_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function patchwork_civicrm_postInstall() {
  _patchwork_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function patchwork_civicrm_uninstall() {
  $patches_dir = Civi::paths()->getPath('[civicrm.files]/patchwork/');
  CRM_Utils_File::cleanDir($patches_dir);
  _patchwork_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function patchwork_civicrm_enable() {
  _patchwork_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function patchwork_civicrm_disable() {
  _patchwork_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function patchwork_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _patchwork_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function patchwork_civicrm_managed(&$entities) {
  _patchwork_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function patchwork_civicrm_caseTypes(&$caseTypes) {
  _patchwork_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function patchwork_civicrm_angularModules(&$angularModules) {
  _patchwork_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function patchwork_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _patchwork_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function patchwork_civicrm_entityTypes(&$entityTypes) {
  _patchwork_civix_civicrm_entityTypes($entityTypes);
}


/**
 * Include a patched file, creating it if needed.
 *
 * @param string $override A path relative to the root dir of civicrm. e.g. /CRM/Core/Activity/BAO/Activity.php
 */
function patchwork__patch_file($override) {

  $paths = Civi::paths();
  $original_file = $paths->getPath("[civicrm.root]$override");

  $patched_version = $paths->getPath(
    '[civicrm.files]/patchwork/'
    . sha1($override . CIVICRM_SITE_KEY)
    . '.php');

  // Do we need to (re)create the patched file?
  $create_patch = (!file_exists($patched_version)
    || (filemtime($original_file) > filemtime($patched_version)));

  // Default action is to include original file.
  $file_to_include = $original_file;

  if ($create_patch) {
    // @todo.
    $code = file_get_contents($original_file);
    if ($code) {
      $dummy = NULL;
      try {
        CRM_Utils_Hook::singleton()->invoke(
          2, $override, $code,
          $success, $dummy, $dummy, $dummy,
          'patchwork_apply_patch');

        // Save the patched code and if that worked, we'll include that file.
        if ($code && file_put_contents($patched_version, $code)) {
          $file_to_include = $patched_version;
        }

      }
      catch (Exception $e) {
        // Something failed.
        Civi::log()->error("Failed patching $override.", ['exception' => $e->getMessage() . $e->getTraceAsString()]);
      }
    }
  }
  else {
    // The file is already up-to-date.
    $file_to_include = $patched_version;
  }
  include $file_to_include;
}

