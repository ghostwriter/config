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
        [$segments, $lastSegment, $normalized] = self::prepareKeySegmentsAndValue($key, $value);

        $operation = 'append to';

        $this->configuration = self::appendNestedValue(
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
        [$found, $value] = self::resolveValueByDotNotationKey($key, $this->configuration);

        if ($found) {
            return $value;
        }

        return $default;
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function has(string $key): bool
    {
        [$found] = self::resolveValueByDotNotationKey($key, $this->configuration);

        return $found;
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function merge(array $options): void
    {
        array_walk($options, fn (mixed $value, mixed $key) => $this->mergeKeyValuePair($key, $value));
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function mergeDirectory(string $directory): void
    {
        $realDirectory = self::getRealDirectoryPathOrThrow($directory);

        self::ensureDirectoryExists($realDirectory);

        self::ensureDirectoryIsReadable($realDirectory);

        $fileList = iterator_to_array(self::buildPhpFileIterator($realDirectory));

        array_walk($fileList, function (SplFileInfo $phpFile) use ($directory, $realDirectory): void {
            $path = self::getFileRealPathOrThrow($phpFile, $directory);
            $configKey = self::convertFilePathToConfigKey($realDirectory, $path);

            $this->mergeFile($path, $configKey);
        });
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function mergeFile(string $file, ?string $key = null): void
    {
        $value = self::loadConfigurationFile($file);

        $options = null === $key ? $value : [
            $key => $value,
        ];

        $this->merge($options);
    }

    /** @throws ConfigurationExceptionInterface */
    #[Override]
    public function prepend(string $key, mixed $value): void
    {
        [$segments, $lastSegment, $normalized] = self::prepareKeySegmentsAndValue($key, $value);

        $this->configuration = self::prependNestedValue(
            $this->configuration,
            $segments,
            $lastSegment,
            $normalized,
            $key
        );
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
        [$segments, $lastSegment, $normalized] = self::prepareKeySegmentsAndValue($key, $value);

        $this->configuration = self::setNestedValue(
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

        $this->configuration = self::unsetNestedValue($this->configuration, self::validateAndSplitDotNotationKey($key));
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

    /** @throws ConfigurationExceptionInterface */
    private function mergeKeyValuePair(mixed $key, mixed $value): void
    {
        self::ensureKeyIsString($key);

        [$segments, $lastSegment] = self::splitDotNotationKey($key);

        $this->configuration = self::mergeNestedValue(
            $this->configuration,
            $segments,
            $lastSegment,
            self::normalizeConfigurationValue($key, $value)
        );
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function accumulateNormalizedIntegerKeyValue(array $carry, int|string $key, mixed $value): array
    {
        $carry[$key] = self::normalizeValueWithIntegerKey($value);

        return $carry;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function accumulateNormalizedStringKeyValue(array $carry, string $key, mixed $value): array
    {
        $carry[$key] = self::normalizeConfigurationValue($key, $value);

        return $carry;
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function appendNestedValue(
        array $node,
        array $segments,
        string $lastSegment,
        mixed $normalized,
        string $fullKey,
        string $operation
    ): array {
        if (empty($segments)) {
            return self::appendToFinalSegment($node, $lastSegment, $normalized);
        }

        return self::appendToNestedSegment($node, $segments, $lastSegment, $normalized, $fullKey, $operation);
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function appendToFinalSegment(array $node, string $lastSegment, mixed $normalized): array
    {
        $existing = $node[$lastSegment] ?? null;

        $node[$lastSegment] = array_merge(self::wrapValueInList($existing), self::wrapValueInList($normalized));

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
    private static function appendToNestedSegment(
        array $node,
        array $segments,
        string $lastSegment,
        mixed $normalized,
        string $fullKey,
        string $operation
    ): array {
        $segment = array_shift($segments);
        $child = self::getChildArrayOrThrowIfInvalid($node, $segment, $fullKey, $operation);
        $node[$segment] = self::appendNestedValue($child, $segments, $lastSegment, $normalized, $fullKey, $operation);

        return $node;
    }

    /** @return RegexIterator<SplFileInfo> */
    private static function buildPhpFileIterator(string $realDirectory): RegexIterator
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

    private static function convertFilePathToConfigKey(string $realDirectory, string $path): string
    {
        $baseLen = mb_strlen($realDirectory);

        $relative = mb_substr($path, $baseLen + 1, -4);

        return str_replace(DIRECTORY_SEPARATOR, '.', mb_trim($relative, DIRECTORY_SEPARATOR));
    }

    /** @param list<string> $segments */
    private static function ensureAllSegmentsNonEmpty(array $segments, string $key): void
    {
        if (! empty(array_filter($segments, static fn (string $segment): bool => '' === mb_trim($segment)))) {
            throw new InvalidDotNotationConfigurationKeyException($key);
        }
    }

    /**
     * @param mixed  $configuration
     * @param string $file
     */
    private static function ensureConfigurationFileReturnsArray(mixed $configuration, string $file): void
    {
        if (! is_array($configuration)) {
            throw new InvalidConfigurationFileException(
                sprintf('Config file "%s" does not return a valid configuration array.', $file)
            );
        }
    }

    private static function ensureDirectoryExists(string $realDirectory): void
    {
        if (! is_dir($realDirectory)) {
            throw new ConfigurationDirectoryNotFoundException(sprintf(
                'Config directory "%s" not found.',
                $realDirectory
            ));
        }
    }

    private static function ensureDirectoryIsReadable(string $realDirectory): void
    {
        if (! is_readable($realDirectory)) {
            throw new ConfigurationDirectoryNotReadableException(sprintf(
                'Config directory "%s" is not readable.',
                $realDirectory
            ));
        }
    }

    private static function ensureFileExists(string $file): void
    {
        if (! is_file($file)) {
            throw new ConfigurationFileNotFoundException(sprintf('Config file "%s" not found.', $file));
        }
    }

    private static function ensureFileIsReadable(string $file): void
    {
        if (! is_readable($file)) {
            throw new ConfigurationFileNotReadableException(sprintf('Config file "%s" is not readable.', $file));
        }
    }

    /** @throws ConfigurationExceptionInterface */
    private static function ensureKeyIsNotEmpty(string $key): void
    {
        if ('' === mb_trim($key)) {
            throw new ConfigurationKeyMustBeNonEmptyStringException();
        }
    }

    /** @param mixed $key */
    private static function ensureKeyIsString(mixed $key): void
    {
        if (! is_string($key)) {
            throw new ConfigurationKeyMustBeStringException();
        }
    }

    /** @param list<string> $segments */
    private static function ensureSegmentsNotEmpty(array $segments, string $key): void
    {
        if ([] === $segments) {
            throw new InvalidDotNotationConfigurationKeyException($key);
        }
    }

    /** @return array<non-empty-string,mixed> */
    private static function getChildArrayOrThrowIfInvalid(
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

    private static function getFileRealPathOrThrow(SplFileInfo $phpFile, string $directory): string
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

    private static function getRealDirectoryPathOrThrow(string $directory): string
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

    private static function isValidScalarOrNull(mixed $value): bool
    {
        return null === $value || is_scalar($value);
    }

    /** @return array<non-empty-string,T> */
    private static function loadConfigurationFile(string $file): array
    {
        self::ensureFileExists($file);

        self::ensureFileIsReadable($file);

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

            self::ensureConfigurationFileReturnsArray($configuration, $file);

            /** @var array<non-empty-string,T> $configuration */
            return $configuration;
        })($file);
    }

    /** @return array<non-empty-string,T> */
    private static function mergeIntoFinalSegment(array $node, string $lastSegment, mixed $normalized): array
    {
        $existing = $node[$lastSegment] ?? null;

        $node[$lastSegment] = self::mergeTwoValues($existing, $normalized);

        return $node;
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @return array<non-empty-string,T>
     */
    private static function mergeIntoNestedSegment(
        array $node,
        array $segments,
        string $lastSegment,
        mixed $normalized
    ): array {
        $segment = array_shift($segments);

        $value =$node[$segment] ?? [];

        if (! is_array($value)) {
            $value = [];
        }

        $node[$segment] = self::mergeNestedValue($value, $segments, $lastSegment, $normalized);

        return $node;
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @return array<non-empty-string,T>
     */
    private static function mergeNestedValue(
        array $node,
        array $segments,
        string $lastSegment,
        mixed $normalized
    ): array {
        if (empty($segments)) {
            return self::mergeIntoFinalSegment($node, $lastSegment, $normalized);
        }

        return self::mergeIntoNestedSegment($node, $segments, $lastSegment, $normalized);
    }

    private static function mergeTwoValues(mixed $existing, mixed $normalized): mixed
    {
        if (! is_array($normalized)) {
            return $normalized;
        }

        if (! is_array($existing)) {
            return $normalized;
        }

        return array_merge($existing, $normalized);
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function normalizeArrayKeys(array $configuration): array
    {
        return array_reduce(
            array_keys($configuration),
            static fn (array $carry, int|string $key): array => self::normalizeKeyValuePair(
                $carry,
                $key,
                $configuration[$key]
            ),
            []
        );
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeConfigurationValue(string $key, mixed $value): mixed
    {
        if ($value instanceof ConfigurationInterface) {
            return $value->toArray();
        }

        return self::normalizeNonInterfaceValue($key, $value);
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeKeyValuePair(array $carry, int|string $key, mixed $value): array
    {
        if (is_string($key)) {
            return self::accumulateNormalizedStringKeyValue($carry, $key, $value);
        }

        return self::accumulateNormalizedIntegerKeyValue($carry, $key, $value);
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeNonInterfaceValue(string $key, mixed $value): mixed
    {
        if (is_array($value)) {
            return self::normalizeArrayKeys($value);
        }

        return self::validateScalarOrThrow($key, $value);
    }

    /** @throws ConfigurationExceptionInterface */
    private static function normalizeValueWithIntegerKey(mixed $value): mixed
    {
        if ($value instanceof ConfigurationInterface) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return self::normalizeArrayKeys($value);
        }

        return $value;
    }

    /** @return list<non-empty-string> */
    private static function parseDotNotationIntoSegments(string $key): array
    {
        /** @var list<string> $parts */
        $parts = preg_split(self::KEY_SEPARATOR, $key);

        if (! is_array($parts)) {
            return [];
        }

        return $parts;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array{0:list<non-empty-string>,1:non-empty-string,2:mixed}
     */
    private static function prepareKeySegmentsAndValue(string $key, mixed $value): array
    {
        [$segments, $lastSegment] = self::splitDotNotationKey($key);

        return [$segments, $lastSegment, self::normalizeConfigurationValue($key, $value)];
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function prependNestedValue(
        array $configuration,
        array $segments,
        string $lastSegment,
        mixed $normalized,
        string $key
    ): array {
        if (empty($segments)) {
            return self::prependToFinalSegment($configuration, $lastSegment, $normalized);
        }

        return self::prependToNestedSegment($configuration, $segments, $lastSegment, $normalized, $key, 'prepend to');
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array<non-empty-string,T>
     */
    private static function prependToFinalSegment(array $node, string $lastSegment, mixed $normalized): array
    {
        $existing = $node[$lastSegment] ?? null;

        $node[$lastSegment] = array_merge(self::wrapValueInList($normalized), self::wrapValueInList($existing));

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
    private static function prependToNestedSegment(
        array $node,
        array $segments,
        string $key,
        mixed $value,
        string $fullKey,
        string $operation
    ): array {
        $segment = array_shift($segments);
        $child = self::getChildArrayOrThrowIfInvalid($node, $segment, $fullKey, $operation);

        if (empty($segments)) {
            $node[$segment] = self::prependToFinalSegment($child, $key, $value);

            return $node;
        }

        $node[$segment] = self::prependToNestedSegment($child, $segments, $key, $value, $fullKey, $operation);

        return $node;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array{0:bool,1:mixed}
     */
    private static function resolveValueByDotNotationKey(string $key, array $configuration): array
    {
        return array_reduce(
            self::validateAndSplitDotNotationKey($key),
            static fn (array $result, string $segment): array => self::traverseConfigurationSegment($result, $segment),
            [true, $configuration]
        );
    }

    /**
     * @param list<non-empty-string> $segments
     * @param non-empty-string       $lastSegment
     *
     * @return array<non-empty-string,T>
     */
    private static function setNestedValue(
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

        $childArrayOrThrow = self::getChildArrayOrThrowIfInvalid($node, $segment, $fullKey, $operation);

        $node[$segment] = self::setNestedValue(
            $childArrayOrThrow,
            $segments,
            $lastSegment,
            $normalized,
            $fullKey,
            $operation
        );

        return $node;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return array{0:list<non-empty-string>,1:non-empty-string}
     */
    private static function splitDotNotationKey(string $key): array
    {
        $segments = self::validateAndSplitDotNotationKey($key);

        $lastSegment = array_pop($segments);

        return [$segments, $lastSegment];
    }

    /** @return array{0:bool,1:mixed} */
    private static function traverseConfigurationSegment(array $result, string $segment): array
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
     * @param list<non-empty-string> $segments
     *
     * @return array<non-empty-string,T>
     */
    private static function unsetNestedValue(array $node, array $segments): array
    {
        $segment = array_shift($segments);

        return self::unsetSegmentValue($node, $segment, $segments);
    }

    /**
     * @param list<non-empty-string> $remaining
     *
     * @return array<non-empty-string,T>
     */
    private static function unsetSegmentValue(array $node, string $segment, array $remaining): array
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

        $node[$segment] = self::unsetNestedValue($node[$segment], $remaining);

        return $node;
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return list<non-empty-string>
     */
    private static function validateAndSplitDotNotationKey(string $key): array
    {
        self::ensureKeyIsNotEmpty($key);

        $segments = self::parseDotNotationIntoSegments($key);

        self::ensureSegmentsNotEmpty($segments, $key);

        self::ensureAllSegmentsNonEmpty($segments, $key);

        return $segments;
    }

    /** @throws ConfigurationExceptionInterface */
    private static function validateScalarOrThrow(string $key, mixed $value): mixed
    {
        if (self::isValidScalarOrNull($value)) {
            return $value;
        }

        throw new InvalidConfigurationValueException(sprintf(
            'Invalid configuration value for key "%s". Expected: array, null, or scalar (bool, float, int, string). Received: %s.',
            $key,
            get_debug_type($value),
        ));
    }

    /**
     * @throws ConfigurationExceptionInterface
     *
     * @return list<mixed>
     */
    private static function wrapValueInList(mixed $value): array
    {
        if (null === $value) {
            return [];
        }

        if (is_array($value)) {
            return self::normalizeArrayKeys($value);
        }

        return [$value];
    }
}
