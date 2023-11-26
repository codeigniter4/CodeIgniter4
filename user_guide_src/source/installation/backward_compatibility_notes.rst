############################
Backward Compatibility Notes
############################

We try to develop our products to be as backward compatible (BC) as possible.

Only major releases (such as 4.0, 5.0 etc.) are allowed to break backward compatibility.
Minor releases (such as 4.2, 4.3 etc.) may introduce new features, but must do so without breaking the existing API.

However, the code is not mature and bug fixes may break compatibility in minor releases, or even in patch releases (such as 4.2.5). In that case, all the breaking changes are described in the :doc:`../changelogs/index`.

*****************************
What are not Breaking Changes
*****************************

- The deprecated items are not covered by backwards compatibility (BC) promise. It may be removed in the next next
  **minor** version or later. For example, if an item has been deprecated since 4.3.x,
  it may be removed in 4.5.0.
- System messages defined in **system/Language/en/** are strictly for internal framework use and are not covered by backwards compatibility (BC) promise. If developers are relying on language string output they should be checking it against the function call (``lang('...')``), not the content.
- `Named arguments <https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments>`_ are not covered by backwards compatibility (BC) promise. We may choose to rename method/function parameter names when necessary in order to improve the  codebase.
