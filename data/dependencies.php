<?php

use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\Solver;
use Composer\DependencyResolver\SolverProblemsException;
use Composer\Package\Link;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\ArrayRepository;
use Composer\Package\MemoryPackage;
use Composer\Package\Version\VersionParser;

class Erebot_Phar_Dependency
{
    protected $_name;
    protected $_version;

    public function __construct($dependency, $allowVersion)
    {
        $opTokens   = ' <>=';
        $errorMessage = 'Invalid dependency specification';
        if (!is_string($dependency) || $dependency == '')
            throw new Exception($errorMessage);

        $dependency     = trim($dependency);
        $depNameEnd     = strcspn($dependency, $opTokens);
        $depName        = (string) substr($dependency, 0, $depNameEnd);

        if ($depName == '')
            throw new Exception($errorMessage);
        $this->_name        = $depName;

        if (!$allowVersion) {
            if ($depNameEnd != strlen($dependency))
                throw new Exception($errorMessage);
            $this->_version = '*';
            return;
        }

        $depVersion     = (string) substr($dependency, $depNameEnd);
        if (strpos($depVersion, '<<') !== FALSE ||
            strpos($depVersion, '>>') !== FALSE)
            throw new Exception($errorMessage);

        $depVersion     = strtr($depVersion, array('<=' => '<<', '>=' => '>>'));
        $depVersion     = str_replace(array('=', ' '), '', $depVersion);
        $depVersion     = strtr($depVersion, array('<<' => '<=', '>>' => '>='));
        if ($depVersion == '')
            $depVersion = NULL;
        $this->_version     = $depVersion;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getVersion()
    {
        if ($this->_version === NULL)
            return '*';
        return $this->_version;
    }

    public function __toString()
    {
        if ($this->_version === NULL)
            return $this->_name;
        return  $this->_name." ".
                $this->_version;
    }
}

class Erebot_Phar_DependencyChecker
{
    protected $_package;
    protected $_versionParser;

    public function __construct($package, $version)
    {
        $this->_package = new MemoryPackage(
            $package,
            $version . '.0',
            $version
        );
        $this->_versionParser = new VersionParser();
        $this->_localRepo = new ArrayRepository();
    }

    protected function _getLink($source, $target, $description)
    {
        $sourcePkg  = new Erebot_Phar_Dependency($source, FALSE);
        $targetPkg  = new Erebot_Phar_Dependency($target, TRUE);
        $constraint = $targetPkg->getVersion();
        $link = new Link(
            $this->_xformPackage($sourcePkg->getName()),
            $this->_xformPackage($targetPkg->getName()),
            $this->_versionParser->parseConstraints($constraint),
            $description,
            $constraint
        );
        return $link;
    }

    protected function _xformPackage($package)
    {
        if (!strcasecmp($package, "php"))
            return $package;

        if (strpos($package, "/") === FALSE) {
            if (!strncasecmp($package, "virt-", 5))
                return $package;
            throw new Exception("Unsupported package name $package");
        }

        $parts      = explode("/", $package);
        $package    = array_pop($parts);
        $path       = implode("/", $parts);
        if (!strncasecmp($path, "pecl.php.net", strlen("pecl.php.net")))
            return "ext-$package";
        return "$path/$package";
    }

    public function check(&$error)
    {
        $error = NULL;
        $versionParser = new VersionParser();
        $this->_package->setRequires(
            array(
                $this->_getLink(
                    "virt-Erebot",
                    "pear.erebot.net/Erebot",
                    "requires"
                ),
            )
        );

        // create installed repo, this contains all local packages + platform packages (php & extensions)
        $platformRepo = new PlatformRepository();
        $installedRepo = new CompositeRepository(array(
            $platformRepo,
            $this->_localRepo,
        ));

        // creating repository pool
        $pool = new Pool;
        $pool->addRepository($installedRepo);

        $request = new Request($pool);
        $links = $this->_package->getRequires();

        foreach ($links as $link) {
            $request->install($link->getTarget(), $link->getConstraint());
        }
        foreach ($platformRepo->getPackages() as $link) {
            $request->install($link->getName(), $this->_versionParser->parseConstraints('*'));
        }

        // prepare solver
        $policy = new DefaultPolicy();
        $solver = new Solver($policy, $pool, $installedRepo);

        // solve dependencies
        try {
            $operations = $solver->solve($request);
        } catch (SolverProblemsException $e) {
            $error = $e->getMessage();
            return FALSE;
        }

        if (count($operations)) {
            $error = "The following actions would be required:" . PHP_EOL;
            foreach ($operations as $operation)
                $error .= "\t- ".((string) $operation).PHP_EOL;
            return FALSE;
        }

        return TRUE;
    }

    public function handleMetadata($metadata)
    {
        foreach ($metadata as $pkgName => $data) {
            if (isset($data['path']))
                Erebot_Autoload::initialize($data['path']);

            $pkg = new MemoryPackage(
                $pkgName,
                $data['version'] . '.0',
                $data['version']
            );
            $types = array(
                "requires",
                "provides",
                "suggests",
                "recommends",
                "replaces",
                "conflicts",
            );
            foreach ($types as $type) {
                if (isset($data[$type])) {
                    $additions = array();
                    foreach ($data[$type] as $dep)
                        $additions[] = $this->_getLink($pkgName, $dep, $type);
                    $pkg->{"set".ucfirst($type)}($additions);
                }
            }
            $this->_localRepo->addPackage($pkg);
        }
    }
}

