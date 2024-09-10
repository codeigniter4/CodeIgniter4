# Release Process

> Documentation guide based on the releases of `4.0.5` and `4.1.0` on January 31, 2021.
>
> Updated for `4.5.0` on April 7, 2024.
>
> -MGatner, kenjis

## Notation

- `4.x.x`: The new release version. (e.g., `4.5.3`)
- `4.y`: The next minor version. (e.g., `4.6`)
- `4.z`: The next next minor version. (e.g., `4.7`)

> [!NOTE]
> Copy this file, and replace the versions above with the actual versions.

## Merge `develop` branch into next minor version branch `4.y`

Before starting release process, if there are commits in `develop` branch that
are not merged into `4.y` branch, merge them. This is because if conflicts occur,
merging will take time.

```console
git fetch upstream
git switch 4.y
git merge upstream/4.y
git merge upstream/develop
git push upstream HEAD
```

## [Minor version only] Merge minor version branch into `develop`

If you release a new minor version.

* [ ] Create PR to merge `4.y` into `develop` and merge it
* [ ] Rename the current minor version (e.g., `4.5`) in Setting > Branches >
  "Branch protection rules" to the next minor version. E.g. `4.5` → `4.6`
* [ ] Delete the merged `4.y` branch (This closes all PRs to the branch)

## Preparation

Work off direct clones of the repos so the release branches persist for a time.

* [ ] Clone both **codeigniter4/CodeIgniter4** and **codeigniter4/userguide** and
  resolve any necessary PRs
    ```console
    rm -rf CodeIgniter4.bk userguide.bk
    mv CodeIgniter4 CodeIgniter4.bk
    mv userguide userguide.bk
    git clone git@github.com:codeigniter4/CodeIgniter4.git
    git clone git@github.com:codeigniter4/userguide.git
    ```
* [ ] Vet the **admin/** folders for any removed hidden files (Action deploy scripts
  *do not remove these*)
  ```console
  cd CodeIgniter4
  git diff --name-status origin/master admin/
  ```

## Changelog

When generating the changelog each Pull Request to be included must have one of
the following [labels](https://github.com/codeigniter4/CodeIgniter4/labels):
- **bug** ... PRs that fix bugs
- **enhancement** ... PRs to improve existing functionalities
- **new feature** ... PRs for new features
- **refactor** ... PRs to refactor

PRs with breaking changes must have the following additional label:
- **breaking change** ... PRs that may break existing functionalities

### Generate Changelog

To auto-generate, navigate to the
[Releases](https://github.com/codeigniter4/CodeIgniter4/releases) page,
click the "Draft a new release" button.

* Tag: `v4.x.x` (Create new tag)
* Target: `develop`

Click the "Generate release notes" button.

Check the resulting content. If there are items in the *Others* section which
should be included in the changelog, add a label to the PR and regenerate
the changelog.

Copy the resulting content into **CHANGELOG.md** and adjust the format to match
the existing content.

## Process

> [!NOTE]
> Most changes that need noting in the User Guide and docs should have
> been included with their PR, so this process assumes you will not be
> generating much new content.

* [ ] Merge any Security Advisory PRs in private forks
* [ ] Replace **CHANGELOG.md** with the new version generated above
* [ ] Update **user_guide_src/source/changelogs/v4.x.x.rst**
  * Remove the section titles that have no items
* [ ] Update **user_guide_src/source/installation/upgrade_4xx.rst**
  * [ ] fill in the "All Changes" section, and add it to **upgrade_4xx.rst**
    * git diff --name-status origin/master -- . ':!system' ':!tests' ':!user_guide_src'
    * Note: `tests/` is not used for distribution repos. See `admin/starter/tests/`
  * [ ] Remove the section titles that have no items
  * [ ] [Minor version only] Update the "from" version in the title. E.g., `from 4.3.x` → `from 4.3.8`
* [ ] Run `php admin/prepare-release.php 4.x.x` and push to origin
  * The above command does the following:
    * Create a new branch `release-4.x.x`
    * Update **system/CodeIgniter.php** with the new version number:
      `const CI_VERSION = '4.x.x';`
    * Update **user_guide_src/source/conf.py** with the new `version = '4.x'` (if applicable)
      and `release = '4.x.x'`
    * Update **user_guide_src/source/changelogs/{version}.rst**
      * Set the date to format `Release Date: January 31, 2021`
    * Update **phpdoc.dist.xml** with the new `<title>CodeIgniter v4.x API</title>`
      and `<version number="4.x.x">`
    * Commit the changes with `Prep for 4.x.x release`
* [ ] Create a new PR from `release-4.x.x` to `develop`:
  * Title: `Prep for 4.x.x release`
  * Description:
    ```
    Updates changelog and version references for 4.x.x.

    Previous version: #xxxx
    Release Code: TODO
    New Changelog: TODO
    ```
    (plus checklist)
* [ ] Let all tests run, then review and merge the PR
* [ ] Create a new PR from `develop` to `master`:
  * Title: `4.x.x Ready code`
  * Description: blank
* [ ] Merge the PR and wait for all tests.
* [ ] Create a new Release:
  * Tag: `v4.x.x` (Create new tag)
  * Target: `master`
  * Title: `CodeIgniter 4.x.x`
  * Description:
    ```
    CodeIgniter 4.x.x release.

    See the changelog: https://github.com/codeigniter4/CodeIgniter4/blob/develop/CHANGELOG.md

    ## New Contributors
    *

    **Full Changelog**: https://github.com/codeigniter4/CodeIgniter4/compare/v4.x.w...v4.x.x
    ```
    Click the "Generate release notes" button, and get the "New Contributors".
* [ ] Watch for the "Deploy Distributable Repos" action to make sure **framework**,
  **appstarter**, and **userguide** get updated
* [ ] Run the following commands to install and test `appstarter` and verify the new
  version:
    ```console
    rm -rf release-test
    composer create-project codeigniter4/appstarter release-test
    cd release-test
    composer test && composer info codeigniter4/framework
    ```
* [ ] Verify that the user guide actions succeeded:
  * [ ] "[Deploy Distributable Repos](https://github.com/codeigniter4/CodeIgniter4/actions/workflows/deploy-distributables.yml)", the main repo
  * [ ] "[Deploy Production](https://github.com/codeigniter4/userguide/actions/workflows/deploy.yml)", UG repo
  * [ ] "[pages-build-deployment](https://github.com/codeigniter4/userguide/actions/workflows/pages/pages-build-deployment)", UG repo
  * [ ] Check if "CodeIgniter4.x.x.epub" is added to UG repo. "CodeIgniter.epub" was
    created when v4.3.8 was released.
* [ ] Fast-forward `develop` branch to catch the merge commit from `master`
    ```console
    git fetch origin
    git checkout develop
    git merge origin/develop
    git merge origin/master
    git push origin HEAD
    ```
* [ ] Update the next minor version branch `4.y`:
    ```console
    git fetch origin
    git checkout 4.y
    git merge origin/4.y
    git merge origin/develop
    git push origin HEAD
    ```
* [ ] [Minor version only] Create the new next minor version branch `4.z`:
    ```console
    git fetch origin
    git switch develop
    git switch -c 4.z
    git push origin HEAD
    ```
* [ ] Request CVEs and Publish any Security Advisories that were resolved from private forks
  (note: publishing is restricted to administrators):
* [ ] Announce the release on the forums and Slack channel
  (note: this forum is restricted to administrators):
  * Make a new topic in the "News & Discussion" forums:
    https://forum.codeigniter.com/forum-2.html
  * The content is somewhat organic, but should include any major features and
    changes as well as a link to the User Guide's changelog
* [ ] Run `php admin/create-new-changelog.php <current_version> <new_version>`
  * The above command does the following:
    * Create **user_guide_src/source/changelogs/{next_version}.rst** and add it to
      **index.rst** (See **next-changelog-*.rst**)
    * Create **user_guide_src/source/installation/upgrade_{next_version}.rst** and add it to
      **upgrading.rst** (See **next-upgrading-guide.rst**)
* [ ] Create a PR for new changelog and upgrade for the next version

## Appendix

### Sphinx Installation

You may need to install Sphinx and its dependencies prior to building the User
Guide.

This worked seamlessly on Ubuntu 20.04:
```console
sudo apt install python3-sphinx
sudo pip3 install sphinxcontrib-phpdomain
sudo pip3 install sphinx_rtd_theme
```

### Manual User Guide Process

* Still in the **CodeIgniter4** repo enter the **user_guide_src** directory
* Clear out any old build files: `rm -rf build/`
* Build the HTML version of the User Guide: `make html`
* Build the ePub version of the User Guide: `make epub`
* Switch to the **userguide** repo and create a new branch `release-4.x.x`
* Replace **docs/** with **CodeIgniter4/user_guide_src/build/html**
* Ensure the file **docs/.nojekyll** exists or GitHub Pages will ignore folders
  with an underscore prefix
* Copy **CodeIgniter4/user_guide_src/build/epub/CodeIgniter.epub** to
  **./CodeIgniter4.x.x.epub**
* Commit the changes with "Update for 4.x.x" and push to origin
* Create a new PR from `release-4.x.x` to `develop`:
  * Title: "Update for 4.x.x"
  * Description: blank
* Merge the PR
* Create a new Release:
  * Version: "v4.x.x"
  * Title: "CodeIgniter 4.x.x User Guide"
  * Description: "CodeIgniter 4.x.x User Guide"
* Watch for the "github pages" Environment to make sure the deployment succeeds

The User Guide website should update itself via the deploy GitHub Action. Should
this fail the server must be updated manually. See repo and hosting details in
the deploy script at the User Guide repo.
