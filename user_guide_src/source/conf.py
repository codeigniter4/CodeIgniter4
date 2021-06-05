# Configuration file for the Sphinx documentation builder.
#
# This file only contains a selection of the most common options. For a full
# list see the documentation:
# https://www.sphinx-doc.org/en/master/usage/configuration.html

# -- Path setup --------------------------------------------------------------

# If extensions (or modules to document with autodoc) are in another directory,
# add these directories to sys.path here. If the directory is relative to the
# documentation root, use os.path.abspath to make it absolute, like shown here.
#
# import os
# import sys
# sys.path.insert(0, os.path.abspath('.'))

# -- Project information -----------------------------------------------------

project = 'CodeIgniter'
author = 'CodeIgniter Foundation'
copyright = '2019-2021 CodeIgniter Foundation'

# The short X.Y version.
version = '4.1'

# The full version, including alpha/beta/rc tags.
release = '4.1.3'

# -- General configuration ---------------------------------------------------

# The master toctree document.
master_doc = 'index'

# Add any Sphinx extension module names here, as strings. They can be
# extensions coming with Sphinx (named 'sphinx.ext.*') or your custom
# ones.
extensions = [
	'sphinxcontrib.phpdomain',
	'sphinx_rtd_theme',
]

# Add any paths that contain templates here, relative to this directory.
# templates_path = ['_templates']

# List of patterns, relative to source directory, that match files and
# directories to ignore when looking for source files.
# This pattern also affects html_static_path and html_extra_path.
exclude_patterns = []

# The name of the Pygments (syntax highlighting) style to use.
pygments_style = 'trac'

# The default language to highlight source code in.
highlight_language = 'html+php'

# A dictionary of options that modify how the lexer specified by
# highlight_language generates highlighted source code.
highlight_options = {'startinline': True}

# -- Options for HTML output -------------------------------------------------

# The theme to use for HTML and HTML Help pages.  See the documentation for
# a list of builtin themes.
html_theme = 'sphinx_rtd_theme'

# Add any paths that contain custom static files (such as style sheets) here,
# relative to this directory. They are copied after the builtin static files,
# so a file named "default.css" will overwrite the builtin "default.css".
html_static_path = ['_static']

# Theme options are theme-specific and customize the look and feel of a theme
# further.  For a list of options available for each theme, see the
# documentation.
html_theme_options = {
	'collapse_navigation': False,
	'sticky_navigation': False,
	'navigation_depth': 2,
	'includehidden': False,
	'logo_only': True,
	'display_version': False,
	'style_nav_header_background': '#DD4814',
}

# If not '', a 'Last updated on:' timestamp is inserted at every page bottom,
# using the given strftime format.
html_last_updated_fmt = '%b %d, %Y'

# The name of an image file (relative to this directory) to place at the top
# of the sidebar.
html_logo = '_static/ci-logo-text.png'

# The name of an image file (within the static path) to use as favicon of the
# docs. This file should be a Windows icon file (.ico) being 16x16 or 32x32
# pixels large.
html_favicon = '_static/favicon.ico'

# The name of an style sheet to use for HTML pages.
html_style = 'css/citheme.css'

# Output file base name for HTML help builder.
htmlhelp_basename = 'CodeIgniterdoc'

# If true, the reST sources are included in the HTML build as _sources/name.
html_copy_source = False

# A list of CSS files.
html_css_files = []

# A list of JS files.
html_js_files = [
	'js/citheme.js',
	'js/carbon.js'
]

# -- Options for LaTeX output --------------------------------------------------

# This value determines how to group the document tree into LaTeX source files.
# It must be a list of tuples (startdocname, targetname, title, author, theme,
# toctree_only)
latex_documents = [
	('index', 'CodeIgniter.tex', 'CodeIgniter4 Documentation',
	'CodeIgniter Foundation', 'manual'),
]

# -- Options for manual page output --------------------------------------------

# This value determines how to group the document tree into manual pages. It
# must be a list of tuples (startdocname, name, description, authors, section)
man_pages = [
	('index', 'codeigniter', 'CodeIgniter4 Documentation',
	['CodeIgniter Foundation'], 1)
]

# -- Options for Epub output ---------------------------------------------------

# Bibliographic Dublin Core metadata.
epub_title = 'CodeIgniter4'
epub_author = 'CodeIgniter Foundation'
epub_publisher = 'CodeIgniter Foundation'
epub_copyright = '2019-2021 CodeIgniter Foundation'
