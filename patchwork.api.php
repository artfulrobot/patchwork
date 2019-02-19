<?php

/**
 * Apply your patch.
 *
 * @param string $original - the original file, like /CRM/Core/...php
 *
 * @param string &$code - the code of the original file (which may have been
 * altered already by another implementor of this hook).
 *
 * This demo implementation is extremely crude. It creates a php file that outputs
 * "it worked! File /CRM/Core/Activity/BAO/Activity.php created at 12:02:34 and accessed at 12:06:21"
 * and dies. This would (obviously) kill your CiviCRM instance, but you'd know the override worked :-)
 */
function hook_patchwork_apply_patch($original, &$code) {
  if ($original === '/CRM/Core/Activity/BAO/Activity.php') {
    $code = '<' . '?php echo "it worked! File ' . $file . ' created at ' . date("H:i:s") . ' and accessed at " . date("H:i:s");exit;';
  }
}

