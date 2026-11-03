const navigationWrap = '.md-sidebar--primary .md-sidebar__scrollwrap';
const navigationScrollKey = 'navigation.scroll';
const scrollElementTo = Element.prototype.scrollTo;

// The theme centers the active link on every page load, with no option to turn that off.
Element.prototype.scrollTo = function (...position) {
    const saved = this.matches(navigationWrap) ? __md_get(navigationScrollKey, sessionStorage) : null;
    const active = saved === null ? null : this.querySelector('.md-nav__link--active[href]');

    if (active === null) {
        return scrollElementTo.apply(this, position);
    }

    const activeTop = active.getBoundingClientRect().top - this.getBoundingClientRect().top + this.scrollTop;
    const isVisible = activeTop >= saved && activeTop + active.offsetHeight <= saved + this.clientHeight;

    return isVisible ? scrollElementTo.call(this, { top: saved }) : scrollElementTo.apply(this, position);
};

document.addEventListener('scroll', (event) => {
    if (event.target.matches?.(navigationWrap)) {
        __md_set(navigationScrollKey, event.target.scrollTop, sessionStorage);
    }
}, { capture: true, passive: true });
