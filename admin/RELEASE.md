# Release Process

> Documentation guide based on the releases of `4.0.5` and `4.1.0` on January 31, 2021.
>
> Updated for `4.2.3` on August 6, 2022.
>
> -MGatner

## Changelog

When generating the changelog each Pull Request to be included must have one of the following [labels](https://github.com/codeigniter4/CodeIgniter4/labels):
- **bug** ... PRs that fix bugs
- **enhancement** ... PRs to improve existing functionalities
- **new feature** ... PRs for new features
- **refactor** ... PRs to refactor

PRs with breaking changes must have the following additional label:
- **breaking change** ... PRs that may break existing functionalities

To auto-generate, start drafting a new Release and use the "Auto-generate release notes" button.
Copy the resulting content into **CHANGELOG.md** and adjust the format to match the existing content.

## Preparation

* Work off direct clones of the repos so the release branches persist for a time
* Clone both **codeigniter4/CodeIgniter4** and **codeigniter4/userguide** and resolve any necessary PRs
```console
git clone git@github.com:codeigniter4/CodeIgniter4.git
git clone git@github.com:codeigniter4/userguide.git
```
* Vet the **admin/** folders for any removed hidden files (Action deploy scripts *do not remove these*)
* Merge any Security Advisory PRs in private forks

## Process

> Note: Most changes that need noting in the User Guide and docs should have been included
> with their PR, so this process assumes you will not be generating much new content.

* Create a new branch `release-4.x.x`
* Update **system/CodeIgniter.php** with the new version number: `const CI_VERSION = '4.x.x';`
* Update **user_guide_src/source/conf.py** with the new `version = '4.x'` (if applicable) and `release = '4.x.x'`
* Replace **CHANGELOG.md** with the new version generated above
* Set the date in **user_guide_src/source/changelogs/{version}.rst** to format `Release Date: January 31, 2021`
* Create a new changelog for the next version at **user_guide_src/source/changelogs/{next_version}.rst** and add it to **index.rst**
* Create **user_guide_src/source/installation/upgrade_{ver}.rst**, fill in the "All Changes" section, and add it to **upgrading.rst**
* Commit the changes with "Prep for 4.x.x release" and push to origin
* Create a new PR from `release-4.x.x` to `develop`:
    * Title: "Prep for 4.x.x release"
    * Decription: "Updates changelog and version references for `4.x.x`." (plus checklist)
* Let all tests run, then review and merge the PR
* Create a new PR from `develop` to `master`:
    * Title: "4.x.x Ready code"
    * Description: blank
* Merge the PR then create a new Release:
    * Version: "v4.x.x"
    * Title: "CodeIgniter 4.x.x"
    * Description:
```
CodeIgniter 4.x.x release.

See the changelog: https://github.com/codeigniter4/CodeIgniter4/blob/develop/CHANGELOG.md
```
* Watch for the "Deploy Framework" Action to make sure **framework** and **appstarter** get updated
* Run the following commands to install and test AppStarter and verify the new version:
```console
composer create-project codeigniter4/appstarter release-test
cd release-test
composer test && composer info codeigniter4/framework
```
* Verify that the User Guide Actions succeeded:
    * "Deploy User Guide", framework repo
    * "Deploy Production", UG repo
    * "pages-build-deployment", both repos
* Fast-forward `develop` branch to catch the merge commit from `master`
* Update the next minor upgrade branch `4.x`:
```console
git fetch origin
git checkout 4.x
git merge origin/4.x
git merge origin/develop
git push origin HEAD
```
* Publish any Security Advisories that were resolved from private forks
* Announce the release on the forums and Slack channel (note: this forum is restricted to administrators):
    * Make a new topic in the "News & Discussion" forums: https://forum.codeigniter.com/forum-2.html
    * The content is somewhat organic, but should include any major features and changes as well as a link to the User Guide's changelog

## After Publishing Security Advisory

* Send a PR to [PHP Security Advisories Database](https://github.com/FriendsOfPHP/security-advisories).
    * E.g. https://github.com/FriendsOfPHP/security-advisories/pull/606
    * See https://github.com/FriendsOfPHP/security-advisories#contributing
    * Don't forget to run `php -d memory_limit=-1 validator.php`, before submitting the PR

## Appendix

### Sphinx Installation

You may need to install Sphinx and its dependencies prior to building the User Guide.
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
* Ensure the file **docs/.nojekyll** exists or GitHub Pages will ignore folders with an underscore prefix
* Copy **CodeIgniter4/user_guide_src/build/epub/CodeIgniter.epub** to **./CodeIgniter4.x.x.epub**
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

The User Guide website should update itself via the deploy GitHub Action. Should this fail
the server must be updated manually. See repo and hosting details in the deploy script
at the User Guide repo.
