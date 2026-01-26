# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## 2.0.0 - 2025-11-01

### Added

 - Class `Ghostwriter\Config\Configuration` has been added.
 - Class `Ghostwriter\Config\Exception\ConfigurationDirectoryNotFoundException` has been added.
 - Class `Ghostwriter\Config\Exception\ConfigurationDirectoryNotReadableException` has been added.
 - Class `Ghostwriter\Config\Exception\ConfigurationFileNotFoundException` has been added.
 - Class `Ghostwriter\Config\Exception\ConfigurationFileNotReadableException` has been added.
 - Class `Ghostwriter\Config\Exception\ConfigurationFilePathResolutionException` has been added.
 - Class `Ghostwriter\Config\Exception\ConfigurationKeyMustBeNonEmptyStringException` has been added.
 - Class `Ghostwriter\Config\Exception\ConfigurationKeyMustBeStringException` has been added.
 - Class `Ghostwriter\Config\Exception\FailedToLoadConfigurationFileException` has been added.
 - Class `Ghostwriter\Config\Exception\FailedToLoadConfigurationFileWithErrorsException` has been added.
 - Class `Ghostwriter\Config\Exception\InvalidConfigurationFileException` has been added.
 - Class `Ghostwriter\Config\Exception\InvalidConfigurationKeyException` has been added.
 - Class `Ghostwriter\Config\Exception\InvalidConfigurationValueException` has been added.
 - Class `Ghostwriter\Config\Exception\InvalidDotNotationConfigurationKeyException` has been added.
 - Interface `Ghostwriter\Config\Interface\ConfigurationExceptionInterface` has been added.
 - Interface `Ghostwriter\Config\Interface\ConfigurationInterface` has been added.


### Removed

- Class `Ghostwriter\Config\Config` has been deleted.
- Class `Ghostwriter\Config\ConfigFactory` has been deleted.
- Class `Ghostwriter\Config\Exception\ConfigDirectoryNotFoundException` has been deleted.
- Class `Ghostwriter\Config\Exception\ConfigFileNotFoundException` has been deleted.
- Class `Ghostwriter\Config\Exception\EmptyConfigKeyException` has been deleted.
- Class `Ghostwriter\Config\Exception\ExceptionInterface` has been deleted.
- Class `Ghostwriter\Config\Exception\InvalidConfigFileException` has been deleted.
- Class `Ghostwriter\Config\Exception\InvalidConfigKeyException` has been deleted.
- Class `Ghostwriter\Config\Exception\ShouldNotHappenException` has been deleted.
- Interface `Ghostwriter\Config\ConfigFactoryInterface` has been deleted.
- Interface `Ghostwriter\Config\ConfigInterface` has been deleted.
- Interface `Ghostwriter\Config\ConfigProviderInterface` has been deleted.

## 1.0.0 - 2025-01-07

### Added

- Class `Ghostwriter\Config\ConfigFactoryInterface` has been added
- Class `Ghostwriter\Config\ConfigFactory` has been added
- Class `Ghostwriter\Config\ConfigInterface` has been added
- Class `Ghostwriter\Config\Exception\ExceptionInterface` has been added
- Method `Ghostwriter\Config\ConfigFactory#createFromFile()` has been added

### Changed

- Method `Ghostwriter\Config\ConfigFactory#createFromPath()` changed to `Ghostwriter\Config\ConfigFactory#createFromFile()`
- The return type of `Ghostwriter\Config\Config#merge()` changed from `void` to the `self`
- The return type of `Ghostwriter\Config\ConfigFactory#create()` changed from `Ghostwriter\Config\Config` to the `Ghostwriter\Config\ConfigInterface`

### Removed

- Class `Ghostwriter\Config\Contract\ConfigFactoryInterface` has been deleted
- Class `Ghostwriter\Config\Contract\ConfigInterface` has been deleted
- Class `Ghostwriter\Config\Contract\Exception\ConfigExceptionInterface` has been deleted
- Method `Ghostwriter\Config\Config#append()` has been removed
- Method `Ghostwriter\Config\Config#count()` has been removed
- Method `Ghostwriter\Config\Config#join()` has been removed
- Method `Ghostwriter\Config\Config#offsetExists()` has been removed
- Method `Ghostwriter\Config\Config#offsetGet()` has been removed
- Method `Ghostwriter\Config\Config#offsetSet()` has been removed
- Method `Ghostwriter\Config\Config#offsetUnset()` has been removed
- Method `Ghostwriter\Config\Config#prepend()` has been removed
- Method `Ghostwriter\Config\Config#wrap()` has been removed
- Method `Ghostwriter\Config\ConfigFactory#createFromPath()` has been removed

## 0.1.0 - 2023-01-01

First version
