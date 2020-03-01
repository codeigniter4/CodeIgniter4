================
Contribution CSS
================

CodeIgniter uses SASS to generate the debug toolbar and the "Welcome" page CSS.
Therefore, you will need to install it first. You can find further instructions
on the official website: https://sass-lang.com/install

**The colors used must comply with the graphic charter.**



Debug toolbar
=============

Open your terminal, and navigate to CodeIgniter's root folder. To generate
the CSS file, use the following command:
``sass --no-cache --sourcemap=none admin/sass/debug-toolbar/toolbar.scss system/Debug/Toolbar/Views/toolbar.css``

Details:
- ``--no-cache`` is a parameter defined to disable SASS cache, this prevents a "cache" folder from being created
- ``--sourcemap=none`` is a parameter which prevents soucemap files from being generated
- ``admin/sass/debug-toolbar/toolbar.scss`` is the SASS source
- ``system/Debug/Toolbar/Views/toolbar.css`` is he CSS destination



Welcome page
============

**Step 1**

Open your terminal, and navigate to CodeIgniter's root folder. To generate
the CSS file, use the following command:
``sass --no-cache --sourcemap=none admin/sass/welcome-page/welcome.scss admin/sass/welcome-page/welcome.css``

Details:
- ``--no-cache`` is a parameter defined to disable SASS cache, this prevents a "cache" folder from being created
- ``--sourcemap=none`` is a parameter which prevents soucemap files from being generated
- ``admin/sass/welcome-page/welcome.scss`` is the SASS source
- ``admin/sass/welcome-page/welcome.css`` is he CSS destination

**Step 2**

The "Welcome" page shouldn't contain links to external resources, like
CSS stylesheets. Therefore, you need to:
- Copy the content of the file ``admin/sass/welcome-page/welcome.css``
- Paste it in the file ``app/Views/welcome_message.php``
- Delete the file ``admin/sass/welcome-page/welcome.css``



Documentation
=============

The documentation is generated using Sphinx and the RTD theme.

**Instructions on how to update the theme**

1/ Backup CI's custom files:
- ``user_guide_src/source/_themes/sphinx_rtd_theme/theme.conf``
- ``user_guide_src/source/_themes/sphinx_rtd_theme/static/css/citheme.css``
- ``user_guide_src/source/_themes/sphinx_rtd_theme/static/js/citheme.js``
- ``user_guide_src/source/_themes/sphinx_rtd_theme/static/img/ci-background.png``
2/ Download the latest version of the RTD theme: https://github.com/readthedocs/sphinx_rtd_theme
3/ Place the latest version in the folder ``user_guide_src/source/_themes/sphinx_rtd_theme/``
3/ Restore CI's custom files

You may want to check if:
- The configuration file ``theme.conf`` has not changed in the latest release of the RTD theme
- The file path to the original CSS (from the RTD theme) is valid in ``citheme.css` (@import)



Graphic charter
===============

**Themes**

Dark: `#252525` / `rgb(37, 37, 37)`

Light: `#FFFFFF` / `rgb(255, 255, 255)`


**Glossy colors**

Blue: `#5BC0DE` / `rgb(91, 192, 222)`

Gray: `#434343` / `rgb(67, 67, 67)`

Green: `#9ACE25` / `rgb(154, 206, 37)`

Orange: `#DD8615` / `rgb(221, 134, 21)`

Red: `#DD4814` / `rgb(221, 72, 20)`


**Matt colors**

Blue: `#D8EAF0` / `rgb(216, 234, 240)`

Gray: `#DFDFDF` / `rgb(223, 223, 223)`

Green: `#DFF0D8` / `rgb(223, 240, 216)`

Orange: `#FDC894` / `rgb(253, 200, 148)`

Red: `#EF9090` / `rgb(239, 144, 144)`


**Subtle colors**

Blue: `#E8EFF1` / `rgb(232, 239, 241)`

Gray: `#FAFAFA` / `rgb(250, 250, 250)`

Green: `#EFF5ED` / `rgb(239, 245, 237)`

Orange: `#F9F2EB` / `rgb(249, 242, 235)`

Red: `#F9F3F3` / `rgb(249, 243, 243)`
