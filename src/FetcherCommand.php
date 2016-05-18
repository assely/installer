<?php

namespace Assely\Installer\Console;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use ZipArchive;

class FetcherCommand extends Command
{
    /**
     * @var mixed
     */
    protected $url;

    /**
     * @param $directory
     */
    protected function assertDoesNotExist($directory)
    {
        if (is_dir($directory)) {
            throw new RuntimeException("Directory [{$directory}] already exsist!");
        }
    }

    /**
     * Generate a random temporary filename.
     *
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd() . '/assely_' . md5(time() . uniqid());
    }

    /**
     * Download files to the given zip.
     *
     * @param string $tempName
     *
     * @return self
     */
    protected function download($tempName)
    {
        $zipFile = "{$tempName}.zip";

        $response = (new Client)->get("https://github.com/assely/{$this->package}/archive/master.zip");

        file_put_contents($zipFile, $response->getBody());

        return $this;
    }

    /**
     * Extract created zip file.
     *
     * @param string $tempName
     * @param string $directory
     *
     * @return self
     */
    protected function extract($tempName, $directory)
    {
        $archive = new ZipArchive;

        $archive->open($tempName . '.zip');

        $archive->extractTo($tempName);

        $archive->close();

        $this->moveFiles($tempName, $directory);

        return $this;
    }

    /**
     * Move files from temporary directory to the destination directory.
     *
     * @param string $tempName
     * @param string $directory
     *
     * @return boolean
     */
    protected function moveFiles($tempName, $directory)
    {
        return rename($tempName . "/{$this->package}-master", $directory);
    }

    /**
     * Clean-up the zip file and temp directory.
     *
     * @param  string  $tempName
     *
     * @return $this
     */
    protected function cleanUp($tempName)
    {
        $zipFile = "{$tempName}.zip";

        @chmod($zipFile, 0777);
        @chmod($tempName, 0777);

        @unlink($zipFile);
        @rmdir($tempName);

        return $this;
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd() . '/composer.phar')) {
            return '"' . PHP_BINARY . '" composer.phar';
        }
        return 'composer';
    }
}
