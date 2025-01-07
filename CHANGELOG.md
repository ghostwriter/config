# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

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
