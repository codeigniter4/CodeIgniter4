/* Splits Pygments' single keyword class the way VS Code's Modern themes do:
   control-flow keywords and $this get their own classes for code.css to color. */
const controlKeywords = new Set([
    'if', 'else', 'elseif', 'switch', 'case', 'default', 'for', 'foreach',
    'while', 'do', 'break', 'continue', 'return', 'try', 'catch', 'finally',
    'throw', 'match', 'yield', 'goto',
]);

// Material's document$ emits on the first load and after every instant navigation.
document$.subscribe(() => {
    for (const span of document.querySelectorAll('.highlight .k')) {
        if (controlKeywords.has(span.textContent)) {
            span.classList.add('k-control');
        }
    }

    for (const span of document.querySelectorAll('.highlight .nv')) {
        if (span.textContent === '$this') {
            span.classList.add('nv-this');
        }
    }

    for (const span of document.querySelectorAll('.highlight .nx')) {
        if (/^[A-Z][A-Z0-9_]*$/.test(span.textContent)) {
            span.classList.add('nx-constant');
        } else if (/^[A-Z]/.test(span.textContent)) {
            span.classList.add('nx-class');
        }
    }

    for (const span of document.querySelectorAll('.highlight .na')) {
        const next = span.nextElementSibling;

        if (next !== null && next.classList.contains('p') && next.textContent.startsWith('(')) {
            span.classList.add('na-call');
        }
    }
});
