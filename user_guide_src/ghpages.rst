#########################
Generating the User Guide
#########################

The intent is, eventually, for the in-progress user guide to be automatically
generated as part of a PR merge. This writeup explains how it can be done manually
in the meantime.

The user guide takes advantage of Github pages, where the "gh-pages" branch of
a repo, containing HTML only, is accessible through `github.io
<https://bcit-ci.github.io/CodeIgniter4>`_.

Setup for Repo Maintainers
==========================

You already have the repo cloned into ``CodeIgniter4`` in a projects folder.
Create another folder at the same level as this, ``CodeIgniter4-guide``.
Clone the CodeIgniter4 repo again, into ``CodeIgniter4-guide/html``.

Inside the ``html`` folder, ``git checkout gh-pages``.
All you should see is the generated HTML for the user guide.

Re-generating the User Guide
============================

In the ``user_guide_src`` folder, you generate a conventional user guide,
for testing, using the command::

	make html

An additional target has been configured, which will generate the same
HTML but inside the ``html`` folder of the second repo clone::

	make ghpages

After making this target, update the online user guide by switching to
the ``CodeIgniter4-guide/html`` folder, and then::

	git add .
	git commit -S -m "Suitable comment"
	git push origin gh-pages

Process
=======

There should be only one maintainer doing this, to avoid collisions.
The user guide would get regenerated whenever there is a PR merge
that affects it.

Note: You might have to delete the ``user_guide_src/doctree`` folder before
making the ``gh-pages`` version of the guide, to make sure that the TOC
is properly rebuilt, especially if you are rebuilding the ``html`` target a number of times.
