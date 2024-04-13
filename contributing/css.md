# Contribution to Debug Toolbar CSS

CodeIgniter uses Dart Sass to generate the debug toolbar's CSS. Therefore,
you will need to install it first. You can find further instructions on
the official website: <https://sass-lang.com/install>

## Compile SASS files

Open your terminal, and navigate to CodeIgniter's root folder. To
generate the CSS file, use the following command:

```console
sass --no-source-map admin/css/debug-toolbar/toolbar.scss system/Debug/Toolbar/Views/toolbar.css
```

Details:
- `--no-source-map` is an option which prevents sourcemap files from being generated
- `admin/css/debug-toolbar/toolbar.scss` is the SASS source
- `system/Debug/Toolbar/Views/toolbar.css` is he CSS destination

## Color scheme

See [_graphic-charter.scss](../admin/css/debug-toolbar/_graphic-charter.scss).
