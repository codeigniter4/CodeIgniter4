====================
Contribution Signing
====================

We ask that contributions have code commits signed. **This is important in order
to prove, as best we can, the provenance of contributions.**

The developer pushing a commit as part of a PR isn't necessarily the person
who committed it originally, if the commit is not signed. This distorts the
commit history and makes it hard to tell where code came from.

If a person "signs" a commit, they are free to use any name, specifically
one not their own. Again, the commit history cannot be relied on to determine
the origin of the code, if one developer is spoofing another. A malicious person
could commit bad code (for instance a virus) and make it look like another
developer created it.

The best solution, while not fool-proof, is to "securely sign" your
commits. Such commits are digitally signed, with a GPG-key, and
associated with your github account. It still isn't foolproof, because
a malicious developer could create a bogus email and account, but it is
more reliable than an unsigned or a "signed" commit.

If you don't sign your commits, we **may** accept your contribution,
assuming it meets usefulness and contribution guidelines, but only
if it isn't critical code and only after checking it carefully.
If code performs an important role, we will insist that it be signed, and if
it is critical code (however we interpret that), we will insist that your
contributions be securely signed.

Read below to find out how to sign your commits :)

Basic Signing
=============
You must sign your work, certifying that you either wrote the work or
otherwise have the right to pass it on to an open source project.

Setup your commit message user name and email address. See
`Setting your email in Git <https://help.github.com/articles/setting-your-email-in-git/>`_
to set these up globally or for a single repository.

.. code-block:: bash

	git config --global user.email "john.public@example.com"
	git config --global user.name "John Q Public"

Once in place, you merely have to use `--signoff` on your commits to your
CodeIgniter fork.

.. code-block:: bash

	git commit --signoff

or simply

.. code-block:: bash

	git commit -s

This will sign your commits with the information setup in your git config, e.g.

	Signed-off-by: John Q Public <john.public@example.com>

Your IDE may have a "Sign-Off" checkbox in the commit window,
or even an option to automatically sign-off all commits you make. You
could even alias git commit to use the -s flag so you don’t have to think about
it.

By signing your work in this manner, you certify to a "Developer's Certificate
of Origin". The current version of this certificate is in the :doc:`/DCO` file
in the root of this documentation.

Secure Signing
==============

The "basic signing" described above cannot be verified, though it is a great start.
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

Do try to make your commit messages meaningful.
.