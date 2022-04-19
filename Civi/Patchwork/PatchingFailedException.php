<?php
namespace Civi\Patchwork;

/**
 * This extension is thrown if patchwork fails to apply a patch for some reason.
 * Typically this will lead to the original file being included.
 */
class PatchingFailedException extends \RuntimeException {
}

