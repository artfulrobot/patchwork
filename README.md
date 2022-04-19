# Patchwork - a new idea for patching core CiviCRM files.

Using core override files is nasty.

- They break every version upgrade.

- If two extensions want to do it, whose version gets used?

This extension does nothing on its own, but it provides a method for other
extensions to override core files in a way that potentially mitigates (not
solves) these problems.


## How to use it

The [mailstorepermissions extension](https://github.com/artfulrobot/mailstorepermissions)
shows a real world example.

1. It includes a core override for the file which contains this line of code:

        <?php patchwork__patch_file('/CRM/Mailing/MailStore.php');

2. It implements `hook_patchwork_patch_file($override, &$code)` to change the
   code of that file.

Nb. it is up to your extension to change the code of the file. You may do this
by piping it through a shell `patch` command, or by some clever regex or
whatever else you want to do.

If you can't patch it you can either:

- throw an exception. It is advised to throw one of:

   - `Civi\Patchwork\CannotIncludeException` if you would like the outcome to
     be that no code (not even the original) gets used.

   - `Civi\Patchwork\PatchingFailedException` if you would like the outcome to
     be that the original code gets used.

- set the `$code` to something falsy in which case the original core file will
  be used (and an error logged).

## Won't this slow my site down - all this patching?

Not too much, the patched files are cached in the `[civicrm.files]/patchwork/` directory.

## What happens when I upgrade core?

The patched files will be recreated! Before using a patched file its mtime is
compared to the mtime of the core file, and if the patched file is older then it
gets recreated.

## How do I manually recreate my patched files?

The easiest way is to delete the patched file in question.

-  Manually: Just delete the files in `[civicrm.files]/patchwork/`

- Progammatically: call
  `\Civi\Patchwork::deletePatch('/CRM/Mailing/MailStore.php')` which will delete
  a patch for that core file. This can be useful in **extension Upgrader
  classes** for example, to ensure your new patch logic is applied.

- Progammatically: call `\Civi\Patchwork::deletePatches()` which will delete
  *all* patches. This could feasibly be useful if you were worried about old
  patch files kicking around; say a patched version of something that a
  now-deleted/disabled extension created.

Deleting patched files should be reasonably safe to do at any time; they will
just get recreated on their next use. There is a slight timing risk if the
deletion happens after one process created it and before it calls `include()`
on it, but we're talking microseconds of risk here.

## Doesn't this leave the patched files vulnerable to download?

The filenames of the patched files are created by hashing the site key with the
original filename, so they're hard to guess, so you're probably ok.

## What if 2+ extensions want to override the same file?

Having two override files both calling `patchwork__patch_file()` won't matter
because they do the same thing anyway.

However your patching code will need to be able to deal with whatever other
extensions are patching the file. You'll probably manage it if you keep your
patches very minimal - e.g. call out to a function in your code instead of
injecting/changing whole swathes of code. As I said, this is not a solution,
it's just a helper.


## Requirements

* PHP v7.0+
* CiviCRM 5.9.1

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl patchwork@https://github.com/artfulrobot/patchwork/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/artfulrobot/patchwork.git
cv en patchwork
# And if you want to check out the demo...
git clone https://github.com/artfulrobot/patchworkdemo.git
cv en patchworkdemo
```

## License

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Change log

### 1.3

- Big refactor

- Add phpunit tests (including patchworktest extension). Note: patchworktest extension should **never** be installed on a site. It's just for use by phpunit.

- Add programmatic ways to delete patched files, e.g. useful if your extension has updated the patch it applies  (see "How do I manually recreate my patched files" above)
