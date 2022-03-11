<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

$srcFolder = __DIR__ . '/source';

// Exclude static folders
$excludes = ['_static', 'images'];

// Safety prefix
$prefix = 'old_';

// Begin user info
echo 'The following sections were renumerated:', PHP_EOL;

// Loop source directory
$srcIterator = new DirectoryIterator($srcFolder);

foreach ($srcIterator as $chapterInfo) {
    if (! $chapterInfo->isDot() && $chapterInfo->isDir() && ! in_array($chapterInfo->getFilename(), $excludes, true)) {
        $chapterName = $chapterInfo->getFilename();

        // Iterate the working directory
        $chapterIterator = new DirectoryIterator($chapterInfo->getPathname());

        foreach ($chapterIterator as $sectionInfo) {
            if (! $sectionInfo->isDot() && $sectionInfo->isFile() && $sectionInfo->getExtension() === 'rst') {
                $sectionName   = $sectionInfo->getBasename('.rst');
                $sectionFolder = $sectionInfo->getPath() . '/' . $sectionName;

                // Read the section file
                $sectionContent = file_get_contents($sectionInfo->getPathname());

                // Match all includes
                preg_match_all("~\\.\\. literalinclude:: {$sectionName}\\/(.+)\\.php~", $sectionContent, $matches);
                $includeStrings = $matches[0];
                $exampleNames   = $matches[1];

                // Exit early if no matches
                if (count($exampleNames) === 0) {
                    continue;
                }

                // Check if examples are consecutive
                $consecutive = true;

                foreach ($exampleNames as $exampleIndex => $exampleName) {
                    if (str_pad($exampleIndex + 1, 3, '0', STR_PAD_LEFT) !== $exampleName) {
                        $consecutive = false;
                        break;
                    }
                }

                // Exit if examples are already consecutive
                if ($consecutive) {
                    continue;
                }

                // Rename all example files to avoid conflicts
                $exampleIterator = new DirectoryIterator($sectionFolder);

                foreach ($exampleIterator as $exampleInfo) {
                    if (! $exampleInfo->isDot() && $exampleInfo->isFile() && $exampleInfo->getExtension() === 'php') {
                        rename($exampleInfo->getPathname(), $exampleInfo->getPath() . '/' . $prefix . $exampleInfo->getFilename());
                    }
                }
                $sectionContent = preg_replace("~\\.\\. literalinclude:: {$sectionName}\\/(.+)\\.php~", ".. literalinclude:: {$sectionName}/{$prefix}$1.php", $sectionContent);

                // Renumerate examples
                foreach ($exampleNames as $exampleIndex => $exampleName) {
                    $newName = str_pad($exampleIndex + 1, 3, '0', STR_PAD_LEFT);

                    // Rename example file
                    rename($sectionFolder . '/' . $prefix . $exampleName . '.php', $sectionFolder . '/' . $newName . '.php');

                    // Fix include link
                    $sectionContent = preg_replace('~' . preg_quote(str_replace($exampleName, $prefix . $exampleName, $includeStrings[$exampleIndex]), '~') . '~', str_replace($exampleName, $newName, $includeStrings[$exampleIndex]), $sectionContent, 1, $count);
                }

                // Write new content to rst
                file_put_contents($sectionInfo->getPathname(), $sectionContent);

                // User info
                echo $chapterName, '/', $sectionName, PHP_EOL;
            }
        }
    }
}

// End user info
echo 'Renumerating finished.', PHP_EOL;
