/*
 * Version switcher for the user guide sidebar.
 *
 * Injects the sphinx_rtd_theme's native ".switch-menus > .version-switch"
 * scaffolding above the search box in the left sidebar, containing a <select>
 * so readers can jump between:
 *   - stable:               https://codeigniter.com/user_guide/
 *   - latest (in-progress): https://codeigniter4.github.io/userguide/
 *   - local build:          file:// or localhost, etc. (only shown when not on a deployed host)
 */
(() => {
	const VERSIONS = [
		{
			slug: 'stable',
			label: 'Stable',
			host: 'codeigniter.com',
			pathPrefix: '/user_guide/',
		},
		{
			slug: 'latest (in-progress)',
			label: 'Latest (in-progress)',
			host: 'codeigniter4.github.io',
			pathPrefix: '/userguide/',
		},
	];

	const LOCAL = Object.freeze({
		slug: 'local build',
		label: 'Local build',
		host: null,
		pathPrefix: null,
	});

	const detect_current = () =>
		VERSIONS.find((version) => version.host === window.location.hostname) ?? LOCAL;

	/*
	 * Derive the absolute URL of the documentation root by inspecting the
	 * <script> tag Sphinx always injects for documentation_options.js. Its
	 * resolved .src is absolute, so we can back out the directory that
	 * contains _static/, which is the doc root. This works identically for
	 * http(s), file://, and localhost.
	 */
	const doc_root_url = () => {
		const opts = document.querySelector('script[src*="documentation_options.js"]');

		if (!opts) {
			return null;
		}

		const idx = opts.src.lastIndexOf('/_static/');

		return idx < 0 ? null : opts.src.slice(0, idx + 1);
	};

	const current_page_path = () => {
		const root = doc_root_url();

		if (!root) {
			return '';
		}

        const here = window.location.href.split('#')[0].split('?')[0];

        return here.startsWith(root) ? here.slice(root.length) : '';
	};

	const url_for = ({ host, pathPrefix }) =>
		`https://${host}${pathPrefix}${current_page_path()}${window.location.hash}`;

	const build = () => {
		const searchArea = document.querySelector('.wy-side-nav-search');
		const searchForm = searchArea?.querySelector('[role="search"]');

		if (!searchArea || !searchForm) {
			return;
		}

        if (searchArea.querySelector('.switch-menus')) {
			return;
		}

		const current = detect_current();
		const entries = current === LOCAL ? [LOCAL, ...VERSIONS] : VERSIONS;

		const select = document.createElement('select');
		select.setAttribute('aria-label', 'Select documentation version');

		for (const entry of entries) {
			const option = document.createElement('option');
			option.value = entry.host ? url_for(entry) : '';
			option.textContent = entry.label;
			option.selected = entry.slug === current.slug;
			select.append(option);
		}

		select.addEventListener('change', () => {
			if (select.value) {
				window.location.href = select.value;
			}
		});

		const versionSwitch = document.createElement('div');
		versionSwitch.className = 'version-switch';
		versionSwitch.append(select);

		const switchMenus = document.createElement('div');
		switchMenus.className = 'switch-menus';
		switchMenus.append(versionSwitch);

		searchArea.insertBefore(switchMenus, searchForm);
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', build, { once: true });
	} else {
		build();
	}
})();
