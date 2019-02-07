# Patchwork - a new idea for patching core CiviCRM files.

Using core override files is nasty.

- They break every version upgrade.

- If two extensions want to do it, whose version gets used?

This extension does nothing on its own, but it provides a method for other
extensions to override core files in a way that potentially mitigates (not
solves) these problems.


## How to use it

The [mailstorepermissions
extension](https://github.com/artfulrobot/mailstorepermissions)
shows a real world example.

1. It includes a core override for the file which contains this line of code:

        <?php patchwork__patch_file('/CRM/Mailing/MailStore.php');

2. It implements `hook_patchwork_patch_file($override, &$code)` to change the
   code of that file.

Nb. it is up to your extension to change the code of the file. You may do this
by piping it through a shell `patch` command, or by some clever regex or
whatever else you want to do.

If you can't patch it you can either:

- throw an exception

- set the `$code` to something falsy.

In which case the original core file will be used (and an error logged).

## Won't this slow my site down - all this patching?

Not too much, the patched files are cached in the `[civicrm.files]/patchwork/` directory.

## What happens when I upgrade core?

The patched files will be recreated! Before using a patched file its mtime is
compared to the mtime of the core file, and if the patched file is older then it
gets recreated.

## How do I manually recreate my patched files?

Just delete the files in `[civicrm.files]/patchwork/`


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

### License

The extension is licensed under [AGPL-3.0](LICENSE.txt).
