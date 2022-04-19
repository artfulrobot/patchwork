<?php

require_once 'patchworktest.civix.php';
// phpcs:disable
use CRM_Patchworktest_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function patchworktest_civicrm_config(&$config) {
  _patchworktest_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function patchworktest_civicrm_xmlMenu(&$files) {
  _patchworktest_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function patchworktest_civicrm_install() {
  _patchworktest_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function patchworktest_civicrm_postInstall() {
  _patchworktest_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function patchworktest_civicrm_uninstall() {
  _patchworktest_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function patchworktest_civicrm_enable() {
  _patchworktest_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function patchworktest_civicrm_disable() {
  _patchworktest_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function patchworktest_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _patchworktest_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function patchworktest_civicrm_managed(&$entities) {
  _patchworktest_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function patchworktest_civicrm_caseTypes(&$caseTypes) {
  _patchworktest_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function patchworktest_civicrm_angularModules(&$angularModules) {
  _patchworktest_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function patchworktest_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _patchworktest_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function patchworktest_civicrm_entityTypes(&$entityTypes) {
  _patchworktest_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function patchworktest_civicrm_themes(&$themes) {
  _patchworktest_civix_civicrm_themes($themes);
}


/**
 * Dummy implementation.
 */
function patchworktest_patchwork_apply_patch($corePath, &$code) {
  if ($corePath === '/CRM/Activity/BAO/ICalendar.php') {
    // Append a global declaration that would run when the patched file is included.
    $code .= "\n\$GLOBALS['patchworktest_patchwork_apply_patch']++;// patchworktest_version: " . $GLOBALS['patchworktest_version'] . "\n";
  }
  else {
    $code = FALSE;
  }
}

