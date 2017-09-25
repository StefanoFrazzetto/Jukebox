<?php

namespace Lib;


use Symfony\Component\Finder\Finder;

class UpdaterManager
{
    /** @var */
    const MIGRATION_FILE_NAME_PATTERN = '/^\d+_([\w_]+).json/i';

    const MIGRATION_TABLE_NAME = 'updater_migrations';

    /** @var string|null  the directory containing the migrations */
    private $migrationsPath;

    public function __construct()
    {
        $migrations_path = Config::getPath('updater_migrations');

        if (is_null($migrations_path)) {
            throw new \Exception('Updater: cannot find the migrations directory path');
        }

        $this->migrationsPath = $migrations_path;
    }

    /**
     * Retrieve the migration files.
     *
     * @throws \InvalidArgumentException if duplicate migrations are found
     *
     * @return array the array containing the migrations paths sorted by version
     */
    public function getMigrationFiles()
    {
        $finder = new Finder();
        $files = $finder->in($this->migrationsPath)->files();

        $migrations = [];
        foreach ($files as $migrationPath) {
            if (self::isValidMigrationFileName(basename($migrationPath))) {
                $version = static::getVersionFromFileName($migrationPath);

                // Check for duplicate migrations
                if ($migrations[$version] === $version) {
                    throw new \InvalidArgumentException('Updater: duplicate migration found.');
                }

                $migrations[$version] = basename($migrationPath);
            }
        }

        ksort($migrations);

        return $migrations;
    }

    /**
     * Check if a migration file name is valid.
     *
     * @param string $fileName File Name
     * @return boolean
     */
    public static function isValidMigrationFileName($fileName)
    {
        $matches = array();
        return preg_match(static::MIGRATION_FILE_NAME_PATTERN, $fileName, $matches);
    }

    /**
     * Get the version from the beginning of a file name.
     *
     * @param string $fileName File Name
     * @return string
     */
    public static function getVersionFromFileName($fileName)
    {
        $matches = array();
        preg_match('/^[0-9]+/', basename($fileName), $matches);
        return $matches[0];
    }
}