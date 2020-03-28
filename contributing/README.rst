###########################
Contributing to CodeIgniter
###########################

- `Contribution guidelines <./guidelines.rst>`_
- `Contribution workflow <./workflow.rst>`_
- `Contribution signing <./signing.rst>`_
- `Contribution CSS <./css.rst>`_
- `Framework internals <./internals.rst>`_
- `CodeIgniter documentation <./documentation.rst>`_
- `PHP Style Guide <./styleguide.rst>`_
- `Developer's Certificate of Origin <../DCO.txt>`_

CodeIgniter is a community driven project and accepts contributions of code
and documentation from the community. These contributions are made in the form
of Issues or `Pull Requests <https://help.github.com/articles/using-pull-requests/>`_
on the `CodeIgniter4 repository <https://github.com/codeigniter4/CodeIgniter4>`_ on GitHub.

Issues are a quick way to point out a bug. If you find a bug or documentation
error in CodeIgniter then please check a few things first:

- There is not already an open Issue
- The issue has already been fixed (check the develop branch, or look for
  closed Issues)
- Is it something really obvious that you fix it yourself?

Reporting issues is helpful but an even better approach is to send a Pull
Request, which is done by "Forking" the main repository and committing to your
own copy. This will require you to use the version control system called Git.

*******
Support
*******

Please note that GitHub is not for general support questions! If you are
having trouble using a feature of CodeIgniter, ask for help on our
`forums <http://forum.codeigniter.com/>`_ instead.

If you are not sure whether you are using something correctly or if you
have found a bug, again - please ask on the forums first.

********
Security
********

Did you find a security issue in CodeIgniter?

Please *don't* disclose it publicly, but e-mail us at security@codeigniter.com,
or report it via our page on `HackerOne <https://hackerone.com/codeigniter>`_.

If you've found a critical vulnerability, we'd be happy to credit you in our
`ChangeLog <https://codeigniter4.github.io/CodeIgniter4/changelogs/index.html>`_.

****************************
Tips for a Good Issue Report
****************************

Use a descriptive subject line (eg parser library chokes on commas) rather than
a vague one (eg. your code broke).

Address a single issue in a report.

Identify the CodeIgniter version (eg 4.0.1) and the component if you know it (eg. parser library)

Explain what you expected to happen, and what did happen.
Include error messages and stacktrace, if any.

Include short code segments if they help to explain.
Use a pastebin or dropbox facility to include longer segments of code or
screenshots - do not include them in the issue report itself.
This means setting a reasonable expiry for those, until the issue is resolved or closed.

If you know how to fix the issue, you can do so in your own fork & branch, and submit a pull request.
The issue report information above should be part of that.

If your issue report can describe the steps to reproduce the problem, that is great.
If you can include a unit test that reproduces the problem, that is even better,
as it gives whoever is fixing it a clearer target!
