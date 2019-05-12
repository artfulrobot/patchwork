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
  patchwork__prepareDir();
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
 * Implementation of hook_civicrm_check
 *
 * Add a check to the status page/System.check results if $snafu is TRUE.
 */
function patchwork_civicrm_check(&$messages) {

  // Check the patchwork dir exists and is writeable.
  $patches_dir = Civi::paths()->getPath('[civicrm.files]/patchwork/');
  if (patchwork__prepareDir() === FALSE) {
    $messages[] = new CRM_Utils_Check_Message(
      'patchwork_missing_patch_dir',
      ts('The directory at %1 is missing and attempting to create it failed.', [1 => $patches_dir]),
      ts('Patchwork patches dir missing'),
      \Psr\Log\LogLevel::ERROR,
      'fa-flag'
    );
    return;
  }
  if (!is_writeable($patches_dir)) {
    $messages[] = new CRM_Utils_Check_Message(
      'patchwork_patch_dir_unwriteable',
      ts('The directory at %1 is not writeable', [1 => $patches_dir]),
      ts('Patchwork patches dir must be writeable.'),
      \Psr\Log\LogLevel::ERROR,
      'fa-flag'
    );
  }
  // Now check all the files within it are writeable.
  $dir = new DirectoryIterator($patches_dir);
	$errors = [];
	foreach ($dir as $fileinfo) {
		if (!$fileinfo->isDot()) {
      if (!is_writeable($patches_dir . '/' . $fileinfo->getFilename())) {
        $errors[] = $fileinfo->getFilename();
      }
		}
	}
  if ($errors) {
    $messages[] = new CRM_Utils_Check_Message(
      'patchwork_patch_unwriteable_files',
      ts('The files %1 are not writeable in %2', [2 => $patches_dir, 1 => implode(' ', $errors)]),
      ts('Patchwork patched files are not writeable.'),
      \Psr\Log\LogLevel::ERROR,
      'fa-flag'
    );
  }
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

  $paths = Civi::paths();
  // paranoia: check the override for anything unexpected. If there is a case
  // for anything that doesn't match this regex, please submit an issue/PR.
  if (!preg_match('@^[/a-zA-Z0-9_-]+\.php$@', $override)) {
    Civi::log()->critical("patchwork: patchwork__patch_file called with dodgy looking override file. Refusing to touch it.", ['override' => $override]);
    return;
  }

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
    Civi::log()->info("patchwork: identified need to (re)patch $override");

    $code = file_get_contents($original_file);
    if ($code) {
      $dummy = NULL;
      try {
        CRM_Utils_Hook::singleton()->invoke(
          2, $override, $code,
          $success, $dummy, $dummy, $dummy,
          'patchwork_apply_patch');

        // Save the patched code and if that worked, we'll include that file.
        if ($code) {
          patchwork__prepareDir();
          // Prepend a comment to the code.
          $code = "<?php /** patchwork-patched version of $override */ ?>$code";

          if (file_put_contents($patched_version, $code)) {
            $file_to_include = $patched_version;
            Civi::log()->info("patchwork: successfully (re)patched $override");
          }
          else {
            Civi::log()->error("patchwork: Failed patching $override while writing file. Attempted to write to: $patched_version");
          }
        }
        else {
          Civi::log()->warning("patchwork: Patching $override resulted in no code?! Using original.");
        }
      }
      catch (Exception $e) {
        // Something failed.
        Civi::log()->error("patchwork: Failed patching $override.", ['exception' => $e->getMessage() . $e->getTraceAsString()]);
      }
    }
  }
  else {
    // The file is already up-to-date.
    $file_to_include = $patched_version;
  }
  include $file_to_include;
}

function patchwork__prepareDir() {
  // Need to create patches dir.
  // Attempt, but don't abort (i.e. throw exception) if it fails.
  $patches_dir = Civi::paths()->getPath('[civicrm.files]/patchwork/');
  return CRM_Utils_File::createDir($patches_dir, FALSE);
}
