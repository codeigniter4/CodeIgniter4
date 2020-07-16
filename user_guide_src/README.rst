######################
CodeIgniter User Guide
######################

******************
Setup Instructions
******************

The CodeIgniter user guide uses Sphinx to manage the documentation and
output it to various formats. Pages are written in human-readable
`ReStructured Text <https://en.wikipedia.org/wiki/ReStructuredText>`_ format.

Prerequisites
=============

Python
------

Sphinx requires Python 3.5+, which may already be installed if you are running
OS X or Linux. You can confirm in a Terminal window by executing ``python``
or ``python3``.

.. code-block:: bash

	python --version
	Python 2.7.17

	python3 --version
	Python 3.6.9

	# For Windows using the Python Launcher
	py -3 --version
	Python 3.8.1

If you're not on 3.5+, go ahead and install the latest 3.x version from
`Python.org <https://www.python.org/downloads/>`_. Linux users should use their
operating systems' built-in Package Managers to update.

pip
---

Now that you have Python 3.x up and running, we will be installing
`pip <https://pip.pypa.io/en/stable/>`_ (The Python Package Installer).

You can check if you have pip installed with ``pip`` or ``pip3``.
As you can see pip follow the same naming convention as Python.
Please take note that it should say ``python 3.x`` at the very end.

.. code-block:: bash

	pip --version
	pip 9.0.1 from /usr/lib/python2.7/dist-packages (python 2.7)

	pip3 --version
	pip 9.0.1 from /usr/lib/python3/dist-packages (python 3.6)

	# For Windows using the Python Launcher
	py -3 -m pip --version
	pip 20.0.2 from C:\Users\<username>\AppData\Local\Programs\Python\Python38\lib\site-packages\pip (python 3.8)

Linux
^^^^^

`Installing pip/setuptools/wheel with Linux Package Managers
<https://packaging.python.org/guides/installing-using-linux-tools/>`_

Others
^^^^^^

pip is already installed if you are using Python 3.5+ downloaded from
`Python.org <https://www.python.org/downloads/>`_.

Installation
============

Now we need to install Sphinx and it's dependencies. Choose ``pip`` or ``pip3``
depending on operative system. After this step you need to restart your Terminal
window as Python won't find all applications we just installed othervise.

.. code-block:: bash

	pip install -r user_guide_src/requirements.txt

	pip3 install -r user_guide_src/requirements.txt

	# For Windows using the Python Launcher
	py -3 -m pip install -r user_guide_src/requirements.txt

It's time to wrap things up and generate the documentation.

.. code-block:: bash

	cd user_guide_src
	make html

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
