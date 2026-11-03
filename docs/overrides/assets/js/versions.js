const root = new URL(JSON.parse(document.querySelector('#__config').textContent).base, location).href;
const isLocal = ['localhost', '127.0.0.1', ''].includes(location.hostname);
const versionsUrl = isLocal ? new URL('assets/versions.json', root) : 'https://codeigniter4.github.io/CodeIgniter4/versions.json';

const releaseUrl = 'https://api.github.com/repos/codeigniter4/CodeIgniter4/releases/latest';
const releaseKey = 'version.latest';

const latestTag = async () => {
    try {
        const cached = sessionStorage.getItem(releaseKey);

        if (cached !== null) {
            return cached;
        }
    } catch {}

    const response = await fetch(releaseUrl);

    if (!response.ok) {
        throw new Error(response.status);
    }

    const { tag_name: tag } = await response.json();

    try {
        sessionStorage.setItem(releaseKey, tag);
    } catch {}

    return tag;
};

const withLatestTag = async (versions) => {
    if (!versions.some((version) => version.title.includes('{tag}'))) {
        return versions;
    }

    const tag = await latestTag().catch(() => null);

    return versions.map((version) => ({
        ...version,
        title: tag === null ? version.title.replace(' ({tag})', '') : version.title.replace('{tag}', tag),
    }));
};

const samePageIn =(version) => version.url + location.pathname.slice(new URL(root).pathname.length) + location.hash;

const goTo = async (version) => {
    const target = samePageIn(version);

    try {
        const response = await fetch(target, { method: 'HEAD' });

        location.assign(response.status === 404 ? version.url : target);
    } catch {
        // A host without CORS headers hides the status, so the page is assumed to exist.
        location.assign(target);
    }
};

const renderVersions = (deployed) => {
    const local = { title: 'Local build', url: root };
    const current = deployed.find((version) => root === version.url) ?? local;
    const versions = current === local ? [local, ...deployed] : deployed;
    const selector = document.createElement('div');
    const button = document.createElement('button');
    const list = document.createElement('ul');

    selector.className = 'md-version';
    button.className = 'md-version__current';
    button.setAttribute('aria-label', 'Select version');
    button.setAttribute('aria-expanded', 'false');
    button.textContent = current.title;
    list.className = 'md-version__list';

    const setOpen = (open) => {
        selector.classList.toggle('md-version--open', open);
        button.setAttribute('aria-expanded', String(open));
    };

    button.addEventListener('click', () => setOpen(!selector.classList.contains('md-version--open')));
    document.addEventListener('click', (event) => {
        if (!selector.contains(event.target)) {
            setOpen(false);
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    const links = versions.map((version) => {
        const item = document.createElement('li');
        const link = document.createElement('a');

        item.className = 'md-version__item';
        link.className = 'md-version__link';
        link.textContent = version.title;
        link.addEventListener('click', (event) => {
            if (event.metaKey || event.ctrlKey || event.shiftKey) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            goTo(version);
        });

        item.append(link);
        list.append(item);

        return [link, version];
    });

    selector.append(button, list);
    document.querySelector('.md-header__title').after(selector);

    document$.subscribe(() => {
        links.forEach(([link, version]) => {
            link.href = samePageIn(version);
        });
    });
};

fetch(versionsUrl)
    .then((response) => (response.ok ? response.json() : Promise.reject(response.status)))
    .then(withLatestTag)
    .then(renderVersions)
    .catch(() => {});
