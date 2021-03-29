# apibot

Builds & deploys API docs.

The in-progress CI4 API docs, warts & all, are rebuilt and
then copied to a nested
repository clone (`build/api`), with the result
optionally pushed to the `master` branch of the `api` repo.
That would then be publically visible as the in-progress
version of the [API](https://codeigniter4.github.io/api/).

## Requirements

You must have phpDocumentor installed, with a `phpdoc` alias installed globally.

## Audience

This script is intended for use by framework maintainers,
i.e. someone with commit rights on the CI4 repository.

You will be prompted for your github credentials and
GPG-signing key as appropriate.

## Usage

Inside a shell prompt, in the project root:

    `admin/apibot [deploy]`

If "deploy" is not added, the script execution is considered
a trial run, and nothing is pushed to the repo.

Whether or not deployed, the results are left inside
`build/api` (which is git ignored).

Generate these and the userguide together with the 'alldocs' script.
