<?php
namespace Kwf\ComposerExtraAssets;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        exec('which npm', $out, $retVar);
        if ($retVar) {
            throw new \Exception("Can't find npm, not in path");
        }
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
        $this->_installNpm('.', $this->composer->getPackage());
        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        foreach($packages as $package){
            if ($package instanceof \Composer\Package\CompletePackage) {
                $this->_installNpm('vendor/'.$package->getName(), $package);
            }
        }

        $requireBower = array();
        $packages = array(
            $this->composer->getPackage()
        );
        $packages = array_merge($packages, $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages());
        foreach($packages as $package){
            if ($package instanceof \Composer\Package\CompletePackage) {
                $extra = $package->getExtra();
                if (isset($extra['require-bower'])) {
                    foreach ($extra['require-bower'] as $packageName => $versionConstraint) {
                        if (isset($requireBower[$packageName]) && $requireBower[$packageName] != $versionConstraint) {
                            $this->io->write("<error>ERROR: {$package->getName()} requires $packageName $versionConstraint but we have already {$requireBower[$packageName]}</error>");
                        } else {
                            $requireBower[$packageName] = $versionConstraint;
                        }
                    }
                }
            }
        }

        if ($requireBower) {
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
            $config = array(
                'directory' => 'vendor/bower_components'
            );
            file_put_contents('.bowerrc', json_encode($config));
            $this->io->write("");
            $this->io->write("installing bower dependencies...");
            $cmd = "vendor/koala-framework/composer-extra-assets/node_modules/.bin/bower install";
            passthru($cmd, $retVar);
            if ($retVar) {
                $this->io->write("<error>bower install failed</error>");
            }
            unlink('bower.json');
            unlink('.bowerrc');
        }
    }

    private function _installNpm($path, $package)
    {
        $extra = $package->getExtra();
        if (isset($extra['require-npm']) && count($extra['require-npm'])) {
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
                'dependencies' => $extra['require-npm'],
            );
            file_put_contents('package.json', json_encode($packageJson));
            $this->io->write("");
            $this->io->write("installing npm dependencies in '$path'...");
            $cmd = "npm install";
            passthru($cmd, $retVar);
            if ($retVar) {
                $this->io->write("<error>npm install failed</error>");
            }
            unlink('package.json');
            chdir($prevCwd);
        }
    }
}
