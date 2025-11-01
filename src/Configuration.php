<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use ErrorException;
use FilesystemIterator;
use Ghostwriter\Config\Exception\ConfigurationDirectoryNotFoundException;
use Ghostwriter\Config\Exception\ConfigurationDirectoryNotReadableException;
use Ghostwriter\Config\Exception\ConfigurationFileNotFoundException;
use Ghostwriter\Config\Exception\ConfigurationFileNotReadableException;
use Ghostwriter\Config\Exception\ConfigurationFilePathResolutionException;
use Ghostwriter\Config\Exception\ConfigurationKeyMustBeNonEmptyStringException;
use Ghostwriter\Config\Exception\ConfigurationKeyMustBeStringException;
use Ghostwriter\Config\Exception\FailedToLoadConfigurationFileException;
use Ghostwriter\Config\Exception\FailedToLoadConfigurationFileWithErrorsException;
use Ghostwriter\Config\Exception\InvalidConfigurationFileException;
use Ghostwriter\Config\Exception\InvalidConfigurationKeyException;
use Ghostwriter\Config\Exception\InvalidConfigurationValueException;
use Ghostwriter\Config\Exception\InvalidDotNotationConfigurationKeyException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use Override;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_pop;
use function array_reduce;
use function array_shift;
use function array_walk;
use function get_debug_type;
use function is_array;
use function is_dir;
use function is_file;
use function is_readable;
use function is_scalar;
use function is_string;
use function iterator_to_array;
use function mb_strlen;
use function mb_substr;
use function mb_trim;
use function preg_split;
use function realpath;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function str_replace;

/**
 * @template T of (array<non-empty-string,T>|bool|float|int|null|string)
 */
final class Configuration implements ConfigurationInterface
{
    private const string KEY_SEPARATOR = '#[./\\\\]#u';

    /** @var array<non-empty-string,T> */
    private array $configuration = [];

    /**
     * @param array<non-empty-string,T> $configuration
     *
     * @throws ConfigurationExceptionInterface
     */
    public function __construct(array $configuration = [])
    {
        $this->merge($configuration);
    }

    /**
     * @param array<non-empty-string,T> $configuration
     *
     * @throws ConfigurationExceptionInterface
     */
    public static function new(array $configuration = []): self
    {
        return new self($configuration);
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function append(string $key, mixed $value): void
    {
        [$segments, $lastSegment, $normalized] = self::resolveSegmentsLastAndNormalized($key, $value);

        $operation = 'append to';

        $this->configuration = self::recurseAppend(
            $this->configuration,
            $segments,
            $lastSegment,
            $normalized,
            $key,
            $operation
        );
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        [$found, $value] = self::findValueByKeySegments($key);

        if ($found) {
            return $value;
        }

        return $default;
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function has(string $key): bool
    {
        [$found] = self::findValueByKeySegments($key);

        return $found;
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function merge(array $options): void
    {
        array_walk($options, fn (mixed $value, mixed $key) => $this->mergeSingleKeyValue($key, $value));
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function mergeDirectory(string $directory): void
    {
        $realDirectory = self::resolveRealDirectoryOrThrow($directory);

        self::assertDirectoryExists($realDirectory);

        self::assertDirectoryIsReadable($realDirectory);

        $fileList = iterator_to_array(self::createPhpFilesIterator($realDirectory));

        array_walk($fileList, function (SplFileInfo $phpFile) use ($directory, $realDirectory): void {
            $path = self::resolveFileRealPathOrThrow($phpFile, $directory);
            $configKey = self::deriveConfigurationKeyFromFilePath($realDirectory, $path);
            $this->mergeFile($path, $configKey);
        });
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function mergeFile(string $file, ?string $key = null): void
    {
        self::assertFileExists($file);
        self::assertFileIsReadable($file);

        $value = self::normalizeArray(self::requireConfigFileReturningArray($file));

        if (null === $key) {
            $this->merge($value);

            return;
        }

        $this->merge([
            $key => $value,
        ]);
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function prepend(string $key, mixed $value): void
    {
        [$segments, $lastSegment, $normalized] = self::resolveSegmentsLastAndNormalized($key, $value);

        $this->configuration = self::prependValue($this->configuration, $segments, $lastSegment, $normalized, $key);
    }

    #[Override]
    public function reset(): void
    {
        $this->configuration = [];
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function set(string $key, mixed $value): void
    {
        [$segments, $lastSegment, $normalized] = self::resolveSegmentsLastAndNormalized($key, $value);

        $this->configuration = self::setRecursively(
            $this->configuration,
            $segments,
            $lastSegment,
            $normalized,
            $key,
            'set'
        );
    }

    /** @return array<non-empty-string,T> */
    #[Override]
    public function toArray(): array
    {
        return $this->configuration;
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function unset(string $key): void
    {
        if (array_key_exists($key, $this->configuration)) {
            unset($this->configuration[$key]);

            return;
        }

        $this->configuration = self::recurseUnset($this->configuration, self::parseAndValidateKeyIntoSegments($key));
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function wrap(string $key, array $default = []): ConfigurationInterface
    {
        $configuration = $this->get($key, $default);

        if (is_array($configuration)) {
            return new self($configuration);
        }

        throw new InvalidConfigurationValueException(sprintf(
            'Cannot wrap configuration key "%s". Expected an array value, received %s.',
            $key,
            get_debug_type($configuration),
        ));
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array{0:bool,1:mixed}
     */
    private function findValueByKeySegments(string $key): array
    {
        return array_reduce(
            self::parseAndValidateKeyIntoSegments($key),
            static fn (array $result, string $segment): array => self::reduceSegmentResolution($result, $segment),
            [true, $this->configuration]
        );
    }

    /** @throws ConfigurationExceptionInterface */
    private function mergeSingleKeyValue(mixed $key, mixed $value): void
    {
        self::assertKeyIsString($key);

        [$segments, $lastSegment] = self::parseAndSplitSegments($key);

        $this->configuration = self::recurseMerge(
            $this->configuration,
            $segments,
            $lastSegment,
            self::normalizeValueForKey($key, $value)
        );
    }

    /** @return array<non-empty-string,T> */
    private function requireConfigFileReturningArray(string $file): array
    {
        return (static function (string $file): array {
            set_error_handler(
                static function (int $severity, string $message, string $file, int $line): never {
                    throw new ErrorException($message, 0, $severity, $file, $line);
                }
            );

            $configuration = null;

            try {
                $configuration = require $file;
            } catch (ErrorException $throwable) {
                throw new FailedToLoadConfigurationFileWithErrorsException(
                    message: sprintf('Failed to load config file: %s', $file),
                    previous: $throwable
                );
            } catch (Throwable $throwable) {
                throw new FailedToLoadConfigurationFileException(
                    message: sprintf('Failed to load config file: %s', $file),
                    previous: $throwable
                );
            } finally {
                restore_error_handler();
            }

            self::assertConfigurationFileReturnsArray($configuration, $file);

            /** @var array<non-empty-string,T> $configuration */
            return $configuration;
        })($file);
    }

    private function resolveFileRealPathOrThrow(SplFileInfo $phpFile, string $directory): string
    {
        $path = $phpFile->getRealPath();

        if (false === $path) {
            throw new ConfigurationFilePathResolutionException(sprintf(
                'Failed to get real path for "%s" file in directory "%s".',
                $phpFile->getPathname(),
                $directory
            ));
        }

        return $path;
    }

    private function resolveRealDirectoryOrThrow(string $directory): string
    {
        $realDirectory = realpath($directory);

        if (false === $realDirectory) {
            throw new ConfigurationDirectoryNotFoundException(sprintf(
                'Config directory "%s" cannot be resolved to a real path.',
                $directory
            ));
        }

        return $realDirectory;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function addNormalizedNonStringKeyValue(array $carry, int|string $key, mixed $value): array
    {
        $carry[$key] = self::normalizeNonStringKeyValue($value);

        return $carry;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function addNormalizedStringKeyValue(array $carry, string $key, mixed $value): array
    {
        $carry[$key] = self::normalizeValueForKey($key, $value);

        return $carry;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function appendLeaf(array $node, string $lastSegment, mixed $normalized): array
    {
        $existing = $node[$lastSegment] ?? null;

        $node[$lastSegment] = self::mergeLists(
            self::convertValueToList($existing),
            self::convertValueToList($normalized)
        );

        return $node;
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function appendNonLeaf(
        array $node,
        array $segments,
        string $lastSegment,
        mixed $normalized,
        string $fullKey,
        string $operation
    ): array {
        $segment = array_shift($segments);
        $child = self::extractChildArrayOrThrow($node, $segment, $fullKey, $operation);
        $node[$segment] = self::recurseAppend($child, $segments, $lastSegment, $normalized, $fullKey, $operation);

        return $node;
    }

    /** @param list<string> $segments */
    private static function assertAllSegmentsAreNonEmpty(array $segments, string $key): void
    {
        if (! empty(array_filter($segments, static fn (string $segment): bool => '' === mb_trim($segment)))) {
            throw new InvalidDotNotationConfigurationKeyException($key);
        }
    }

    /**
     * @param mixed  $configuration
     * @param string $file
     */
    private static function assertConfigurationFileReturnsArray(mixed $configuration, string $file): void
    {
        if (! is_array($configuration)) {
            throw new InvalidConfigurationFileException(
                sprintf('Config file "%s" does not return a valid configuration array.', $file)
            );
        }
    }

    private static function assertDirectoryExists(string $realDirectory): void
    {
        if (! is_dir($realDirectory)) {
            throw new ConfigurationDirectoryNotFoundException(sprintf(
                'Config directory "%s" not found.',
                $realDirectory
            ));
        }
    }

    private static function assertDirectoryIsReadable(string $realDirectory): void
    {
        if (! is_readable($realDirectory)) {
            throw new ConfigurationDirectoryNotReadableException(sprintf(
                'Config directory "%s" is not readable.',
                $realDirectory
            ));
        }
    }

    private static function assertFileExists(string $file): void
    {
        if (! is_file($file)) {
            throw new ConfigurationFileNotFoundException(sprintf('Config file "%s" not found.', $file));
        }
    }

    private static function assertFileIsReadable(string $file): void
    {
        if (! is_readable($file)) {
            throw new ConfigurationFileNotReadableException(sprintf('Config file "%s" is not readable.', $file));
        }
    }

    /** @throws ConfigurationExceptionInterface */
    private static function assertKeyIsNotEmpty(string $key): void
    {
        if ('' === mb_trim($key)) {
            throw new ConfigurationKeyMustBeNonEmptyStringException();
        }
    }

    /** @param mixed $key */
    private static function assertKeyIsString(mixed $key): void
    {
        if (! is_string($key)) {
            throw new ConfigurationKeyMustBeStringException();
        }
    }

    /** @param list<string> $segments */
    private static function assertSegmentsListIsNotEmpty(array $segments, string $key): void
    {
        if (empty($segments)) {
            throw new InvalidDotNotationConfigurationKeyException($key);
        }
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return list<mixed>
     */
    private static function convertValueToList(mixed $value): array
    {
        if (null === $value) {
            return [];
        }

        if (is_array($value)) {
            return self::normalizeArray($value);
        }

        return [$value];
    }

    /** @return RegexIterator<SplFileInfo> */
    private static function createPhpFilesIterator(string $realDirectory): RegexIterator
    {
        return new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($realDirectory, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            ),
            '#\.php$#iu',
            RegexIterator::MATCH
        );
    }

    private static function deriveConfigurationKeyFromFilePath(string $realDirectory, string $path): string
    {
        $baseLen = mb_strlen($realDirectory);

        $relative = mb_substr($path, $baseLen + 1, -4);

        return str_replace(DIRECTORY_SEPARATOR, '.', mb_trim($relative, DIRECTORY_SEPARATOR));
    }

    /** @return array<non-empty-string,mixed> */
    private static function extractChildArrayOrThrow(
        array $node,
        string $segment,
        string $fullKey,
        string $operation
    ): array {
        if (! array_key_exists($segment, $node)) {
            return [];
        }

        $value = $node[$segment];

        if (null === $value) {
            return [];
        }

        if (! is_array($value)) {
            throw new InvalidConfigurationKeyException(sprintf(
                'Cannot %s configuration key "%s". Segment "%s" is already set to a non-array value of type %s.',
                $operation,
                $fullKey,
                $segment,
                get_debug_type($value),
            ));
        }

        return $value;
    }

    private static function isPermittedScalarOrNull(mixed $value): bool
    {
        return null === $value || is_scalar($value);
    }

    /** @return array<non-empty-string,T> */
    private static function mergeLeaf(array $node, string $lastSegment, mixed $normalized): array
    {
        $existing = $node[$lastSegment] ?? null;

        $node[$lastSegment] = self::mergeLeafValue($existing, $normalized);

        return $node;
    }

    private static function mergeLeafValue(mixed $existing, mixed $normalized): mixed
    {
        if (! is_array($normalized)) {
            return $normalized;
        }

        if (! is_array($existing)) {
            return $normalized;
        }

        return array_merge($existing, $normalized);
    }

    /** @return list<mixed> */
    private static function mergeLists(array $left, array $right): array
    {
        return array_merge($left, $right);
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @return array<non-empty-string,T>
     */
    private static function mergeNonLeaf(array $node, array $segments, string $lastSegment, mixed $normalized): array
    {
        $segment = array_shift($segments);

        $value =$node[$segment] ?? [];

        if (! is_array($value)) {
            $value = [];
        }

        $node[$segment] = self::recurseMerge($value, $segments, $lastSegment, $normalized);

        return $node;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function normalizeArray(array $configuration): array
    {
        return array_reduce(
            array_keys($configuration),
            static fn (array $carry, int|string $key): array => self::normalizeArrayKeyValue(
                $carry,
                $key,
                $configuration[$key]
            ),
            []
        );
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeArrayKeyValue(array $carry, int|string $key, mixed $value): array
    {
        if (is_string($key)) {
            return self::addNormalizedStringKeyValue($carry, $key, $value);
        }

        return self::addNormalizedNonStringKeyValue($carry, $key, $value);
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeNonConfigurationNonStringValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return self::normalizeArray($value);
        }

        return $value;
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeNonConfigurationValue(string $key, mixed $value): mixed
    {
        if (is_array($value)) {
            return self::normalizeArray($value);
        }

        return self::normalizeScalarOrNullOrThrow($key, $value);
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeNonStringKeyValue(mixed $value): mixed
    {
        if ($value instanceof ConfigurationInterface) {
            return $value->toArray();
        }

        return self::normalizeNonConfigurationNonStringValue($value);
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeScalarOrNullOrThrow(string $key, mixed $value): mixed
    {
        if (self::isPermittedScalarOrNull($value)) {
            return $value;
        }

        throw new InvalidConfigurationValueException(sprintf(
            'Invalid configuration value for key "%s". Expected: array, null, or scalar (bool, float, int, string). Received: %s.',
            $key,
            get_debug_type($value),
        ));
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeValueForKey(string $key, mixed $value): mixed
    {
        if ($value instanceof ConfigurationInterface) {
            return $value->toArray();
        }

        return self::normalizeNonConfigurationValue($key, $value);
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array{0:list<non-empty-string>,1:non-empty-string}
     */
    private static function parseAndSplitSegments(string $key): array
    {
        $segments = self::parseAndValidateKeyIntoSegments($key);

        $lastSegment = array_pop($segments);

        return [$segments, $lastSegment];
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return list<non-empty-string>
     */
    private static function parseAndValidateKeyIntoSegments(string $key): array
    {
        self::assertKeyIsNotEmpty($key);

        $segments = self::splitKeyIntoSegments($key);

        self::assertSegmentsListIsNotEmpty($segments, $key);

        self::assertAllSegmentsAreNonEmpty($segments, $key);

        return $segments;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function prependLeaf(array $node, string $lastSegment, mixed $normalized): array
    {
        $existing = $node[$lastSegment] ?? [];

        $node[$lastSegment] = self::mergeLists(
            self::convertValueToList($normalized),
            self::convertValueToList($existing)
        );

        return $node;
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $key
     *
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function prependNonLeaf(
        array $node,
        array $segments,
        string $key,
        mixed $value,
        string $fullKey,
        string $operation
    ): array {
        $segment = array_shift($segments);
        $child = self::extractChildArrayOrThrow($node, $segment, $fullKey, $operation);

        if (empty($segments)) {
            $node[$segment] = self::prependLeaf($child, $key, $value);

            return $node;
        }

        $node[$segment] = self::prependNonLeaf($child, $segments, $key, $value, $fullKey, $operation);

        return $node;
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function prependValue(
        array $configuration,
        array $segments,
        string $lastSegment,
        mixed $normalized,
        string $key
    ): array {
        if (empty($segments)) {
            return self::prependLeaf($configuration, $lastSegment, $normalized);
        }

        return self::prependNonLeaf($configuration, $segments, $lastSegment, $normalized, $key, 'prepend to');
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function recurseAppend(
        array $node,
        array $segments,
        string $lastSegment,
        mixed $normalized,
        string $fullKey,
        string $operation
    ): array {
        if (empty($segments)) {
            return self::appendLeaf($node, $lastSegment, $normalized);
        }

        return self::appendNonLeaf($node, $segments, $lastSegment, $normalized, $fullKey, $operation);
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @return array<non-empty-string,T>
     */
    private static function recurseMerge(array $node, array $segments, string $lastSegment, mixed $normalized): array
    {
        if (empty($segments)) {
            return self::mergeLeaf($node, $lastSegment, $normalized);
        }

        return self::mergeNonLeaf($node, $segments, $lastSegment, $normalized);
    }

    /**
     * @param list<non-empty-string> $segments
     *
     * @return array<non-empty-string,T>
     */
    private static function recurseUnset(array $node, array $segments): array
    {
        $segment = array_shift($segments);

        return self::unsetHereOrDescend($node, $segment, $segments);
    }

    /** @return array{0:bool,1:mixed} */
    private static function reduceSegmentResolution(array $result, string $segment): array
    {
        [$found, $reference] = $result;

        if (! $found) {
            return $result;
        }

        if (! is_array($reference)) {
            return [false, null];
        }

        if (array_key_exists($segment, $reference)) {
            return [true, $reference[$segment]];
        }

        return [false, null];
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array{0:list<non-empty-string>,1:non-empty-string,2:mixed}
     */
    private static function resolveSegmentsLastAndNormalized(string $key, mixed $value): array
    {
        [$segments, $lastSegment] = self::parseAndSplitSegments($key);

        return [$segments, $lastSegment, self::normalizeValueForKey($key, $value)];
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @return array<non-empty-string,T>
     */
    private static function setRecursively(
        array $node,
        array $segments,
        string $lastSegment,
        mixed $normalized,
        string $fullKey,
        string $operation
    ): array {
        if (empty($segments)) {
            $node[$lastSegment] = $normalized;

            return $node;
        }

        $segment = array_shift($segments);

        $childArrayOrThrow = self::extractChildArrayOrThrow($node, $segment, $fullKey, $operation);

        $node[$segment] = self::setRecursively(
            $childArrayOrThrow,
            $segments,
            $lastSegment,
            $normalized,
            $fullKey,
            $operation
        );

        return $node;
    }

    /** @return list<non-empty-string> */
    private static function splitKeyIntoSegments(string $key): array
    {
        /** @var list<string> $parts */
        $parts = preg_split(self::KEY_SEPARATOR, $key);

        if (! is_array($parts)) {
            return [];
        }

        return $parts;
    }

    /**
     * @param list<non-empty-string> $remaining
     *
     * @return array<non-empty-string,T>
     */
    private static function unsetHereOrDescend(array $node, string $segment, array $remaining): array
    {
        if (empty($remaining)) {
            unset($node[$segment]);

            return $node;
        }

        if (! array_key_exists($segment, $node)) {
            return $node;
        }

        if (! is_array($node[$segment])) {
            return $node;
        }

        $node[$segment] = self::recurseUnset($node[$segment], $remaining);

        return $node;
    }
}
