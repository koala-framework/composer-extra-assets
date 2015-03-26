<?php
namespace Kwf\ComposerExtraAssets;

use Version\Constraint;

class VersionMatcher
{
    /**
     * Return the higher version of $version1 and $version2 if both match. Else return false.
     *
     * @param string $version1
     * @param string $version2
     * @return string|bool
     */
    public static function matchVersions($version1, $version2)
    {
        if ($version1 == '*') {
            return $version2;
        }
        if ($version2 == '*') {
            return $version1;
        }
        if ($version1 == $version2) {
            return $version1;
        }

        $v1 = self::_normalizeVersion($version1);
        $v2 = self::_normalizeVersion($version2);

        try {
            $v1 = Constraint::parse($v1);
            $v2 = Constraint::parse($v2);
        } catch (\UnexpectedValueException $e) {
            return false;
        }

        if ($v1->isSubsetOf($v2)) {
            return $version1;
        } else if ($v2->isSubsetOf($v1)) {
            return $version2;
        } else {
            return false;
        }
    }
    private static function _normalizeVersion($v)
    {
        if (preg_match('#^(\d+\.\d+\.\d+)-(\d+)$#', $v, $m)) {
            $v = $m[1].'.'.$m[2];
        }
        return $v;
    }
}
