<?php

use CRM_Patchwork_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class Civi_PatchworkTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->install(['patchwork', 'patchworktest'])
      ->apply();
  }

  public function setUp() {
    parent::setUp();
    Civi\Patchwork::singleton()->deletePatches();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   *
   */
  public function testBasicOperation() {
    $GLOBALS['patchworktest_patchwork_apply_patch'] = 0;
    $GLOBALS['patchworktest_version'] = 1;

    // Cause Civi to load this class file - we hope it loads the patched version.
    include_once 'CRM/Activity/BAO/ICalendar.php';
    // The patched version sets a global var, so we can test if that ran.
    $this->assertEquals(1, $GLOBALS['patchworktest_patchwork_apply_patch'], "Expected the patched file to have been run but it appears not to have been.");

    // Check the file is where we expect it to be.
    $p = \Civi\Patchwork::singleton();
    $patchFile = $p->getPatchesDir() . '/' . $p->getHashedFilename('/CRM/Activity/BAO/ICalendar.php');
    $this->assertFileExists($patchFile);

    // Check the file contains 'patchworktest_version: 1'
    $src = file_get_contents($patchFile);
    $this->assertStringContainsString('patchworktest_version: 1', $src, "Patched file does not contain expected 'patchworktest_version: 1'");

    // Check we can delete the file
    $p->deletePatch('/CRM/Activity/BAO/ICalendar.php');
    $this->assertFileNotExists($p->getPatchesDir() . '/' . $p->getHashedFilename('/CRM/Activity/BAO/ICalendar.php'));

    // Check it would be recreated by ensurePatchApplied()
    $w = new Civi\Patchwork\Worker('/CRM/Activity/BAO/ICalendar.php');
    $w->ensurePatchApplied();
    $this->assertFileExists($p->getPatchesDir() . '/' . $p->getHashedFilename('/CRM/Activity/BAO/ICalendar.php'));

    // Check deletePatches deletes all patches (but leaves other files)
    // Create a dummy other patch with 40 char filename (like SHA1)
    touch($p->getPatchesDir() . '/0123456789012345678901234567890123456789.php');
    // Create some other file that does not match.
    touch($p->getPatchesDir() . '/something-else.txt');
    $p->deletePatches();
    $this->assertFileExists($p->getPatchesDir() . '/something-else.txt');
    $this->assertFileNotExists($p->getPatchesDir() . '/0123456789012345678901234567890123456789.php');
    $this->assertFileNotExists($p->getPatchesDir() . '/' . $p->getHashedFilename('/CRM/Activity/BAO/ICalendar.php'));
    // Clean up
    unlink($p->getPatchesDir() . '/something-else.txt');

    // Now this time...repeat this:
    $w->ensurePatchApplied();
    $src = file_get_contents($patchFile);
    $this->assertStringContainsString('patchworktest_version: 1', $src, "Patched file does not contain expected 'patchworktest_version: 1'");
    // Calling ensurePatchApplied again should not result in a change, since nothing has changed.
    $GLOBALS['patchworktest_version'] = 2;
    $w->ensurePatchApplied();
    $src = file_get_contents($patchFile);
    $this->assertGreaterThan(0, strpos($src, 'patchworktest_version: 1'), "Patched file does not contain expected 'patchworktest_version: 1'");
    // Change the mtime on our patch file so it's older than that of the core file.
    $coreMtime = filemtime($w->getOriginalPath());
    touch($patchFile, $coreMtime - 60);
    $w->ensurePatchApplied();
    $src = file_get_contents($patchFile);
    $this->assertGreaterThan(0, strpos($src, 'patchworktest_version: 2'), "Patched file does not contain expected 'patchworktest_version: 2'");

    // Check we can force it.
    $GLOBALS['patchworktest_version'] = 3;
    $w->ensurePatchApplied(TRUE);
    $src = file_get_contents($patchFile);
    $this->assertGreaterThan(0, strpos($src, 'patchworktest_version: 3'), "Patched file does not contain expected 'patchworktest_version: 3'");

    // Cleanup.
    $p->deletePatches();
  }


}
