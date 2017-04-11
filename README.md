# UpStream
*UpStream* is a powerful, extensible and easy to use project manager for WordPress. Manage projects, clients, milestones, tasks, files and more.

## Table of contents
- [About](https://github.com/upstreamplugin/UpStream#upstream)
- [Requirements](https://github.com/upstreamplugin/UpStream#requirements)
- [Contributing](https://github.com/upstreamplugin/UpStream#contributing)
   - [Bug Reports](https://github.com/upstreamplugin/UpStream#bug-reports)
   - [Pull Requests](https://github.com/upstreamplugin/UpStream#pull-requests)
   - [Git Settings](https://github.com/upstreamplugin/UpStream#git-settings)
- [Versioning](https://github.com/upstreamplugin/UpStream#versioning)
- [License](https://github.com/upstreamplugin/UpStream#license)

## Requirements
- PHP 5.4+ or newer
- WordPress 4.0 or newer

## Contributing
Hey, that's awesome! Feel free to contribute in any way you can, submitting bug reports and features requests, forking and sending pull requests.

### Bug Reports
A bug is a demonstrable problem caused by code in the repository.

Good bug reports are extremely helpful. They should really clear and contain all the necessary information.

Please try to be as detailed as possible in your report, e.g: what's the expected outcome, steps to reproduce the issue, details about your environment.

### Pull Requests
The `master` branch always reflects a production-ready state while the latest development is taking place in the `development` branch.

Only pull requests to the `development` branch will be reviewed and merged.

### Git Settings
Ensure that your local-git setting `ignorecase` is set to `false`.

To change it locally, run:
```
$ cd <path-to-your-local-repository>
$ git config core.ignorecase false
```

In case you want to change it globally instead:
```
$ cd <path-to-your-local-repository>
$ git config --global core.ignorecase false
```

Alternatively, you can change manually editing git config file, which can be found here:
```
<path-to-your-local-repository>/.git/config
```

## Versioning
*UpStream* is maintained using the [Semantic Versioning Specification (SemVer)](http://semver.org/).

## License
Released under GPLv3.

For more info, see [license file](https://github.com/upstreamplugin/UpStream/blob/master/LICENSE).
