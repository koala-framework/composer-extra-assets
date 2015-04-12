<?php
namespace Kwf\ComposerExtraAssets;

use Version\Constraint;

/**
 * Class in charge of writing links in the bin directory. 
 */
class LinkWriter
{
	private $binaryDir;
	
	/**
	 * 
	 * @param string $binaryDir The path to the binary directory.
	 */
	public function __construct($binaryDir) {
		$this->binaryDir = $binaryDir;
	}
	
    /**
     * Writes a shortcut to the target link in the vendor directory.
     *
	 * @param string $target
	 */
    public function writeLink($target)
    {
    	$fileName = basename($target);
    	$realTarget = realpath($target);
    	$relativePathToTarget = $this->makePathRelative(dirname($realTarget), $this->binaryDir).basename($realTarget);
    	
    	// If Windows cmd link
    	if (pathinfo($target, PATHINFO_EXTENSION) == "cmd") {
    		$this->writeWindows($fileName, $relativePathToTarget);
    	} else {
    		$this->writeBash($fileName, $relativePathToTarget);
    	}
    }
    
    private function writeWindows($fileName, $relativePathToTarget) {
    	$fileContent = <<<EOT
@ECHO OFF
    	
SET PATH=%%~dp0;%%PATH%%
    	
%%~dp0%s %%*
EOT;
    	$completePath = $this->binaryDir.DIRECTORY_SEPARATOR.$fileName;
    	file_put_contents($completePath, sprintf($fileContent, str_replace('/', '\\', $relativePathToTarget)));
    }

    private function writeBash($fileName, $relativePathToTarget) {
    	$fileContent = <<<EOT
#!/bin/bash

DIR=\$( cd "\$( dirname "\${BASH_SOURCE[0]}" )" && pwd )
    
export PATH=\$DIR:\$PATH
    
\$DIR/%s $@
EOT;
    	$completePath = $this->binaryDir.DIRECTORY_SEPARATOR.$fileName;
    	file_put_contents($completePath, sprintf($fileContent, $relativePathToTarget));
    	chmod($completePath, 0755);
    }
    
    /**
     * Given an existing path, convert it to a path relative to a given starting path.
     * Function borrowed to Symfony's Filesystem. Thanks pals.
     *
     * @param string $endPath   Absolute path of target
     * @param string $startPath Absolute path where traversal begins
     *
     * @return string Path of target relative to starting path
     */
    private function makePathRelative($endPath, $startPath)
    {
    	// Normalize separators on Windows
    	if ('\\' === DIRECTORY_SEPARATOR) {
    		$endPath = strtr($endPath, '\\', '/');
    		$startPath = strtr($startPath, '\\', '/');
    	}
    	// Split the paths into arrays
    	$startPathArr = explode('/', trim($startPath, '/'));
    	$endPathArr = explode('/', trim($endPath, '/'));
    	// Find for which directory the common path stops
    	$index = 0;
    	while (isset($startPathArr[$index]) && isset($endPathArr[$index]) && $startPathArr[$index] === $endPathArr[$index]) {
    		$index++;
    	}
    	// Determine how deep the start path is relative to the common path (ie, "web/bundles" = 2 levels)
    	$depth = count($startPathArr) - $index;
    	// Repeated "../" for each level need to reach the common path
    	$traverser = str_repeat('../', $depth);
    	$endPathRemainder = implode('/', array_slice($endPathArr, $index));
    	// Construct $endPath from traversing to the common path, then to the remaining $endPath
    	$relativePath = $traverser.('' !== $endPathRemainder ? $endPathRemainder.'/' : '');
    	return '' === $relativePath ? './' : $relativePath;
    }
    
}
