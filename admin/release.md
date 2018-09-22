# release

Builds & deploys a framework release.

This tool is meant to help automate the process of
launching a new release, by creating the release
distribution files, tagging everything properly, 
and getting the repo branches in order.

## Audience

This script is intended for use by framework maintainers,
i.e. someone with commit rights on the CI4 repository.

You will be prompted for your github credentials and
GPG-signing key as appropriate.

## Workflow

The repo has two branches of interest: "master" (stable) and "develop" (in progress).
There might be other feature branches, but they are not relevant to this process.

Once "develop" is ready for a new release, the general workflow is to

- create a release branch from develop
- update version dependencies or constants
- generate version(s) of the user guide
- move or ignore stuff, distinguishing release from development
- test that all is as it should be
- tag and merge the release branch into "master"
- merge "master" into "develop"
- put everything back where it should be for development
- remove the release branch

Visually:

    develop -> release -> master -> develop

Finally, there are a couple of manual tasks:

- post a sticky announcement thread on the forum
- tweet the release announcement

## Assumptions

You (a maintainer) have forked the main CodeIgniter4 repo,
and the git alias `origin`, in your local clone, refers to your fork. 
The script creates an additional alias, `upstream`, which refers to the 
main repo. This separation keeps the release branch isolated
for any testing you want to do.

The `develop` branch of the main repo should be "clean", and ready for
a release. This means that the changelog and upgrading instructions
have been suitably edited.

This script is not intended to deal with hotfixes, i.e. PRs against
`master` that need to also be merged into `develop`, probably
as part of a bug fix minor release.

## Usage

Inside a shell prompt, in the project root:

    `admin/release [test|deploy] version [qualifier]`

If the "deploy" action is not specified, the script execution is considered
a trial run, and nothing is pushed to the repo. 
Whether or not deployed, the results are left inside
the release branch in your local clone.

The "version" should follow semantic versioning, e.g. `4.0.6`, and the
version number should be higher than the current released one.

The "qualifier" argument is a suffix to add to the version
for a pre-release, e.g. `beta.2` or `rc.41`.

Examples:
- `admin/release test 4.0.0 alpha.1` would prepare the "4.0.0-alpha.1" pre-release PR
- `admin/release 4.0.0` would prepare the "4.0.0" release PR
- `admin/release peanut butter banana` would complain and tell you to read these directions

