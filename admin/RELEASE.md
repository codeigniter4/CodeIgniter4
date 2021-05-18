# Release Process

> Documentation guide based on the releases of `4.0.5` and `4.1.0` on January 31, 2021.
> Updated for `4.1.2` on May 17, 2021.
> -MGatner

## Preparation

* Work off direct clones of the repos so the release branches persist for a time
* Clone both **codeigniter4/CodeIgniter4** and **codeigniter4/userguide** and resolve any necessary PRs
* Vet the **admin/** folders for any removed hidden files (Action deploy scripts *do not remove these*)
* Generate a new **CHANGELOG.md** ahead of time using [GitHub Changelog Generator](https://github.com/github-changelog-generator/github-changelog-generator):
```
github_changelog_generator --user codeigniter4 --project codeigniter4 --since-tag v4.0.4 --future-release v4.0.5 --token {your_github_token}
...or
github_changelog_generator --user codeigniter4 --project codeigniter4 --since-commit "2021-02-01 13:26:28" --future-release v4.0.5 --token {your_github_token}
```

## CodeIgniter4

> Note: Most changes that need noting in the User Guide and docs should have been included
> with their PR, so this process assumes you will not be generating much new content.

* Create a new branch `release-4.x.x`
* Update **system/CodeIgniter.php** with the new version number: `const CI_VERSION = '4.x.x';`
* Update **user_guide_src/source/conf.py** with the new `version = '4.x'` (if applicable) and `release = '4.x.x'`
* Replace **CHANGELOG.md** with the new version generated above
* Set the date in **user_guide_src/source/changelogs/{version}.rst** to format `Release Date: January 31, 2021`
* Create a new changelog for the next version at **user_guide_src/source/changelogs/{next_version}.rst** and add it to **index.rst**
* If there are additional upgrade steps, create **user_guide_src/source/installation/upgrade_{ver}.rst** and add it to **upgrading.rst**
* Commit the changes with "Prep for 4.x.x release" and push to origin
* Create a new PR from `release-4.x.x` to `develop`:
	* Title: "Prep for 4.x.x release"
	* Decription: "Updates changelog and version references for `4.x.x`." (plus checklist)
* Let all tests run, then review and merge the PR
* Create a new PR from `develop` to `master`:
	* Title: "4.x.x Ready code"
	* Description: blank
* Merge the PR then fast-forward `develop` to catch the merge commit
* Create a new Release:
	* Version: "v4.x.x"
	* Title: "CodeIgniter 4.x.x"
	* Description:
```
CodeIgniter 4.x.x release. 

See the changelog: https://github.com/codeigniter4/CodeIgniter4/blob/develop/CHANGELOG.md
```
* Watch for the "Deploy Framework" Action to make sure **framework** and **appstarter** get updated

## User Guide

> See "Sphinx Installation" below if you run into issues during `make`

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

## Website

Currently the User Guide on the website has to be updated manually. Visit Jim's user home
where the served directory **codeigniter.com** exists. Copy the latest **docs** folder from
the User Guide repo to **public/userguide4** and browse to the website to make sure it works.

## Announcement

> Note: This forum is restricted to administrators.

* Make a new topic in the "News & Discussion" forums: https://forum.codeigniter.com/forum-2.html
* The content is somewhat organic, but should include any major features and changes as well as a link to the User Guide's changelog

## Appendix

### Sphinx Installation

You may need to install Sphinx and its dependencies prior to building the User Guide.
This worked seamlessly on Ubuntu 20.04:
```
sudo apt install python3-sphinx
sudo pip3 install sphinxcontrib-phpdomain
sudo pip3 install sphinx_rtd_theme
```
