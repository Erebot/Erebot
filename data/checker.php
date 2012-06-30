<?php

require(__DIR__ . DIRECTORY_SEPARATOR . 'dependency.php');

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

class Erebot_Package_Dependencies_Checker
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

    protected function _getLink($source, $target, $constraints, $description)
    {
        $constraints = str_replace(' ', '', $constraints);
        $link = new Link(
            $this->_xformPackage($source),
            $this->_xformPackage($target),
            $this->_versionParser->parseConstraints($constraints),
            $description,
            $constraints
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
            array_merge(
                array(
                    $this->_getLink(
                        "virt-Erebot",
                        "pear.erebot.net/Erebot",
                        "*",
                        "requires"
                    ),
                ),
                $this->_package->getRequires()
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
        $root = isset($metadata['pear.erebot.net/Erebot']);
        $modules = array();

        foreach ($metadata as $pkgName => $data) {
            $isErebotModule = !strncasecmp($pkgName, 'pear.erebot.net/', 16);
            if (!$root && $isErebotModule)
                $modules[] = $this->_getLink(
                    'virt-Erebot',
                    $pkgName,
                    '*',
                    'requires'
                );

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
                "replaces",
                "conflicts",
            );
            foreach ($types as $type) {
                if (isset($data[$type])) {
                    $additions = array();
                    foreach ($data[$type] as $dep => $constraints) {
                        if (is_int($dep)) {
                            $dep            = $constraints;
                            $constraints    = "*";
                        }

                        // Don't depend on (a specific version of) Pyrus.
                        if ($dep == 'pear2.php.net/pyrus')
                            continue;

                        $additions[] = $this->_getLink(
                            $pkgName,
                            $dep,
                            $constraints,
                            $type
                        );
                    }
                    $pkg->{"set".ucfirst($type)}($additions);
                }
            }
            $this->_localRepo->addPackage($pkg);
        }

        // Add modules for virt-Erebot.
        $modules = array_merge($modules, $this->_package->getRequires());
        $this->_package->setRequires($modules);
    }
}

