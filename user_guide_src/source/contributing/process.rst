====================
Contribution Process
====================

Branching
=========

CodeIgniter uses the `Git-Flow
<http://nvie.com/posts/a-successful-git-branching-model/>`_ branching model
which requires all pull requests to be sent to the "develop" branch. This is
where the next planned version will be developed. The "master" branch will
always contain the latest stable version and is kept clean so a "hotfix" (e.g:
an emergency security patch) can be applied to master to create a new version,
without worrying about other features holding it up. For this reason all
commits need to be made to "develop" and any sent to "master" will be closed
automatically. If you have multiple changes to submit, please place all
changes into their own branch on your fork.

One thing at a time: A pull request should only contain one change. That does
not mean only one commit, but one change - however many commits it took. The
reason for this is that if you change X and Y but send a pull request for both
at the same time, we might really want X but disagree with Y, meaning we
cannot merge the request. Using the Git-Flow branching model you can create
new branches for both of these features and send two requests.

Signing
=======
You must sign your work, certifying that you either wrote the work or
otherwise have the right to pass it on to an open source project. git makes
this easy as you merely have to use `--signoff` on your commits to your
CodeIgniter fork.

.. code-block:: bash

	git commit --signoff

or simply

.. code-block:: bash

	git commit -s

This will sign your commits with the information setup in your git config, e.g.

	Signed-off-by: John Q Public <john.public@example.com>

If you are using Tower there is a "Sign-Off" checkbox in the commit window. You
could even alias git commit to use the -s flag so you don’t have to think about
it.

By signing your work in this manner, you certify to a "Developer's Certificate
of Origin". The current version of this certificate is in the :doc:`/DCO` file
in the root of this documentation.
