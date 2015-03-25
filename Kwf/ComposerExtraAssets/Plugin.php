<?php
namespace Kwf\ComposerExtraAssets;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

use Kwf\ComposerExtraAssets\VersionMatcher;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'post-install-cmd' => array(
                array('onPostUpdateInstall', 0)
            ),
            'post-update-cmd' => array(
                array('onPostUpdateInstall', 0)
            ),
        );
    }

    public function onPostUpdateInstall(Event $event)
    {
        $this->_installNpm('.', $this->composer->getPackage(), $event->isDevMode());
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        foreach ($packages as $package){
            if ($package instanceof \Composer\Package\CompletePackage) {

                $this->_installNpm($this->composer->getConfig()->get('vendor-dir') . '/' .$package->getName(), $package, false);
            }
        }

        $requireBower = array();

        if ($event->isDevMode()) {
            $extra = $this->composer->getPackage()->getExtra();
            if (isset($extra['require-dev-bower'])) {
                foreach ($extra['require-dev-bower'] as $packageName => $versionConstraint) {
                    if (isset($requireBower[$packageName])) {
                        $v = VersionMatcher::matchVersions($requireBower[$packageName], $versionConstraint);
                        if ($v === false) {
                            throw new \Exception("{$package->getName()} requires $packageName '$versionConstraint' but we have already incompatible '{$requireBower[$packageName]}'");
                        }
                    } else {
                        $v = $versionConstraint;
                    }
                    $requireBower[$packageName] = $v;
                }
            }
        }

        $packages = array(
            $this->composer->getPackage()
        );
        $packages = array_merge($packages, $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages());
        foreach ($packages as $package){
            if ($package instanceof \Composer\Package\CompletePackage) {
                $extra = $package->getExtra();
                if (isset($extra['require-bower'])) {
                    foreach ($extra['require-bower'] as $packageName => $versionConstraint) {
                        if (isset($requireBower[$packageName])) {
                            $v = VersionMatcher::matchVersions($requireBower[$packageName], $versionConstraint);
                            if ($v === false) {
                                throw new \Exception("{$package->getName()} requires $packageName '$versionConstraint' but we have already incompatible '{$requireBower[$packageName]}'");
                            }
                        } else {
                            $v = $versionConstraint;
                        }
                        $requireBower[$packageName] = $v;
                    }
                }
            }
        }

        if ($requireBower) {
            $out = array();
            $retVar = null;
            exec("bower --version 2>&1", $out, $retVar);
            if ($retVar) {
                //bower isn't installed globally, install locally
                $dir = $this->composer->getConfig()->get('vendor-dir').'/koala-framework/composer-extra-assets';
                $this->_installNpmDependencies($dir, array(
                    'bower' => '*'
                ));
                $node = $this->composer->getConfig()->get('bin-dir').'/node';
                $bowerBin = "$node ".$dir . "/node_modules/bower/bin/bower";
            } else {
                $bowerBin = 'bower';
            }

            if (file_exists('bower.json')) {
                $p = json_decode(file_get_contents('bower.json'), true);
                if ($p['name'] != 'temp-composer-extra-asssets') { //assume we can overwrite our own temp one
                    throw new \Exception("Can't install npm dependencies as there is already a bower.json");
                }
            }
            $packageJson = array(
                'name' => 'temp-composer-extra-asssets',
                'repository' => array('type'=>'git'),
                'dependencies' => $requireBower,
            );
            file_put_contents('bower.json', json_encode($packageJson));
            if (!file_exists('.bowerrc')) {
                $vd = $this->composer->getConfig()->get('vendor-dir');
                if (substr($vd, 0, strlen(getcwd())) == getcwd()) {
                    //make vendor-dir relative go cwd
                    $vd = substr($vd, strlen(getcwd())+1);
                }
                $config = array(
                    'directory' => $vd . '/bower_components'
                );
                file_put_contents('.bowerrc', json_encode($config));
            }
            $this->io->write("");
            $this->io->write("installing bower dependencies...");

            $cmd = "$bowerBin --allow-root install";
            passthru($cmd, $retVar);
            if ($retVar) {
                throw new \RuntimeException('bower install failed');
            }

            $cmd = "$bowerBin --allow-root prune";
            passthru($cmd, $retVar);
            if ($retVar) {
                throw new \RuntimeException('bower prune failed');
            }
        }
    }

    private function _installNpm($path, $package, $devMode)
    {
        $dependencies = array();

        $extra = $package->getExtra();
        if ($devMode) {
            if (isset($extra['require-dev-npm']) && count($extra['require-dev-npm'])) {
                $dependencies = array_merge($dependencies, $extra['require-dev-npm']);
            }

        }

        if (isset($extra['require-npm']) && count($extra['require-npm'])) {
            $dependencies = array_merge($dependencies, $extra['require-npm']);
        }

        if ($dependencies) {
            $this->_installNpmDependencies($path, $dependencies);
        }
    }

    private function _installNpmDependencies($path, $dependencies)
    {
        $prevCwd = getcwd();
        chdir($path);
        if (file_exists('package.json')) {
            $p = json_decode(file_get_contents('package.json'), true);
            if ($p['name'] != 'temp-composer-extra-asssets') { //assume we can overwrite our own temp one
                throw new \Exception("Can't install npm dependencies as there is already a package.json");
            }
        }
        $packageJson = array(
            'name' => 'temp-composer-extra-asssets',
            'description' => ' ',
            'readme' => ' ',
            'repository' => array('type'=>'git'),
            'dependencies' => $dependencies,
        );
        file_put_contents('package.json', json_encode($packageJson));
        $this->io->write("");
        $this->io->write("installing npm dependencies in '$path'...");
        $npm = $this->composer->getConfig()->get('bin-dir').'/npm';
        $cmd = "$npm install";
        passthru($cmd, $retVar);
        if ($retVar) {
            throw new \RuntimeException('npm install failed');
        }

        $cmd = "$npm prune";
        passthru($cmd, $retVar);
        if ($retVar) {
            throw new \RuntimeException('npm prune failed');
        }

        unlink('package.json');
        chdir($prevCwd);
    }
}

