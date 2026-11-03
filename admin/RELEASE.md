# Release Process

This guide walks a release manager through publishing a CodeIgniter4 release, from the first merge to the announcement.
It covers both **patch releases** (e.g., `4.7.6`) and **minor releases** (e.g., `4.8.0`).

Follow the steps in order. Every command is in its own code block, so you can copy it as it is.

## Edit History

| Update | Date (UTC) | By |
| --- | --- | --- |
| Guide based on the releases of `4.0.5` and `4.1.0` | February 1, 2021 | MGatner |
| Updated for `4.5.0` | April 7, 2024 | kenjis |
| Updated for `4.6.0` | January 19, 2025 | paulbalandan |
| Updated for `4.7.5` | July 7, 2026 | paulbalandan |
| Rewritten as numbered steps with named variables, for `4.7.6` | November 1, 2026 | paulbalandan |

## Contents

- [Before You Start](#before-you-start)
- [Phase A: Prepare the Branches](#phase-a-prepare-the-branches) (steps 1 to 3)
- [Phase B: Prepare the Content](#phase-b-prepare-the-content) (steps 4 to 9)
- [Phase C: Prep Pull Request](#phase-c-prep-pull-request) (steps 10 to 12)
- [Phase D: Release](#phase-d-release) (steps 13 to 16)
- [Phase E: After the Release](#phase-e-after-the-release) (steps 17 to 21)
- [Troubleshooting](#troubleshooting)
- [Appendix](#appendix)

## Before You Start

### What you need

- Push access to `codeigniter4/CodeIgniter4`, including permission to merge into `master` and to publish releases.
- A clone of your fork with two remotes. The scripts depend on these names:
  - `origin` is your fork. You push release branches here.
  - `upstream` is `codeigniter4/CodeIgniter4`. The scripts push to it directly.
- PHP, Git and the authenticated [GitHub CLI](https://cli.github.com/) (`gh`).
- Administrator rights for the last steps: publishing security advisories and posting on the forum.

Check the remotes and the GitHub CLI:

```bash
git remote -v
```

```bash
gh auth status
```

The working tree must be clean before you start:

```bash
git status --short
```

### Release types

| Type | When | Example |
| --- | --- | --- |
| Patch | Bug fixes and security fixes from `develop` | `4.7.6` |
| Minor | New features from the next minor branch | `4.8.0` |

A version ending in `.0` is a minor release. Steps that apply to only one type are marked **Minor releases only** or **Patch releases only**.

### The variables

The old notation (`4.x.x`, `4.y`, `4.z`) is replaced by four named variables.
Use the same names everywhere in this guide.

| Variable | Meaning | Patch example | Minor example | Old notation |
| --- | --- | --- | --- | --- |
| `VERSION` | The version you are releasing | `4.7.6` | `4.8.0` | `4.x.x` |
| `FEATURE_BRANCH` | The branch that holds the next minor's features. It exists before the release. | `4.8` | `4.8` | `4.y` |
| `NEW_FEATURE_BRANCH` | The branch created after a minor release for the following minor (minor releases only) | not used | `4.9` | `4.z` |
| `NEXT_VERSION` | The next patch version, for which you prepare the changelog at the end | `4.7.7` | `4.8.1` | not named |

To find `FEATURE_BRANCH`, list the minor branches. There is exactly one, and it is the highest version:

```bash
git ls-remote --heads upstream '4.*'
```

To find the latest published release:

```bash
gh release view --repo codeigniter4/CodeIgniter4 --json tagName --jq .tagName
```

Set the variables once. Use the same terminal for the whole release.
If you open a new terminal, run this block again.

```bash
export VERSION=4.7.6
export FEATURE_BRANCH=4.8
export NEW_FEATURE_BRANCH=4.9
export NEXT_VERSION=4.7.7
```

> [!NOTE]
> Edit the values above before you paste them. For a patch release you can leave `NEW_FEATURE_BRANCH` set, since no step reads it.

### How to merge each pull request

This release creates four kinds of pull requests. The merge method matters for one of them.

| Pull request | Merge method |
| --- | --- |
| `$FEATURE_BRANCH.0 Merge code` (minor releases only) | **Create a merge commit** |
| `Prep for $VERSION release` | Squash and merge |
| `$VERSION Ready code` (`develop` into `master`) | **Create a merge commit. Never squash.** |
| Changelog for the next version | Squash and merge |

> [!IMPORTANT]
> The `Ready code` pull request must be merged with **Create a merge commit**, not the usual squash.
> A squash writes a new commit to `master` that `develop` does not contain.
> Then `develop` and `master` diverge, and the post-release sync in step 17 can no longer be a fast-forward.
>
> In the GitHub merge button, open the drop-down arrow and choose **Create a merge commit**.
> The CLI equivalent is `gh pr merge <number> --merge`.

## Phase A: Prepare the Branches

### 1. Merge `develop` into the feature branch

Do this first. If conflicts occur, they are easier to resolve before the release work starts.

```bash
php admin/sync-release-branches.php $FEATURE_BRANCH develop --push
```

The script switches to `$FEATURE_BRANCH` (creating it from `upstream` if needed), merges `upstream/$FEATURE_BRANCH` and then `upstream/develop`, and pushes to `upstream`.

If the merge conflicts, the script stops. Resolve the conflicts, then run:

```bash
git merge --continue
```

```bash
git push upstream HEAD
```

### 2. Merge the feature branch into `develop` (minor releases only)

Skip this step for a patch release.

1. Create a pull request from `$FEATURE_BRANCH` to `develop`:
   - Title: `<FEATURE_BRANCH>.0 Merge code`, for example `4.8.0 Merge code`
   - Description: blank
2. Merge it with **Create a merge commit**.
3. In **Settings > Branches > Branch protection rules**, rename the rule for `$FEATURE_BRANCH` to `$NEW_FEATURE_BRANCH` (for example `4.8` to `4.9`).
4. Delete the merged `$FEATURE_BRANCH` branch.

> [!WARNING]
> Deleting the branch closes every open pull request that targets it.
> Retarget or merge those pull requests first.

### 3. Update your local `develop`

The `prepare-release.php` script in step 10 starts from your local `develop`, so it must match `upstream/develop`.
Step 1 leaves you on `$FEATURE_BRANCH`, so switch back.

```bash
git fetch upstream
```

```bash
git switch develop
```

```bash
git merge --ff-only upstream/develop
```

Check: `git status` reports a clean tree on `develop`.

## Phase B: Prepare the Content

> [!IMPORTANT]
> From step 7 to step 10, **leave your edits uncommitted**.
> The `prepare-release.php` script in step 10 commits them for you, but only changes to files that Git already tracks.
> Do not create new files during these steps.

### 4. Check the pull request labels

The changelog is built from pull request labels. Every pull request to include needs one of these [labels](https://github.com/codeigniter4/CodeIgniter4/labels):

| Label | Use for |
| --- | --- |
| `bug` | Pull requests that fix bugs |
| `enhancement` | Pull requests that improve existing functionality |
| `new feature` | Pull requests for new features |
| `refactor` | Pull requests that refactor |

Pull requests with breaking changes also need the `breaking change` label.

Find merged pull requests without a changelog label:

```bash
php admin/check-pr-labels.php
```

The script prints the `gh pr edit` commands that add the missing labels. Review them, label the pull requests, then run the check again until it reports nothing.

### 5. Check the `admin/` folder for removed files

The deploy scripts do not delete files. Look for hidden or other files that were removed from `admin/` since the last release.

```bash
git diff --name-status upstream/master upstream/develop -- admin/
```

Lines that start with `D` are removed files. Make sure none of them is needed by the deploy.

### 6. Merge the security advisory pull requests

Skip this step if the release has no security fixes.

Merge any security advisory pull requests from the private forks. Each fix must also be documented in the `SECURITY` section of **user_guide_src/source/changelogs/v$VERSION.rst**, because step 7 copies that section into **CHANGELOG.md**.

### 7. Generate `CHANGELOG.md`

The script builds the entry from GitHub's auto-generated release notes plus the `SECURITY` section from step 6.

Preview first:

```bash
php admin/generate-changelog.php $VERSION --dry-run
```

The script lists pull requests that have none of the changelog labels. If any of them belong in the changelog, add a label and run the preview again.

When the preview is right, prepend the entry to **CHANGELOG.md**:

```bash
php admin/generate-changelog.php $VERSION
```

Check: **CHANGELOG.md** starts with the new `v$VERSION` entry.

If the script cannot be used, open the [Releases](https://github.com/codeigniter4/CodeIgniter4/releases) page and click **Draft a new release**.
Choose the tag `v$VERSION` (create it on publish) with the target `develop`, and click **Generate release notes**.
Copy the result into **CHANGELOG.md** and match the format of the existing entries.

### 8. Update the detailed changelog

Edit **user_guide_src/source/changelogs/v$VERSION.rst**.
Remove every section title that has no items under it.

### 9. Update the upgrade guide

Edit the upgrade guide page for the version in **user_guide_src/source/installation/**. The file name is `upgrade_` followed by the version without dots, so `4.7.6` is **upgrade_476.rst**.

Preview the generated content:

```bash
php admin/update-upgrade-guide.php $VERSION --dry-run
```

Fill in the "Config" and "All Changes" sections with the project space files changed since the last release:

```bash
php admin/update-upgrade-guide.php $VERSION
```

Then finish the page by hand:

- The script does not modify a "Config" section that already has content. It prints any missing entries for you to merge manually.
- Add notes to the "Config" entries where users need them.
- Remove every section title that has no items under it.
- **Minor releases only:** change the "from" version in the title to the last patch of the previous minor, for example `from 4.7.x` to `from 4.7.6`.

## Phase C: Prep Pull Request

### 10. Create the release branch

Run the script. It starts from your local `develop`, so step 3 matters.

```bash
php admin/prepare-release.php $VERSION
```

The script does the following:

- Creates the branch `release-$VERSION` from `develop`, replacing any local branch of the same name.
- Sets `CI_VERSION` in **system/CodeIgniter.php** to `$VERSION`.
- Sets `version` (for example `4.7`) and `release` (for example `4.7.6`) in **user_guide_src/source/conf.py**.
- Sets the release date in **user_guide_src/source/changelogs/v$VERSION.rst** (format `Release Date: January 31, 2021`).
- Updates the title and version in **phpdoc.dist.xml**.
- Updates **admin/starter/builds**: `LATEST_RELEASE` becomes `^<minor>` and `NEXT_MINOR` becomes `^<next minor>-dev`.
- Commits everything, including your uncommitted edits to tracked files, as `Prep for $VERSION release`.

> [!NOTE]
> If the major version changes, set `NEXT_MINOR` in **admin/starter/builds** by hand, for example to `^5.0-dev`.

Check what was committed:

```bash
git show --stat HEAD
```

Check: the commit contains **CHANGELOG.md**, the changelog and upgrade guide pages, and the version files above.

### 11. Open the Prep pull request

Push the branch to your fork:

```bash
git push origin HEAD
```

Find the number of the previous release's Prep pull request. You need it for the description below.

```bash
gh pr list --repo codeigniter4/CodeIgniter4 --state merged --search "Prep for release in:title" --limit 3
```

Open a pull request from `release-$VERSION` to `develop`. The description keeps the repository's pull request template, with the extra lines filled in later:

```bash
gh pr create --repo codeigniter4/CodeIgniter4 --base develop --head "$(gh api user --jq .login):release-$VERSION" --title "Prep for $VERSION release" --body-file - <<EOF
**Description**
Updates changelog and version references for $VERSION.

Previous version: #xxxx
Release Code: TODO
New Changelog: TODO

**Checklist:**
- [ ] Securely signed commits
- [ ] Component(s) with PHPDoc blocks, only if necessary or adds value (without duplication)
- [ ] Unit testing, with >80% coverage
- [ ] User guide updated
- [ ] Conforms to style guide
EOF
```

Then edit the description on GitHub:

- Replace `#xxxx` with the number of the previous Prep pull request.
- Replace the two `TODO` values with links when they exist: the `Ready code` pull request from step 13 and the changelog pull request from step 21.

### 12. Merge the Prep pull request

Wait for all tests to pass, review the diff, and merge it with **Squash and merge**.

## Phase D: Release

### 13. Open the `Ready code` pull request

This pull request moves the release commit from `develop` to `master`.

```bash
gh pr create --repo codeigniter4/CodeIgniter4 --base master --head develop --title "$VERSION Ready code" --body ""
```

If your `gh` cannot create a pull request from `develop`, open it on GitHub with the title `$VERSION Ready code`, base `master`, compare `develop`, and a blank description.

### 14. Merge it with a merge commit

> [!IMPORTANT]
> Choose **Create a merge commit**. Do not use **Squash and merge**.
> See [How to merge each pull request](#how-to-merge-each-pull-request) for the reason.

In the GitHub merge button, open the drop-down arrow, pick **Create a merge commit**, and merge.
Alternatively, use the CLI, where `--merge` means a merge commit:

```bash
gh pr merge <number> --merge
```

Then wait for all the tests on `master` to pass.

### 15. Publish the release

Preview the release notes and the contributor list:

```bash
php admin/create-release.php $VERSION --dry-run
```

Publish the release:

```bash
php admin/create-release.php $VERSION
```

The script does the following:

- Fetches GitHub's auto-generated release notes for `master` since the latest release. Override the starting point with `--previous=v<tag>`.
- Credits every pull request author and every commit author or co-author (including those of security advisory fixes), except bots and AI assistants, and marks first-time contributors.
- Publishes the release `CodeIgniter $VERSION` with the tag `v$VERSION` on `master`, linking to **CHANGELOG.md** and the full comparison.

### 16. Verify the release

Publishing starts the "[Verify Release](https://github.com/codeigniter4/CodeIgniter4/actions/workflows/verify-release.yml)" workflow. Follow it:

```bash
gh run list --repo codeigniter4/CodeIgniter4 --workflow verify-release.yml --limit 3
```

The workflow does the following:

- Waits for the "[Deploy Distributable Repos](https://github.com/codeigniter4/CodeIgniter4/actions/workflows/deploy-distributables.yml)" workflow to update **framework**, **appstarter** and **userguide**.
- Installs `appstarter` from Packagist, checks that it pulls framework `v$VERSION`, and runs its tests.
- Checks that the user guide deployments succeeded ("Deploy Production" and "pages build and deployment" in the **userguide** repository) and that **CodeIgniter$VERSION.epub** was added.

Check: the workflow passes.

If it fails, fix the cause and run it again with the tag:

```bash
gh workflow run verify-release.yml --repo codeigniter4/CodeIgniter4 -f version=v$VERSION
```

## Phase E: After the Release

### 17. Fast-forward `develop` to `master`

This brings the merge commit from step 14 into `develop`. It should be a fast-forward.

```bash
php admin/sync-release-branches.php develop master --push
```

### 18. Update or create the feature branch

**Patch releases only:** bring the fixes released from `develop` into the feature branch.

```bash
php admin/sync-release-branches.php $FEATURE_BRANCH develop --push
```

**Minor releases only:** `$FEATURE_BRANCH` was deleted in step 2, so skip the command above.
Create the branch for the following minor from `develop`:

```bash
git fetch upstream
```

```bash
git switch develop
```

```bash
git switch -c $NEW_FEATURE_BRANCH
```

```bash
git push upstream HEAD
```

### 19. Publish the security advisories

Skip this step if the release has no security fixes.

Request the CVEs and publish the advisories that were resolved from private forks.

> [!NOTE]
> Publishing is restricted to administrators.

### 20. Announce the release

Post the announcement on the forum and in the Slack channel.

> [!NOTE]
> The forum is restricted to administrators.

1. Create a new topic in the ["News & Discussion" forum](https://forum.codeigniter.com/forum-2.html).
2. The content is up to you, but include the major features and changes and a link to the User Guide's changelog.

The repository includes the `forum-announcement` skill, which drafts the forum post in myBB format from **CHANGELOG.md** and the detailed changelog.
It lives in **.github/skills/forum-announcement**, which GitHub Copilot Chat reads directly.
For another agent, copy the folder to that agent's skills location. For Claude Code that is **.claude/skills/**:

```bash
mkdir -p .claude/skills && cp -R .github/skills/forum-announcement .claude/skills/
```

Then ask the agent to run the skill with the version, for example `/forum-announcement $VERSION`. It writes **v$VERSION-announcement.txt** in the repository root.
Review the text before you post it, and do not commit the file.

### 21. Prepare the next version's changelog

This creates the changelog and upgrade guide stubs for the next patch version, so the next release starts with them in place.

```bash
php admin/create-new-changelog.php $VERSION $NEXT_VERSION
```

The script does the following:

- Creates **user_guide_src/source/changelogs/v$NEXT_VERSION.rst** from **admin/next-changelog-patch.rst** and adds it to **index.rst**.
- Creates the upgrade guide stub from **admin/next-upgrading-guide.rst** and adds it to **upgrading.rst**.
- Sets `CI_VERSION` to `$NEXT_VERSION-dev`.
- Creates the branch `docs-changelog-$NEXT_VERSION` from `develop` and commits.

When the old and new versions are in different minor series, the script uses the minor templates and branches from the branch you are on, not from `develop`.

Push the branch and open the pull request:

```bash
git push origin HEAD
```

```bash
gh pr create --repo codeigniter4/CodeIgniter4 --base develop --head "$(gh api user --jq .login):docs-changelog-$NEXT_VERSION" --title "docs: add changelog and upgrade for v$NEXT_VERSION" --body ""
```

Merge it with **Squash and merge**. The release is complete.

## Troubleshooting

| Problem | What to do |
| --- | --- |
| A sync script stops with a merge conflict | Resolve the conflicts, run `git merge --continue`, then `git push upstream HEAD`. |
| `prepare-release.php` fails to switch branches | Your tree has uncommitted changes that conflict with `develop`. Run step 3 first and redo the edits. |
| The Prep commit is missing a file | `git add -u` only stages tracked files. Add the file, then run `git commit --amend --no-edit`. |
| The "Verify Release" workflow fails | Fix the cause, then run the workflow again with the command in step 16. |
| You squashed the `Ready code` pull request by mistake | Stop before you publish the release, and ask another maintainer. `master` and `develop` no longer share history. |
| `create-release.php` says the release already exists | The tag is already published. Check the [Releases](https://github.com/codeigniter4/CodeIgniter4/releases) page. |

## Appendix

### Sphinx Installation

You may need to install Sphinx and its dependencies before you build the User Guide.

This worked on Ubuntu 20.04:

```bash
sudo apt install python3-sphinx
```

```bash
sudo pip3 install sphinxcontrib-phpdomain
```

```bash
sudo pip3 install sphinx_rtd_theme
```

### Manual User Guide Process

> [!NOTE]
> This process is normally not needed. The "Deploy Distributable Repos" action builds and deploys the User Guide when a release is published.
> Follow these steps only if the automatic deployment fails.

1. In the **CodeIgniter4** repository, enter the **user_guide_src** directory and clear out any old build files:

   ```bash
   cd user_guide_src
   rm -rf build/
   ```

2. Build the HTML version, then the ePub version:

   ```bash
   make html
   ```

   ```bash
   make epub
   ```

3. Switch to the **userguide** repository and create the branch `release-$VERSION`.
4. Replace **docs/** with **CodeIgniter4/user_guide_src/build/html**.
5. Make sure the file **docs/.nojekyll** exists. Without it, GitHub Pages ignores folders that start with an underscore.
6. Copy **CodeIgniter4/user_guide_src/build/epub/CodeIgniter.epub** to **./CodeIgniter$VERSION.epub**.
7. Commit the changes with the message `Update for $VERSION` and push to `origin`.
8. Create a pull request from `release-$VERSION` to `develop`, with the title `Update for $VERSION` and a blank description, and merge it.
9. Create a release in the **userguide** repository:
   - Version: `v$VERSION`
   - Title: `CodeIgniter $VERSION User Guide`
   - Description: `CodeIgniter $VERSION User Guide`
10. Watch the "github pages" environment to make sure the deployment succeeds.

The User Guide website should update itself through the deploy GitHub Action.
If that fails, the server must be updated manually. See the repository and hosting details in the deploy script of the User Guide repository.
