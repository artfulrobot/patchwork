<?php
namespace Civi\Patchwork;

use Civi;

/**
 * The main doing class.
 *
 * outcomes of normal operation:
 * - orig file not found: do not include anything, log critical
 * - orig file could not be read; could not be patched: log critical but include original.
 * - new patch created: log notice; include patched  file.
 * - existing patch reused: include patched file.
 * - coding errors: refuse to include anythign. log critical.
 *
 */
class Worker {

  /**
   * @var string The core filepath being overridden (starts with /)
   */
  protected $corePath = '';

  /**
   * @var string Absolute path to $corePath
   */
  protected $originalPath = '';

  /**
   * @var string Absolute path to patched version.
   */
  protected $patchedPath = '';

  /**
   * @var string The file that should be included, or '' if the original file is not found.
   */
  protected $usePatch = '';

  /**
   * Main calling point; all exceptions logged as critical messages, but
   * execution will continue.
   */
  public static function doInclude(string $corePath) {
    try {
      // Check $corePath is ok and we have what we need to continue.
      $p = new static($corePath);
      // Make patch as necessary
      $this->ensurePatchApplied();
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

  /**
   * Prepare the directory for the patched files, return the path or throw a PatchingFailedException.
   */
  public static function preparePatchworkDir() :string {
    // Need to create patches dir.
    // Attempt, but don't abort (i.e. throw exception) if it fails.
    $patchesDir = static::getPatchworkDir();
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
  public static function getPatchworkDir() :string {
    return Civi::paths()->getPath('[civicrm.files]/patchwork/');
  }
  /**
   * Sanity-check the input and calculate the required paths.
   *
   * Throws exceptions if it will not be able to run.
   */
  public function __construct(string $corePath) {

    // paranoia: check the override for anything unexpected. If there is a case
    // for anything that doesn't match this regex, please submit an issue/PR.
    if (!preg_match('@^/[/a-zA-Z0-9_-]+\.php$@', $corePath)) {
      throw new CannotIncludeException("Dodgy looking override file. Refusing to touch it. This is likely a coding error in an extension trying to use Patchwork. Given: " . json_encode($corePath, JSON_UNESCAPED_SLASHES));
    }

    $paths = Civi::paths();
    $this->corePath = $corePath;
    $this->originalPath = $paths->getPath("[civicrm.root]$corePath");
    if (!file_exists($this->originalFilePath)) {
      throw new CannotIncludeException("Can not patch non-existant file '{$this->originalFilePath}'");
    }

    if (!defined('CIVICRM_SITE_KEY')) {
      // Without a site key, the hash would be knowable. This could potentially
      // expose non-public php files to public access (though it shouldn't in a
      // properly configured environment).
      throw new PatchingFailedException("Refusing to patch because missing CIVICRM_SITE_KEY could be a security risk.");
    }

    // Otherwise, looks ok, store the patchedPath now.
    $this->patchedPath = $paths->getPath(
      '[civicrm.files]/patchwork/'
      . sha1($corePath . CIVICRM_SITE_KEY)
      . '.php');
  }

  /**
   * Ensure we have a patched file or throw exception.
   */
  public function ensurePatchApplied(bool $force = FALSE) :Worker {

    // Do we need to (re)create the patched file?
    if ($force) {
      Civi::log()->info("Patchwork: forcing (re)patch of '{$this->corePath}'");
    }
    elseif (!file_exists($this->patchedPath)) {
      Civi::log()->info("Patchwork: Creating new patched version of '{$this->corePath}'");
    }
    elseif (filemtime($this->originalPath) > filemtime($this->patchedPath)) {
      Civi::log()->info("Patchwork: Updating patched version of '{$this->corePath}'");
    }
    else {
      // Nothing to do, we have an up-to-date patched file.
      return $this;
    }

    // Try to patch the file.
    return $this->createPatchedFile();
  }

  /**
   */
  public function createPatchedFile() :Worker {

    $code = file_get_contents($this->originalPath);
    if (!$code) {
      throw new CannotIncludeException("Original file ({$this->originalPath}) has zero size/could not be loaded!");
    }

    $dummy = NULL;
    CRM_Utils_Hook::singleton()->invoke(
      ['override', 'code'], $this->originalPath, $code,
      $dummy, $dummy, $dummy, $dummy,
      'patchwork_apply_patch');

    // Save the patched code and if that worked, we'll include that file.
    if (!$code) {
      throw new PatchingFailedException("After patching, code was empty.");
    }

    static::preparePatchworkDir();

    // Prepend a comment to the code.
    $code = "<?php /** patchwork-patched version of {$this->corePath} */ ?>$code";

    if (file_put_contents($this->patchedPath, $code)) {
      $file_to_include = $patched_version;
      Civi::log()->info("patchwork: successfully (re)patched $override", []);
    }
    else {
      throw new PatchingFailedException("Failed to write patch file at {$this->patchedPath}");
    }
  }

  /**
   * Return the aboslute path to the patched file.
   */
  public function getPatchedPath() :string {
    return $this->patchedPath;
  }

  /**
   * Return the aboslute path to the original file.
   */
  public function getOriginalPath() :string {
    return $this->originalPath;
  }


}

