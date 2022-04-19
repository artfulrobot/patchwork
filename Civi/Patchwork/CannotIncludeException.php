<?php
namespace Civi\Patchwork;

/**
 * This extension is thrown if patchwork will be unable to provide a file to be
 * included at all; not even the original.
 */
class CannotIncludeException extends \RuntimeException {
}
