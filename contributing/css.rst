================
Contribution CSS
================

CodeIgniter uses SASS to generate the debug toolbar's CSS. Therefore, you
will need to install it first. You can find further instructions on the
official website: https://sass-lang.com/install

Compile SASS files
==================

Open your terminal, and navigate to CodeIgniter's root folder. To generate
the CSS file, use the following command: ``sass --no-cache --sourcemap=none admin/css/debug-toolbar/toolbar.scss system/Debug/Toolbar/Views/toolbar.css``

Details:
- ``--no-cache`` is a parameter defined to disable SASS cache, this prevents
  a "cache" folder from being created
- ``--sourcemap=none`` is a parameter which prevents soucemap files from
  being generated
- ``admin/css/debug-toolbar/toolbar.scss`` is the SASS source
- ``system/Debug/Toolbar/Views/toolbar.css`` is he CSS destination
