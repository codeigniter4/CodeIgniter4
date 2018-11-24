# Workflow

The main repo has two branches of interest: "master" (stable) and "develop" (in progress).
There might be other feature branches, but they are not relevant to this process.

Once "develop" is ready for a new release, the general workflow is to

pre-release...
- create a "release" branch from develop
- update version dependencies or constants
- generate version(s) of the user guide
- move or ignore stuff, distinguishing release from development
- test that all is as it should be

release...
- merge the release branch into "master"
- update the distribution repos
- **manually** create the releases & tag them on github, based on master

post-release...
- eliminate the build & dist folders used above
- setup substitution variables for the next release
- merge the post-release branch into "master"
- merge the post-release branch into "develop"
- **manually** delete the release branches in the repo
- **manually** post a sticky announcement thread on the forum
- **manually** tweet the release announcement


    This would be manually merged to the `master` and `develop` branches.  
    Creating a release is done on github.com manually, so that additional
    binaries can be added to the release if appropriate.

Visually:

    develop -> release -> master
    post-release -> master
    post->release  -> develop

## Assumptions

You (a maintainer) have forked the main CodeIgniter4 repo,
and the git alias `origin`, in your local clone, refers to your fork. 
The `config` script defines an additional alias, `CI_ORG`, which refers to the 
CodeIgniter 4 organization on github. 
This separation keeps the release branch isolated for any testing you want to do.

The `develop` branch of the main repo should be "clean", and ready for
a release. This means that the changelog and upgrading instructions
have been suitably edited.

This script is not intended to deal with hotfixes, i.e. PRs against
`master` that need to also be merged into `develop`, probably
as part of a bug fix minor release.

## Usage

Inside a shell prompt, in the project root:

    `admin/pre-release version [qualifier]`

Nothing is pushed to the repo at this point -
the results are left inside
the release branch in your local clone.

The "version" should follow semantic versioning, e.g. `4.0.6`, and the
version number should be higher than the current released one.

The "qualifier" argument is a suffix to add to the version
for a pre-release, e.g. `beta.2` or `rc.41`.

Examples:
- `admin/pre-release 4.0.0 alpha.1` would prepare the "4.0.0-alpha.1" pre-release PR
- `admin/pre-release 4.0.0` would prepare the "4.0.0" release PR
- `admin/pre-release peanut butter banana` would complain and tell you to read these directions

Once you have vetted the `dist` folder inside your local repo, you
can merge & push everything with

    `admin/release`

On github.com, create an appropriate release (or pre-release),
with any optional files from the root of `dist`.

Once the github releases are done, clean up with:

    `admin/post_release`

## Release notes

On launch of a new release, a release notes post should be made in the
announcements subforum. The planned text for it (so it can be previewed
by admins) is in `admin/release_notes.bb`.

## Audience

These scripts are intended for use by framework maintainers,
i.e. someone with commit rights on the CI4 repositories.

You will be prompted for your github credentials and
GPG-signing key as appropriate.

