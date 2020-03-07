######################
CodeIgniter User Guide
######################

******************
Setup Instructions
******************

The CodeIgniter user guide uses Sphinx to manage the documentation and
output it to various formats. Pages are written in human-readable
`ReStructured Text <http://sphinx.pocoo.org/rest.html>`_ format.

Prerequisites
=============

Sphinx requires Python 2, which may already be installed if you are running OS X or Linux.
You can confirm in a Terminal window by executing the ``python`` command
without any parameters. It should load up and tell you which version you have
installed. If you're not on 2.7+, go ahead and install 2.7+ from
`Python.org <https://www.python.org/downloads/>`_

Installation
============

1. Install `pip <https://packaging.python.org/guides/installing-using-linux-tools/>`_ (package manager).
2. ``pip install "sphinx==1.8.5"``
3. ``pip install "sphinxcontrib-phpdomain>=0.7.0"``
4. Reboot your operating system
5. ``cd user_guide_src``
6. ``make html``

Editing and Creating Documentation
==================================

All of the source files exist under *source/* and is where you will add new
documentation or modify existing documentation. Just as with code changes,
we recommend working from feature branches and making pull requests to
the *develop* branch of this repo.

So where's the HTML?
====================

Obviously, the HTML documentation is what we care most about, as it is the
primary documentation that our users encounter. Since revisions to the built
files are not of value, they are not under source control. This also allows
you to regenerate as necessary if you want to "preview" your work. Generating
the HTML is very simple. From the root directory of your user guide repo
fork issue the command you used at the end of the installation instructions::

	make html

You will see it do a whiz-bang compilation, at which point the fully rendered
user guide and images will be in *build/html/*. After the HTML has been built,
each successive build will only rebuild files that have changed, saving
considerable time. If for any reason you want to "reset" your build files,
simply delete the *build* folder's contents and rebuild.

***************
Style Guideline
***************

Please refer to /contributing/documentation.rst for general guidelines for
using Sphinx to document CodeIgniter.
