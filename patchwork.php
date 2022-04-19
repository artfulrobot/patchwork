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
  try {
    \Civi\Patchwork::singleton()->prepareDir();
  }
  catch (Civi\Patchwork\PatchingFailedException $e) {
    Civi::log()->critical('While installing patchwork extension: ' . $e->getMessage() . ' Extension will not do anything until permissions are fixed.');
  }
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
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
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
 * Implementation of hook_civicrm_check
 *
 * Add a check to the status page/System.check results if $snafu is TRUE.
 */
function patchwork_civicrm_check(&$messages) {
  \Civi\Patchwork::singleton()->systemCheck($messages);
}

/**
 * Include a patched file, creating it if needed.
 *
 * This is called by the core override files which must be created in
 * implementing extensions - see README.md
 *
 * @param string $override A path relative to the root dir of civicrm. e.g. /CRM/Core/Activity/BAO/Activity.php
 */
function patchwork__patch_file($override) {
  Civi\Patchwork::singleton()->includeOnce($override);
}

