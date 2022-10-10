# Contribution Workflow

Much of the workflow for contributing to CodeIgniter (or any project)
involves understanding how [Git](https://git-scm.com/) is used to manage
a shared repository and contributions to it. Examples below use the Git
bash shell, to be as platform neutral as possible. Your IDE may make
some of these easier.

Some conventions used below, which you will need to provide appropriate
values for when you try these:

    ALL_PROJECTS    // folder location with all your projects in subfolders, eg /lampp/htdocs
    YOUR_PROJECT    // folder containing the project you are working on, inside ALL_PROJECTS
    ORIGIN_URL      // the cloning URL for your repository fork
    UPSTREAM_URL    // the cloning URL for the CodeIgniter4 repository

## Branching

- All bug fix PRs should be sent to the __"develop"__ branch, this is where the next bug fix version will be developed.
- PRs with any enhancement should be sent to next minor version branch, e.g. __"4.3"__

The "master" branch will always contain the latest stable
version and is kept clean so a "hotfix" (e.g: an emergency security
patch) can be applied to master to create a new version, without
worrying about other features holding it up. Any sent to "master" will be
closed automatically.

If you have multiple changes to submit, please
place each change into their own branch on your fork.

One thing at a time: a pull request should only contain one change. That
does not mean only one commit, but one change - however many commits it
took. The reason for this is that if you change X and Y but send a
single pull request for both at the same time, we might really want X
but disagree with Y, meaning we cannot merge the request. Using the
Git-Flow branching model you can create new branches for both of these
features and send two requests.

## Forking

You work with a fork of the CodeIgniter4 repository. This is a copy of
our repository, in your GitHub account. You can make changes to your
forked repository, while you cannot do the same with the shared one -
you have to submit pull requests to it instead.

[Creating a fork](https://help.github.com/articles/fork-a-repo/) is done
through the GitHub website. Navigate to [our
repository](https://github.com/codeigniter4/CodeIgniter4), click the
**Fork** button in the top-right of the page, and choose which account
or organization of yours should contain that fork.

## Cloning

You *could* work on your repository using GitHub's web interface, but
that is awkward. Most developers will clone their repository to their
local system, and work with it there.

On GitHub, navigate to your forked repository, click **Clone or
download**, and copy the cloning URL shown. We will refer to this as
ORIGIN\_URL.

Clone your repository, leaving a local folder for you to work with:

```console
> cd ALL_PROJECTS
> git clone ORIGIN_URL
```

## Syncing Your Repository

Within your local repository, Git will have created an alias,
**origin**, for the GitHub repository it is bound to. You want to create
an alias for the shared repository as well, so that you can "synch" the
two, making sure that your repository includes any other contributions
that have been merged by us into the shared repo:

```console
> git remote add upstream UPSTREAM_URL
```

Then synchronizing is done by pulling from us and pushing to you. This
is normally done locally, so that you can resolve any merge conflicts.
For instance, to synchronize **develop** branches:

```console
> git fetch upstream
> git switch develop
> git merge upstream/develop
> git push origin develop
```

You might get merge conflicts when you merge. It is your
responsibility to resolve those locally, so that you can continue
collaborating with the shared repository. Basically, the shared
repository is updated in the order that contributions are merged into
it, not in the order that they might have been submitted. If two PRs
update the same piece of code, then the first one to be merged will take
precedence, even if it causes problems for other contributions.

It is a good idea to synchronize repositories when the shared one
changes.

## Branching Revisited

The top of this page talked about the **master** and **develop**
branches. The *best practice* for your work is to create a *feature
branch* locally, to hold a group of related changes (source, unit
testing, documentation, changelog, etc).

This local branch should be named appropriately, for instance
"fix/problem123" or "new/mind-reader". The slashes in these branch names
is optional, and implies a sort of namespacing if used.

- All bug fix PRs should be sent to the __"develop"__ branch, this is where the next bug fix version will be developed.
- PRs with any enhancement should be sent to next minor version branch, e.g. __"4.3"__

For instance, if you send a PR to __"develop"__ branch, make sure you are in the *develop* branch, and create a
new bugfix branch, based on *develop*, for a new feature you are
creating:

```console
> git switch develop
> git switch -c fix/problem123
```

If you send a PR with an enhancement, make sure you are in the *next minor version* branch,
and create a new feature branch, based on, e.g., *4.3*, for a new feature you are creating:

```console
> git switch 4.3
> git switch -c new/mind-reader
```

Saving changes only updates your local working area.

## Committing

Your local changes need to be *committed* to save them in your local
repository. This is where [contribution signing](./signing.md) comes
in.

Now we don't have detailed rules on commits and its messages. But
[atomic commit](https://en.wikipedia.org/wiki/Atomic_commit#Atomic_commit_convention) is recommended.
Keep your commits atomic. One commit for one change.

There are some references for writing good commit messages:

- [Git Best Practices — AFTER Technique - DZone DevOps](https://dzone.com/articles/git-best-practices-after-technique-1)
- [Semantic Commit Messages](https://gist.github.com/joshbuchea/6f47e86d2510bce28f8e7f42ae84c716)
- [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)

If there are intermediate commits that are not meaningful to the overall PR,
such as "Fix error on style guide", "Fix phpstan error", "Fix mistake in code",
and other related commits, you can squash your commits so that we can have a clean commit history.
But it is not a must.

### Commit Messages

Commit messages are important. They communicate the intent of a specific change, concisely.
They make it easier to review code, and to find out why a change was made
if the code history is examined later.

The audience for your commit messages will be the codebase maintainers,
any code reviewers, and debuggers trying to figure out when a bug might
have been introduced.

Make your commit messages meaningful.

Commit messages are expected to be descriptive of **why** and what you changed specifically.
Commit messages like "Fixes #1234" would be asked by the reviewer to be revised.

You can have as many commits in a branch as you need to "get it right".
For instance, to commit your work from a debugging session:

```console
> git add .
> git commit -S -m "Fix the broken reference problem"
```

Just make sure that your commits in a feature branch are all related.

### GPG-Signing Old Commits

Any developer can forget GPG-signing their commits with the option `-S`, like `git commit -S -m 'Signed GPG'`. In such a case, all you need to do is the following:

Latest commit only:
```console
> git switch your-branch
> git commit --amend --no-edit --no-verify -S
> git push --force-with-lease origin your-branch
```

All commits:
```console
> git switch your-branch
> git rebase -i --root --exec 'git commit --amend --no-edit --no-verify -S'
> git push --force-with-lease origin your-branch
```

As a faster alternative, you can still securely sign commits without the `-S` option in `git commit` by setting `git config --global commit.gpgsign true` and `git config --global user.signingkey 3AC5C34371567BD2` to all local repositories. Without the `--global` option, the change is applied to one local repository only.

> **Note**
> `3AC5C34371567BD2` is your GPG Key ID

### Changing a Commit Message

See <https://docs.github.com/en/pull-requests/committing-changes-to-your-project/creating-and-editing-commits/changing-a-commit-message>.

### When You Work on Two Features

If you are working on two features at a time, then you will want to
switch between them to keep the contributions separate. For instance:

```console
> git switch new/mind-reader
> ## work away
> git add .
> git commit -S -m "Added adapter for abc"
> git switch fix/issue-123
> ## work away
> git add .
> git commit -S -m "Fixed problem in DEF\Something"
> git switch develop
```

The last switch makes sure that you end up in your *develop* branch as
a starting point for your next session working with your repository.
This is a good practice, as it is not always obvious which branch you
are working in.

## Pushing Your Branch

At some point, you will decide that your feature branch is complete, or
that it could benefit from a review by fellow developers.

> **Note**
> Remember to sync your local repo with the shared one before pushing!
It is a lot easier to resolve conflicts at this stage.

Synchronize your repository:

```console
> git fetch upstream
> git switch develop
> git merge upstream/develop
> git push origin develop
```

Bring your feature branch up to date:

```console
> git switch fix/issue-123
> git rebase upstream/develop
```

And finally push your local branch to your GitHub repository:

```console
> git push --force-with-lease origin fix/issue-123
```

## Pull Requests

On GitHub, you propose your changes one feature branch at a time, by
switching to the branch you wish to contribute, and then clicking on
"New pull request".

Make sure the pull request is for the shared __"develop"__ or next minor version branch, e.g. __"4.3"__, or it
may be rejected.

Make sure that the PR title is helpful for the maintainers and other
developers. Add any comments appropriate, for instance asking for
review.

> **Note**
> If you do not provide a title or description for your PR, the odds of it being summarily rejected
rise astronomically.

When your PR is submitted, a continuous integration task will be
triggered, running all the unit tests as well as any other checking we
have configured for it. If the unit tests fail, or if there are merge
conflicts, your PR will not be mergeable until those are fixed.

Fix such changes locally, commit them properly, and then push your
branch again. That will update the PR automatically, and re-run the CI
tests. You don't need to raise a new PR.

If your PR does not follow our contribution guidelines, or is
incomplete, the codebase maintainers will comment on it, pointing out
what needs fixing.

### Labeling PRs

If you have the privilege of labeling PRs, you can help the maintainers.

Label your PRs with the one of the following [labels](https://github.com/codeigniter4/CodeIgniter4/labels):
- **bug** ... PRs that fix bugs
- **enhancement** ... PRs to improve existing functionalities
- **new feature** ... PRs for new features
- **refactor** ... PRs to refactor

And if your PRs have the breaking changes, label the following label:
- **breaking change** ... PRs that may break existing functionalities

## Updating Your Branch

If you are asked for changes in the review, commit the fix in your branch and push it to GitHub again.

If the __"develop"__ or next minor version branch, e.g. __"4.3"__, progresses and conflicts arise that prevent merging, or if you are asked to *rebase*,
do the following:

Synchronize your repository:

```console
> git fetch upstream
> git switch develop
> git merge upstream/develop
> git push origin develop
```

(Optional) Create a new branch as a backup, just in case:

```console
> git branch fix/problem123.bk fix/problem123
```

Bring your feature branch up to date:

```console
> git switch fix/problem123
> git rebase upstream/develop
```

You might get conflicts when you rebase. It is your
responsibility to resolve those locally, so that you can continue
collaborating with the shared repository.

And finally push your local branch to your GitHub repository:

```console
> git push --force-with-lease origin fix/problem123
```

## If You Sent to the Wrong Branch

If you have sent a PR to the wrong branch, you need to create a new PR branch.

When you have the PR branch `feat-abc` and you should have sent the PR to `4.3`,
but you created the PR branch from `develop` and sent a PR.

Copy the IDs of any commits you made that you want to keep:

```console
> git log
```

Update your `4.3` branch:

```console
> git fetch upstream
> git switch 4.3
> git merge upstream/4.3
> git push origin 4.3
```

(Optional) Create a new branch as a backup, just in case:

```console
> git branch feat-abc.bk feat-abc
```

Rebase your PR branch from `develop` onto `4.3`:

```console
> git rebase --onto 4.3 develop feat-abc
```

Force push.

```console
> git push --force-with-lease origin feat-abc
```

On the GitHub PR page, change the base branch to the correct branch `4.3`.

## Cleanup

If your PR is accepted and merged into the shared repository, you can
delete that branch in your GitHub repository as well as locally.
