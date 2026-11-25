# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 25 November 2026
### Changed
- BREAKING: Module permissions are now nested under Stores > Taxes (Magento_Tax::manage_tax) and cleaning all rates needs a new separate permission; admin roles must be re-granted the module permissions
- BREAKING: The Add, Fix and Clean actions only accept POST requests and the Clean button asks for confirmation; `LoggerInterface::warning()` takes an optional `$action` array instead of HTML in the message
- Make the cron schedule configurable and run the check daily by default instead of every 3 minutes
- Only run the tax rate check when opening the tax rate grid, instead of on every tax rate admin request
- Require Guzzle 7.4.5 or newer, as Guzzle 6 is end-of-life and affected by several CVEs
- Add PHP 8.5 support

### Removed
- Drop PHP 8.1 support

### Fixed
- Stored XSS in admin warning messages via tax rate codes and feed values
- SSRF via the alternative feed URL: only https URLs to public hosts are accepted, with timeouts and a redirect limit
- Invalid rows from the online feed could be saved as tax rates; they are now validated, skipped and logged
- An invalid cron schedule expression stopped schedule generation for the whole default cron group; it is now rejected on save
- Error pages in the Add and Fix actions on invalid input or feed failures; they now redirect back with an error message
- Configuration page breaking when the feed list could not be fetched, plus validation of the feed source and cron email and expiry of cached feed data
- The "No online rates found" warning never showed for an empty feed

## [1.3.1] - 29 April 2026
### Fixed
- Show error in backend when clean task gives exception

## [1.3.0] - 19 April 2026
### Fixed
- Add CLI command to add all rates
- Copy generic CI/CD files

## [1.2.15] - 22 October 2025
### Fixed
- Add integration test helper to GitHub Action
- Add dep with integration testing helper
- Always run integration tests
- Add new workflow tests

## [1.2.14] - 27 July 2025
### Fixed
- $escape parameter must be provided #5

## [1.2.13] - 19 July 2025
### Fixed
- Fix possible error with empty postcode

## [1.2.12] - 06 May 2025
### Fixed
- Fix PHP 8.4 issue
- Fix compat with PHPUnit
- Remove `setup_version` and add `Magento_Config` dep

## [1.2.11] - 24 October 2024
### Fixed
- Add funding

## [1.2.10] - 2 October 2024
### Fixed
- Send mail by using the configured email address

## [1.2.9] - 9 April 2024
### Fixed
- Fix calling Rate class in the Add action #2 @se-viacheslav

## [1.2.8] - 9 April 2023
### Fixed
- Compat psr/log with Magento 2.4.7

## [1.2.7] - 8 April 2023
- Failed release

## [1.2.6] - 9 January 2023
### Fixed
- Properly use ZIP codes from feed

## [1.2.5] - 25 April 2022
### Fixed
- Version bump

## [1.2.4] - 25 April 2022
### Fixed
- Upgrade Guzzle because of Magento 2.4.4

## [1.2.3] - 1 January 2021
### Fixed
- Only report rates higher than 0

## [1.2.2] - 29 July 2019
### Added
- Support for Magento 2.4
- Add KeepAChangeLog support
- Move configuration to separate **Yireo** section

## [1.2.1] - January 2019
### Added
- Fix issue with mail not being sent via cron
- Add website name to email subject
- Increase cron time to every 3 minutes

# [1.2.0] - December 2019
### Added
- Add Fix link to notices
- Add "Clean All Rates" button
- Clean up dependencies in composer file
- Add GitLab CI integration

# [1.0.1] - December 2019
### Added
- Fix typo in settings

# [1.0.0] - December 2019
### Added
- Stable release

# [0.0.2] - December 2019
### Added
- Integration tests completed
- Functional tests completed

# [0.0.1] - December 2019
### Added
- Working proof of concept
