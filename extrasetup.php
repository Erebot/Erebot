<?php
/**
 * This file is used to provide extra files/packages outside package.xml
 * More information: http://pear.php.net/manual/en/pyrus.commands.package.php#pyrus.commands.package.extrasetup
 */

$targets    =   (int) $options['phar']  +
                (int) $options['tgz']   +
                (int) $options['tar']   +
                (int) $options['zip'];

if ($targets != 1) {
    echo    "Don't try to be smart about creating multiple " .
            "types of packages at once, I won't let you!" . PHP_EOL;
    exit(-1);
}

$extrafiles = array();
include(
    __DIR__ .
    DIRECTORY_SEPARATOR . 'buildenv' .
    DIRECTORY_SEPARATOR . 'extrafiles.php'
);
$deps = array(
    'pear.erebot.net/Erebot_API',
);

if ($options['phar']) {
    // Only for ".phar" packages.
    $deps = array_unique(
        array_merge(
            $deps,
            array(
#                'pear.erebot.net/Erebot_Module_IrcConnector',
#                'pear.erebot.net/Erebot_Module_AutoConnect',
#                'pear.erebot.net/Erebot_Module_PingReply',
                'pear.erebot.net/Plop',
                'composer/composer',
            )
        )
    );
}

// Only for ".phar" packages (ignored for other types).
$pearDeps = array(
    'pear.php.net/Console_CommandLine',
    'pear.php.net/File_Gettext',
);

// Add (data & php) files from dependencies
// in the vendor/ directory.
foreach ($deps as $dep) {
    echo PHP_EOL . "Adding files from $dep" . PHP_EOL;
    list($channel, $dep) = explode('/', $dep, 2);
    if ("$channel/$dep" == 'pear.erebot.net/Erebot_API' &&
        file_exists('scripts' . DIRECTORY_SEPARATOR . 'Erebot_API')) {
        fprintf(STDERR, "%s", "WARNING: Using local API override" . PHP_EOL);
        $depDir = 'scripts' .
                    DIRECTORY_SEPARATOR . 'Erebot_API' .
                    DIRECTORY_SEPARATOR;
    }
    else {
        $depDir = 'vendor' .
                    DIRECTORY_SEPARATOR . $dep .
                    DIRECTORY_SEPARATOR;
    }

    foreach (array('data' => 'data', 'src' => 'php') as $dir => $type) {
        try {
            $iter = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $depDir . $dir . DIRECTORY_SEPARATOR,
                    FilesystemIterator::SKIP_DOTS           |
                    FilesystemIterator::UNIX_PATHS          |
                    FilesystemIterator::CURRENT_AS_SELF
                )
            );
        }
        catch (Exception $e) {
            continue;
        }

        $targetDir =
            $type . '/' .
            ($type == 'data' ? $channel . '/' . $dep . '/' : '');

        foreach ($iter as $file) {
            $sourceFile = $file->getPathname();
            $targetFile = $targetDir . $file->getSubPathName();
            $padding = str_repeat(
                " ",
                max(1, strlen($sourceFile) - strlen($targetFile) - 2)
            );
            echo "\t$sourceFile" . PHP_EOL .
                "\t=>$padding$targetFile" . PHP_EOL;
            $extrafiles[$targetDir . $file->getSubPathName()] =
                str_replace(
                    '/',
                    DIRECTORY_SEPARATOR,
                    $file->getPathname()
                );
        }
    }
}

if (!$options['phar'])
    return;

/**
 * This class is required due to File_Gettext
 * trying to flock() files, which fails when
 * Erebot is run from a .phar.
 *
 * This class replaces occurrences of "flock"
 * with "array" in the file's content.
 */
class       Erebot_Packaging_Pear
implements  \Pyrus\PackageInterface
{
    public $packagingcontents;
    private $_contents;

    public function __construct($name, $file)
    {
        $this->packagingcontents = array(
            array('attribs' => array('name' => $name))
        );
        $this->_contents = str_ireplace(
            'flock',
            'array',
            file_get_contents($file)
        );
    }

    function getFileContents($file, $asstream = FALSE)
    {
        if (!$asstream)
            return $this->_contents;
        $fp = tmpfile();
        fwrite($fp, $this->_contents);
        fseek($fp, 0);
        return $fp;
    }

    /* We don't need to implement those. */
    function getFilePath($file) {}
    function getFrom() {}
    function isStatic() {}
    function isUpgradeable() {}
    function __call($func, $args) {}
    function getPackageFileObject() {}
    function copyTo($where) {}
    function __get($var) {}
    function __set($var, $value) {}
    function __toString() {}
    function toArray($forpackaging = false) {}
    function getValidator() {}
    function offsetExists($offset) {}
    function offsetGet($offset) {}
    function offsetSet($offset, $value) {}
    function offsetUnset($offset) {}
}

// Add (data & php) files from installed PEAR packages.
/// @FIXME: what about @*_dir@ substitutions?...
if (count($pearDeps)) {
    $prefixes = array();
    $types = array();
    foreach (array("php", "data") as $type) {
        $paddedType = str_pad($type, 5, " ", STR_PAD_RIGHT);
        $prefixes[$type] = exec("pear config-get ${type}_dir", $output, $status);
        if ($status != 0) {
            echo "Could not determine path for type '$type'" . PHP_EOL;
            exit($status);
        }
        $types[$type] = $paddedType;
    }

    foreach ($pearDeps as $pearDep) {
        echo  PHP_EOL . "Adding files from $pearDep" . PHP_EOL;
        exec("pear list-files $pearDep", $output, $status);
        list($channel, $package) = explode('/', $pearDep, 2);
        if ($status != 0) {
            echo "Could not list files for '$pearDep'" . PHP_EOL;
            exit($status);
        }

        foreach ($output as $line) {
            $type = array_search(substr($line, 0, 5), $types);
            if ($type === FALSE)
                continue;

            $file = substr($line, 5);
            $targetFile =
                $type . '/' .
                ($type == 'data' ? $channel . '/' : '') .
                str_replace(
                    DIRECTORY_SEPARATOR,
                    '/',
                    ltrim(
                        substr($file, strlen($prefixes[$type])),
                        DIRECTORY_SEPARATOR
                    )
                );

            $padding = str_repeat(" ", strlen($file) - strlen($targetFile) - 2);
            echo "\t$file" . PHP_EOL . "\t=>$padding$targetFile" . PHP_EOL;
            // Wrap the file & replace occurrences of "flock" (File_Gettext).
            $extrafiles[$targetFile] =
                new Erebot_Packaging_Pear($targetFile, $file);
        }
    }
}

// PEAR Exception...
echo PHP_EOL . "Adding parts of PEAR" . PHP_EOL;
$php_dir = exec("pear config-get php_dir", $output, $status);
if ($status != 0) {
    echo "Could not determine php_dir" . PHP_EOL;
    exit($status);
}
$targetFile = 'php/PEAR/Exception.php';
$sourceFile =
    $php_dir .
    DIRECTORY_SEPARATOR . "PEAR" .
    DIRECTORY_SEPARATOR . "Exception.php";
echo "\t$sourceFile => $targetFile" . PHP_EOL;
$extrafiles[$targetFile] = $sourceFile;

// The Dependency Injection Container is so specific...
echo PHP_EOL . "Adding Symfony's DIC" . PHP_EOL;
$iter = new DirectoryIterator(
    'vendor' .
    DIRECTORY_SEPARATOR . 'DependencyInjection' .
    DIRECTORY_SEPARATOR . 'lib'
);

foreach ($iter as $fileinfo) {
    if ($fileinfo->isDir())
        continue;

    $filename = $fileinfo->getFilename();
    if (in_array(substr($filename, -4), array('.php', '.xsd'))) {
        $targetFile = 'php/SymfonyComponents/DependencyInjection/' . $filename;
        $sourceFile = $fileinfo->getPathname();
        $padding = str_repeat(
            " ",
            max(1, strlen($sourceFile) - strlen($targetFile) - 2)
        );
        echo "\t$sourceFile" . PHP_EOL .
            "\t=>$padding$targetFile" . PHP_EOL;
        $extrafiles[$targetFile] = $sourceFile;
    }
}

