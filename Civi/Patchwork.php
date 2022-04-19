<?php
namespace Civi;

use Civi;
use Civi\Patchwork\Worker;
use Civi\Patchwork\CannotIncludeException;
use Civi\Patchwork\PatchingFailedException;

class Patchwork {

  /**
   * @var string
   */
  protected string $patchesDir;

  /**
   * @var Patchwork Holds singleton.
   */
  protected static Patchwork $singleton;

  /**
   * Main calling point; all exceptions logged as critical messages, but
   * execution will continue.
   */
  public function includeOnce(string $corePath) {
    try {
      // Check $corePath is ok and we have what we need to continue.
      $p = new Worker($corePath);
      // Make patch as necessary
      $p->ensurePatchApplied();
      // If that worked, include the patched version.
      include_once $p->getPatchedPath();
    }
    catch (CannotIncludeException $e) {
      Civi::log()->critical("Patchwork CannotIncludeException, code may be missing ({$corePath}): " . $e->getMessage());
    }
    catch (PatchingFailedException $e) {
      Civi::log()->critical("Patchwork PatchingFailedException, using original unpatched code ({$corePath}): " . $e->getMessage());
      include_once $p->getOriginalPath();
    }
    catch (\Exception $e) {
      Civi::log()->critical("Patchwork unhandled exception: " . get_class($e) . " ({$corePath}): " . $e->getMessage());
      throw $e;
    }
  }

  public static function singleton() :Patchwork {
    if (!isset(static::$singleton)) {
      static::$singleton = new static();
    }
    return static::$singleton;
  }

  public function __construct() {

  }
  /**
   * Prepare the directory for the patched files, return the path or throw a PatchingFailedException.
   */
  public function prepareDir() :string {
    // Need to create patches dir.
    // Attempt, but don't abort (i.e. throw exception) if it fails.
    $patchesDir = $this->getPatchesDir();
    $outcome = \CRM_Utils_File::createDir($patchesDir, FALSE);
    if ($outcome === FALSE) {
      // Creation failed.
      throw new PatchingFailedException("Patchwork failed to create/prepare the patches directory at '{$patchesDir}'");
    }
    // Otherwise (TRUE|NULL), it's fine.
    return $patchesDir;
  }
  /**
   * Returns the path to the patches dir.
   */
  public function getPatchesDir() :string {
    if (!isset($this->patchesDir)) {
      $this->patchesDir = Civi::paths()->getPath('[civicrm.files]/patchwork/');
    }
    return $this->patchesDir;
  }
  /**
   * Delete all patched files.
   *
   * @return int the number of deleted patch files (may be 0).
   * @throw RuntimeException if a deletion fails.
   */
  public function deletePatches() :int {
    $patchesDir = $this->prepareDir();

    $dir = new \DirectoryIterator($patchesDir);
    $errors = [];
    $successes = 0;
    foreach ($dir as $fileinfo) {
      if (!$fileinfo->isDot()) {
        if (preg_match('/^[0-9a-f]{40}\.php$/', $fileinfo->getFilename())) {
          // Looks like a patch file.
          if (unlink($fileinfo->getPathname())) {
            $successes++;
          }
          else {
            $errors[] = $fileinfo->getFilename();
          }
        }
      }
    }
    if ($errors) {
      throw new \RuntimeException("Patchwork failed to delete the following patches in '$patchesDir' (check permissions?): " . implode(' ', $errors));
    }
    return $successes;
  }
  /**
   * Delete the patch file for a given core path.
   *
   * @param string the core file, e.g. /CRM/Core/Activity.php
   *
   * @throw RuntimeException if a deletion fails.
   */
  public function deletePatch(string $corePath) {
    $patchesDir = $this->getPatchesDir();

    $patchedPath = $patchesDir . '/' . $this->getHashedFilename($corePath);
    if (file_exists($patchedPath)) {
      if (!unlink($patchedPath)) {
        throw new \RuntimeException("Patchwork Failed to delete old patched file at '$patchedPath' (from $corePath) - check permissions?");
      }
      else {
        Civi::log()->notice("Patchwork::deletePatch($corePath) succeeded.");
      }
    }
    else {
      Civi::log()->info("Patchwork::deletePatch($corePath): no patch to delete.");
    }
  }
  /**
   * System check.
   */
  public function systemCheck(array &$messages) {
    // Check the patchwork dir exists and is writeable.
    $patchesDir = $this->getPatchesDir();
    try {
      $this->prepareDir();
    }
    catch (PatchingFailedException $e) {
      $messages[] = new \CRM_Utils_Check_Message(
        'patchwork_missing_patch_dir',
        ts('The directory at %1 is missing and attempting to create it failed.', [1 => $patchesDir]),
        ts('Patchwork patches dir missing'),
        \Psr\Log\LogLevel::ERROR,
        'fa-flag'
      );
      return;
    }
    if (!is_writeable($patchesDir)) {
      $messages[] = new \CRM_Utils_Check_Message(
        'patchwork_patch_dir_unwriteable',
        ts('The directory at %1 is not writeable', [1 => $patchesDir]),
        ts('Patchwork patches dir must be writeable.'),
        \Psr\Log\LogLevel::ERROR,
        'fa-flag'
      );
    }
    // Now check all the files within it are writeable.
    $dir = new \DirectoryIterator($patchesDir);
    $errors = [];
    foreach ($dir as $fileinfo) {
      if (!$fileinfo->isDot()) {
        if (!is_writeable($patchesDir . '/' . $fileinfo->getFilename())) {
          $errors[] = $fileinfo->getFilename();
        }
      }
    }
    if ($errors) {
      $messages[] = new \CRM_Utils_Check_Message(
        'patchwork_patch_unwriteable_files',
        ts('The files %1 are not writeable in %2', [2 => $patchesDir, 1 => implode(' ', $errors)]),
        ts('Patchwork patched files are not writeable.'),
        \Psr\Log\LogLevel::ERROR,
        'fa-flag'
      );
    }
  }
  public function getHashedFilename(string $corePath) :string {
    return sha1($corePath . CIVICRM_SITE_KEY) . '.php';
  }
}
