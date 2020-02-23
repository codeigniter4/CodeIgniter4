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

Color scheme
============

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
