const isMac = /Mac|iPhone|iPad/.test(navigator.userAgentData?.platform ?? navigator.platform);

document.querySelector('.md-search__form')?.setAttribute('data-shortcut', isMac ? '⌘K' : 'Ctrl K');

document.addEventListener('keydown', (event) => {
    if (event.key.toLowerCase() !== 'k' || !(isMac ? event.metaKey : event.ctrlKey)) {
        return;
    }

    event.preventDefault();

    const toggle = document.querySelector('[data-md-toggle="search"]');

    toggle.checked = true;
    toggle.dispatchEvent(new CustomEvent('change'));
    document.querySelector('.md-search__input')?.focus();
});
