#################################
Writing CodeIgniter Documentation
#################################

CodeIgniter uses Sphinx to generate its documentation in a variety of formats,
using reStructuredText to handle the formatting.  If you are familiar with
Markdown or Textile, you will quickly grasp reStructuredText.  The focus is
on readability and user friendliness.
While they can be quite technical, we always write for humans!

A local table of contents should always be included, like the one below.
It is created automatically by inserting the following:

::

	.. contents::
		:local:

	.. raw:: html

	<div class="custom-index container"></div>

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

The <div> that is inserted as raw HTML is a event for the documentation's
JavaScript to dynamically add links to any function and method definitions
contained in the current page.

**************
Tools Required
**************

To see the rendered HTML, ePub, PDF, etc., you will need to install Sphinx
along with the PHP domain extension for Sphinx. The underlying requirement
is to have Python installed.

You can read more about installing all tools in /user_guide_src/README.rst

*****************************************
Page and Section Headings and Subheadings
*****************************************

Headings not only provide order and sections within a page, but they also
are used to automatically build both the page and document table of contents.
Headings are formed by using certain characters as underlines for a bit of
text.  Major headings, like page titles and section headings also use
overlines.  Other headings just use underlines, with the following hierarchy::

	# with overline for page titles
	* with overline for major sections
	= for subsections
	- for subsubsections
	^ for subsubsubsections
	" for subsubsubsubsections (!)

The :download:`TextMate ELDocs Bundle <./ELDocs.tmbundle.zip>` can help you
create these with the following tab triggers::

	title->

		##########
		Page Title
		##########

	sec->

		*************
		Major Section
		*************

	sub->

		Subsection
		==========

	sss->

		SubSubSection
		-------------

	ssss->

		SubSubSubSection
		^^^^^^^^^^^^^^^^

	sssss->

		SubSubSubSubSection (!)
		"""""""""""""""""""""""
