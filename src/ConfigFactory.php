<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use FilesystemIterator;
use Ghostwriter\Config\Exception\ConfigDirectoryNotFoundException;
use Ghostwriter\Config\Exception\ConfigFileNotFoundException;
use Ghostwriter\Config\Exception\EmptyConfigKeyException;
use Ghostwriter\Config\Exception\InvalidConfigFileException;
use Ghostwriter\Config\Exception\InvalidConfigKeyException;
use Ghostwriter\Config\Exception\ShouldNotHappenException;
use Ghostwriter\Config\Interface\ConfigFactoryInterface;
use Ghostwriter\Config\Interface\ConfigInterface;
use Ghostwriter\Config\Interface\ConfigProviderInterface;
use Override;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

use const DIRECTORY_SEPARATOR;

use function basename;
use function explode;
use function is_array;
use function is_dir;
use function is_file;
use function mb_trim;
use function str_replace;

final readonly class ConfigFactory implements ConfigFactoryInterface
{
    public static function new(): ConfigFactoryInterface
    {
        return new self();
    }

    /**
     * @param array<string,mixed> $config
     *
     * @throws EmptyConfigKeyException
     * @throws InvalidConfigKeyException
     */
    #[Override]
    public function create(array $config = []): ConfigInterface
    {
        return Config::new($config);
    }

    /**
     * @throws ShouldNotHappenException
     * @throws ConfigDirectoryNotFoundException
     * @throws EmptyConfigKeyException
     * @throws InvalidConfigKeyException
     */
    #[Override]
    public function createFromDirectory(string $configDirectory): ConfigInterface
    {
        if ('' === mb_trim($configDirectory)) {
            throw new ShouldNotHappenException('Invalid config directory, empty string provided.');
        }

        if (! is_dir($configDirectory)) {
            throw new ConfigDirectoryNotFoundException($configDirectory);
        }

        /** @var RegexIterator<SplFileInfo> $phpFiles */
        $phpFiles = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($configDirectory, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            ),
            '#\.php$#iu',
            RegexIterator::MATCH
        );

        /** @var array<string,mixed> $configs */
        $configs = [];

        /** @var SplFileInfo $phpFile */
        foreach ($phpFiles as $phpFile) {
            $path = $phpFile->getPathname();

            $current = &$configs;

            $keys = explode(
                DIRECTORY_SEPARATOR,
                mb_trim(str_replace([$configDirectory, '.php'], '', $path), DIRECTORY_SEPARATOR)
            );

            foreach ($keys as $key) {
                $current[$key] ??= [];
                $current = &$current[$key];
            }

            $current = $this->createFromFile($path);
        }

        return $this->create($configs);
    }

    /**
     * @throws ConfigFileNotFoundException
     * @throws InvalidConfigFileException
     * @throws ShouldNotHappenException
     * @throws EmptyConfigKeyException
     * @throws InvalidConfigKeyException
     */
    #[Override]
    public function createFromFile(string $configFile): ConfigInterface
    {
        if ('' === mb_trim($configFile)) {
            throw new ShouldNotHappenException('Invalid config file, empty string provided.');
        }

        if (! is_file($configFile)) {
            throw new ConfigFileNotFoundException($configFile);
        }

        $key = basename($configFile, '.php');

        /** @var null|array<string,mixed>|ConfigProviderInterface $options */
        $options = require $configFile;

        if ($options instanceof ConfigProviderInterface) {
            $config = Config::new();

            $options($config);

            return $this->create([
                $key => $config->toArray(),
            ]);
        }

        if (! is_array($options)) {
            throw new InvalidConfigFileException($configFile);
        }

        /** @var array<string,mixed> $options */
        $options = [
            $key => $options,
        ];

        return $this->create($options);
    }
}
