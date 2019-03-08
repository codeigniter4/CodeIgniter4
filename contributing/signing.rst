====================
Contribution Signing
====================

We ask that contributions have code commits signed. **This is important in order
to prove, as best we can, the provenance of contributions.**

The developer pushing a commit as part of a PR isn't necessarily the person
who committed it originally, if the commit is not signed. This distorts the
commit history and makes it hard to tell where code came from.

If a person "signs off" a commit, they are free to use any name, specifically
one not their own. Again, the commit history cannot be relied on to determine
the origin of the code, if one developer is spoofing another. A malicious person
could commit bad code (for instance a virus) and make it look like another
developer created it.

The best solution, while not fool-proof, is to "securely sign" your
commits. Such commits are digitally signed, with a GPG-key
associated with your github account. It still isn't foolproof, because
a malicious developer could create a bogus email and account, but it is
more reliable than an unsigned or a "signed-off by" commit.

If you don't sign your commits, we **may** accept your contribution,
assuming it meets usefulness and contribution guidelines, but only
if it isn't critical code and only after checking it carefully.
If code performs an important role, we will insist that it be securely signed.

Read below to find out how to sign your commits :)


Secure Signing
==============

To verify your commits, you will need to
setup a GPG key, and attach it to your github account.

See the `git tools <https://git-scm.com/book/en/v2/Git-Tools-Signing-Your-Work>`_
page for directions on doing this. The complete story is part of
`Github help <https://help.github.com/categories/gpg/>`_.

The basic steps are

-  `generate your GPG key <https://help.github.com/articles/generating-a-new-gpg-key/>`_, and copy the ASCII representation of it.
-  `Add your GPG key to your Github account <https://help.github.com/articles/adding-a-new-gpg-key-to-your-github-account/>`_.
-   `Tell Git <https://help.github.com/articles/telling-git-about-your-gpg-key/>`_ about your GPG key.
-   `Set default signing <https://help.github.com/articles/signing-commits-using-gpg/>`_ to have all of your commits securely signed automatically.
-   Provide your GPG key passphrase, as prompted, when you do a commit.

Depending on your IDE, you may have to do your Git commits from your Git bash shell
to use the **-S** option to force the secure signing.

Commit Messages
===============

Regardless of how you sign a commit, commit messages are important too.
They communicate the intent of a specific change, concisely.
They make it easier to review code, and to find out why a change was made
if the code history is examined later.

The audience for your commit messages will be the codebase maintainers, any
code reviewers, and debuggers trying to figure out when a bug might have been
introduced.

Make your commit messages meaningful.
