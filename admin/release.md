# release

Builds the branches needed for a framework release.

This tool is meant to help automate the process of
launching a new release, by creating the release
distribution files, (tagging everything properly), 
and getting/keeping the repo branches in order.

## Audience

This script is intended for use by framework maintainers,
i.e. someone with commit rights on the CI4 repository.

You will be prompted for your github credentials and
GPG-signing key as appropriate.

## Workflow

The repo has two branches of interest: "master" (stable) and "develop" (in progress).
There might be other feature branches, but they are not relevant to this process.

Once "develop" is ready for a new release, the general workflow is to

- create a "release" branch from develop
- update version dependencies or constants
- generate version(s) of the user guide
- move or ignore stuff, distinguishing release from development
- test that all is as it should be
- merge the release branch into "master"
- **manually** create the release & tag on github, based on master
- put everything back where it should be for development
- merge the post-release branch into "master"
- merge the post-release branch into "develop"
- **manually** delete the release branches in the repo
- **manually** post a sticky announcement thread on the forum
- **manually** tweet the release announcement

Visually:

    develop -> release -> master
    post-release -> master
    post->release  -> develop

The `release` bash script does the first six workflow steps,
and the `post-release` script does the other three between
the manual steps.

For now, everything past the release branch build will be done
manually, until the condidence level is high enough to
automate some/all of those steps.

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

    `admin/release version [qualifier]`

Nothing is pushed to the repo. at this point -
the results are left inside
the release branch in your local clone.

The "version" should follow semantic versioning, e.g. `4.0.6`, and the
version number should be higher than the current released one.

The "qualifier" argument is a suffix to add to the version
for a pre-release, e.g. `beta.2` or `rc.41`.

Examples:
- `admin/release 4.0.0 alpha.1` would prepare the "4.0.0-alpha.1" pre-release PR
- `admin/release 4.0.0` would prepare the "4.0.0" release PR
- `admin/release peanut butter banana` would complain and tell you to read these directions

Complete the next few steps of the release manually:
- merge the release branch to "master"
- push that to the main repo
- on github.com, create an appropriate release (or pre-release)

Once the release branch has been vetted, and you have
completed the manual steps, clean up with:

    `admin/post_release version [qualifier]`

## Release notes

On launch of a new release, a release notes post should be made in the
announcements subforum. The planned text for it (so it can be previewed
by admins) is in `admin/release_notes`.
