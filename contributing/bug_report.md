# Reporting a Bug

## Issues

Issues are a quick way to point out a bug. If you find a bug or documentation error in CodeIgniter then please make sure that:

1. There is not already an open [Issue](https://github.com/codeigniter4/CodeIgniter4/issues)
2. The Issue has not already been fixed (check the develop branch or look for [closed Issues](https://github.com/codeigniter4/CodeIgniter4/issues?q=is%3Aissue+is%3Aclosed))
3. It's not something really obvious that you can fix yourself

Reporting Issues is helpful, but an even [better approach](./workflow.md) is to send a
[Pull Request](https://help.github.com/en/articles/creating-a-pull-request), which is done by
[Forking](https://help.github.com/en/articles/fork-a-repo) the main repository and making
a [Commit](https://help.github.com/en/desktop/contributing-to-projects/committing-and-reviewing-changes-to-your-project)
to your own copy of the project. This will require you to use the version control system called [Git](https://git-scm.com/).

## Support

Please note that GitHub is not for general support questions! If you are
having trouble using a feature, you can:

- Start a new thread on our [Forums](http://forum.codeigniter.com/)
- Ask your questions on [Slack](https://join.slack.com/t/codeigniterchat/shared_invite/zt-rl30zw00-obL1Hr1q1ATvkzVkFp8S0Q)

If you are not sure whether you are using something correctly or if you
have found a bug, again - please ask on the forums first.

## Security

See [SECURITY.md](../SECURITY.md).

## Tips for a Good Issue Report

Use a descriptive subject line (eg parser library chokes on commas)
rather than a vague one (eg. your code broke).

Address a single issue in a report.

Identify the CodeIgniter version (eg 4.0.1) and the component if you
know it (eg. parser library)

Explain what you expected to happen, and what did happen. Include error
messages and stacktrace, if any.

Include short code segments if they help to explain. Use a pastebin or
dropbox facility to include longer segments of code or screenshots - do
not include them in the issue report itself. This means setting a
reasonable expiry for those, until the issue is resolved or closed.

If you know how to fix the issue, you can do so in your own fork &
branch, and submit a pull request. The issue report information above
should be part of that.

If your issue report can describe the steps to reproduce the problem,
that is great. If you can include a unit test that reproduces the
problem, that is even better, as it gives whoever is fixing it a clearer
target!
