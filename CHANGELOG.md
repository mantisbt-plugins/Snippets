# Snippets Plugin Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/)
specification.

--------------------------------------------------------------------------------

## [2.2.5] - 2018-03-18

### Added

- Korean translation
  [#42](https://github.com/mantisbt-plugins/snippets/issues/42)

### Fixed

- Hide Snippets selection list when none are available 
  [#41](https://github.com/mantisbt-plugins/snippets/issues/41)


## [2.2.4] - 2018-03-18

### Fixed

- Sort Snippets selection list by name
  [#34](https://github.com/mantisbt-plugins/snippets/issues/34)


## [2.2.3] - 2018-03-17

### Fixed

- Always replace User Placeholders with username
  [#37](https://github.com/mantisbt-plugins/snippets/issues/37)


## [2.2.2] - 2018-02-26

### Fixed

- qTip2 library throws 'Source map error' in browser console
  [#32](https://github.com/mantisbt-plugins/snippets/issues/32)


## [2.2.1] - 2018-01-31

### Fixed

- Can't retrieve snippets data from REST API if URL rewriting is not working
  [#31](https://github.com/mantisbt-plugins/snippets/issues/31)


## [2.2.0] - 2018-01-14

### Changed

- Require MantisBT 2.3 or later
- Use REST API instead of xmlhttprequest
  [#16](https://github.com/mantisbt-plugins/snippets/issues/16)
- Replaced simpletip.js library with qTip2
  [#25](https://github.com/mantisbt-plugins/snippets/issues/25)

### Removed

- Unused version information from JSON payload
  [#27](https://github.com/mantisbt-plugins/snippets/issues/27)

### Fixed

- Tooltip not shown on snippets list page 
  [#19](https://github.com/mantisbt-plugins/snippets/issues/19)


## [2.1.0] - 2017-10-23

### Changed

- Javascript refactoring and code cleanup
- Increase spacing between checkbox and label on config page
  [#21](https://github.com/mantisbt-plugins/snippets/issues/21)
- Update jquery-textrange library to 1.4.0

### Fixed

- Ensure numeric JSON fields have correct data type
- HTML syntax error in config page


## [2.0.0] - 2017-07-31

### Added

- Support for MantisBT 2.0

### Changed

- Add ‘Manage Global Snippets’ to account menu
- Hide checkbox when editing a single Snippet
  [#13](https://github.com/mantisbt-plugins/snippets/issues/13)

### Removed

- Support for MantisBT 1.3


## [1.2.0] - 2017-07-31

### Added

- Spanish translation
  [#17](https://github.com/mantisbt-plugins/snippets/issues/17)

### Changed

- Move plugin to root


## [1.1.0] - 2016-04-19

### Changed

- Don't use `user0` for unassigned issues' handler
- Use current user as reporter when reporting issues
- Use more descriptive placeholders (e.g. `%u` -> `{user}`)
  [#10](https://github.com/mantisbt-plugins/snippets/issues/10)


### Fixed

- PHP errors in config page
- Fix snippets for additional info field
- Replace deprecated db_query_bound() calls


## [1.0.0] - 2016-01-02

### Added

- Support for MantisBT 1.3

### Removed

- Support for MantisBT 1.2
- jQuery plugin is no longer required

### Fixed

- Tooltip position outside viewable area
  [#8](https://github.com/mantisbt-plugins/snippets/issues/8)


## [0.6] - 2014-10-30

### Added

- Allowing selection of fields on which Snippets can be used in plugin configuration   
  [#6](https://github.com/mantisbt-plugins/snippets/issues/6)


### Changed

- Minified JavaScript
- Bump minimum jQuery version to 1.6

### Fixed

- Text duplicated when inserting snippet with caret at beginning of textarea 
  [#4](https://github.com/mantisbt-plugins/snippets/issues/4)
- Fix behavior of Select all check box in snippet lists

## [0.5] - 2013-04-05

### Fixed

- Conditional Dependency for jQuery plugin (MantisBT 1.2/1.3 compatibility)


## [0.4] - 2012-10-12

### Added

- French translation
- German translation

### Fixed

- Internet Explorer compatibility issue
- Snippets containing single quotes are truncated 
  [#2](https://github.com/mantisbt-plugins/snippets/issues/2)


## [0.3] - 2010-04-15

### Added

- Access thresholds
- Tooltip docs for placeholder patterns
- Snippet insertion at cursor position
- Error checking for blank names and values 

### Fixed

- bug_id sniffing on change status page
- Language consistency
- Prevent SQL errors when given empty arrays


## [0.2] - 2010-03-29

### Added

- Implemented placeholder patterns for snippets

### Fixed

- Problem with empty snippet lists
- Proper cleaning of snippets for usage


## [0.1] - 2010-03-22

### Added

- Initial release


[Unreleased]: https://github.com/mantisbt-plugins/snippets/compare/v2.2.5...HEAD

[2.2.4]: https://github.com/mantisbt-plugins/snippets/compare/v2.2.4...v2.2.5
[2.2.4]: https://github.com/mantisbt-plugins/snippets/compare/v2.2.3...v2.2.4
[2.2.3]: https://github.com/mantisbt-plugins/snippets/compare/v2.2.2...v2.2.3
[2.2.2]: https://github.com/mantisbt-plugins/snippets/compare/v2.2.1...v2.2.2
[2.2.1]: https://github.com/mantisbt-plugins/snippets/compare/v2.2.0...v2.2.1
[2.2.0]: https://github.com/mantisbt-plugins/snippets/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/mantisbt-plugins/snippets/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/mantisbt-plugins/snippets/compare/v1.2.0...v2.0.0

[1.2.0]: https://github.com/mantisbt-plugins/snippets/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/mantisbt-plugins/snippets/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/mantisbt-plugins/snippets/compare/v0.6...v1.0.0

[0.6]: https://github.com/mantisbt-plugins/snippets/compare/v0.5...v0.6
[0.5]: https://github.com/mantisbt-plugins/snippets/compare/v0.4...v0.5
[0.4]: https://github.com/mantisbt-plugins/snippets/compare/v0.3...v0.4
[0.3]: https://github.com/mantisbt-plugins/snippets/compare/v0.2...v0.3
[0.2]: https://github.com/mantisbt-plugins/snippets/compare/v0.1...v0.2
[0.1]: https://github.com/mantisbt-plugins/snippets/compare/25fd763c463de359cc7f83e9bdd30ea3e8f58cdd...v0.1
