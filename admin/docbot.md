# docbot

Builds & deploys user guide.

The in-progress CI4 user guide, warts & all, is rebuilt in a nested
repository clone (`user_guide_src/build/html`), with the result
optionally pushed to the `gh-pages` branch of the repo.
That would then be publically visible as the in-progress
version of the [User Guide](https://codeigniter4.github.io/CodeIgniter4/).

## Requirements

You must have python & sphinx installed.

## Audience

This script is intended for use by framework maintainers,
i.e. someone with commit rights on the CI4 repository.

This script wraps the conventional user guide building,
i.e. `user_guide_src/make html`, with additional
steps.

You will be prompted for your github credentials and
GPG-signing key as appropriate.

## Usage

Inside a shell prompt, in the project root:

    `admin/docbot [deploy]`

If "deploy" is not added, the script execution is considered
a trial run, and nothing is pushed to the repo.

Whether or not deployed, the results are left inside
user_guide_src/build (which is git ignored).

Generate these and the API docs together with the 'alldocs' script.
