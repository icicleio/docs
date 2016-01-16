#!/usr/bin/env php
<?php
/*
 * Generates a configuration file for MkDocs to generate the appropriate
 * documentation HTML.
 */
const API_DIR = __DIR__ . '/docs/api';
const CONF_TEMPLATE = __DIR__ . '/mkdocs.yml.tpl';
const CONF_FILE = __DIR__ . '/mkdocs.yml';


/**
 * Creates a YAML page entry string.
 */
function createPageEntry($indent, $name, $path = false)
{
    if ($path) {
        return sprintf("%s- %s: '%s'\n", str_repeat(" ", $indent * 4), ucfirst($name), $path);
    }

    return sprintf("%s- %s:\n", str_repeat(" ", $indent * 4), ucfirst($name));
}

/**
 * Generates the document tree for the API documentation.
 */
function generateApiTree()
{
    $str = "";

    // Get each directory representing a package.
    $dirIter = new CallbackFilterIterator(
        new GlobIterator(API_DIR . "/*"),
        function ($current, $key, $iter) {
            return !$iter->isDot() && $current->isDir();
        }
    );

    foreach ($dirIter as $dir) {
        $package = "Icicle\\" . $dir->getBasename();
        $str .= createPageEntry(1, $package);

        // If the package has an index file, link it first.
        if (is_file($dir->getPathname() . "/index.md")) {
            $str .= createPageEntry(2, $package, substr($dir->getPathname(), strlen(API_DIR) - 3) . "/index.md");
        }

        // Get all files in the package.
        $fileIter = new CallbackFilterIterator(
            new GlobIterator($dir->getPathname() . "/*.md"),
            function ($current, $key, $iter) {
                return $current->isFile();
            }
        );

        foreach ($fileIter as $file) {
            // Skip the index file, since we did it earlier.
            if ($file->getBasename() === "index.md") {
                continue;
            }

            $name = str_replace(".", "\\", substr($file->getBasename(), 0, -3));
            $str .= createPageEntry(
                2,
                $name,
                substr($file->getPathname(), strlen(API_DIR) - 3)
            );
        }
    }

    return $str;
}

function main()
{
    $conf = file_get_contents(CONF_TEMPLATE);

    $replacements = [
        "API_DOCS" => generateApiTree()
    ];

    foreach ($replacements as $key => $value) {
        $conf = str_replace("%$key", "\n$value", $conf);
    }

    file_put_contents(CONF_FILE, $conf);
}


main();
