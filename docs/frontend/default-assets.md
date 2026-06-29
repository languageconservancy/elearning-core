# Learning Platform - Web - Language

This repository is a submodule of the [Owoksape Web App](https://bitbucket.org/owoksapedevelopers/owoksape-web-app) repository, that provides language-specific files for the parent repository, which is the web front-end of the Learning Platform project.

## Setup
This repository should be cloned into `owoksape-web-app/src/language`, and managed using either the `scripts/set-language.js` script or with npm run commands defined in the parent repo's package.json.

### Copying Files
`scripts/set-language.js` allows for "switching languages", by deleting the project assets/ directory and copying default and language-specific files to the project as defined in the table below.

| Source Directory/File | Destination Directory |
| ----------------------- | ------------------------ |
| `<project-root>/src/assets` | delete |
| `./defaults-assets` | `<project-root>/src/assets` |
| `./languages/<language>/assets/images/\*` | `<project-root>/src/assets/images/` |
| `./languages/<language>/assets/scss/\*` | `<project-root>/src/assets/scss/modules/` |
| `./languages/<language>/assets/keyboard/layouts\*` | `<project-root>/src/assets/keyboard/layouts/` |
| `./languages/<language>/index.html` | `<project-root>/src/` |
| `./languages/<language>/favicon.ico` | `<project-root>/src/` |

#### Script usage is as follows:
To switch to the dakota language and prompt user for confirmation:

`node ./scripts/set-language.js dakota `

To switch to the lakota language without prompting user for confirmation:

`node ./scripts/set-language.js lakota -p`

To watch default and lakota files for changes and copy files when any files change:

`node ./scripts/set-language.js lakota -w`

## Contents

- .
- ├── **default-assets/** - *Default assets, some of which will be overwritten by language-specific ones.*
- │   ├── **css/** - *3rd-party css files.*
- │   ├── **font/** - *Default font files.*
- │   ├── **tranlations/** - *Default translation files.*
- │   ├── **icons/** - *Default icons.*
- │   ├── **images/** - *Default images.*
- │   ├── **js/** - *3rd-party scripts.*
- │   ├── **keyboard/** - *Virtual keyboard files.*
- │   │   ├── **css/** - *Virtual keyboard style files.*
- │   │   ├── **extensions/** - *Virtual keyboard helper scripts.*
- │   │   ├── **layouts/** - *Virtual keyboard default layouts.*
- │   │   └── **test/** - *Interactive test for virtual keyboard.*
- │   └── **scss/** - *Default scss files. Structure taken from [Simple Sass Structure](https://thesassway.com/how-to-structure-a-sass-project/)*
- │   │   ├── **modules/** - *scss code you want to include, but not compile into the css.*
- │   │   └── **partials/** - *scss code you want to include and compile into css.*
- ├── **languages/** - *Directory with supported languages.*
- │   ├── **lakota/** - *Directory for dakota language-specific versions of files.*
- │   │   └── **assets/** - *Lakota language-specific version of assets.*
- │   │   │    ├── **index.html** - *Lakota site specific index.html file.*
- │   │   │    └── **favicon.ico** - *Lakota site specific favicon.ico file.*
- │   └── **ute/** - *Directory for Ute language-specific versions of files.*
- └── **scripts/** - *Directory for management scripts.*

## Contribution guidelines

- Develop on a branch and use pull requests to merge to develop and then master.
- Only the master branch should be used for production releases.
- Use Tabs for indentation with a Tab-width of 2.
- This allows for adjusting the tab-width automatically, which spaces don't accomodate, and results in smaller file sizes.

## Contacts
- Peter Vieira
- Elliot Thornton
- Logan Swango
