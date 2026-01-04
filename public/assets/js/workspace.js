document.addEventListener('DOMContentLoaded', function() {
    const previewContainer = document.querySelector('.col-md-6 .bg-white'); // The preview box
    const projectId = window.location.pathname.split('/').pop();

    // Add pages list container
    const pagesListDiv = document.createElement('div');
    pagesListDiv.className = 'd-flex gap-2 mt-3 overflow-auto';
    pagesListDiv.id = 'pages-thumbnails';
    document.querySelector('.col-md-6').appendChild(pagesListDiv);

    loadPages();

    function loadPages() {
        fetch(`/api/pages/${projectId}`)
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('pages-thumbnails');
            list.innerHTML = '';

            if (data.length > 0) {
                // Render first page by default
                renderPreview(data[0].id);

                data.forEach(page => {
                    const thumb = document.createElement('div');
                    thumb.className = 'card cursor-pointer flex-shrink-0';
                    thumb.style.width = '100px';
                    thumb.style.cursor = 'pointer';
                    thumb.innerHTML = `
                        <div class="card-body p-2 text-center">
                            <span class="small fw-bold">صفحه ${page.page_number}</span>
                            <br>
                            <span class="small text-muted">${page.layout_type}</span>
                        </div>
                    `;
                    thumb.addEventListener('click', () => renderPreview(page.id));
                    list.appendChild(thumb);
                });
            } else {
                 previewContainer.innerHTML = '<span class="text-muted">صفحه‌ای موجود نیست</span>';
            }
        })
        .catch(err => console.error(err));
    }

    function renderPreview(pageId) {
        // Create iframe
        previewContainer.innerHTML = '';
        const iframe = document.createElement('iframe');
        iframe.src = `/api/page/preview/${pageId}`;
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        iframe.style.border = 'none';

        // Adjust scale to fit
        // The page is 1920x1080.
        // Container max-width is 800px.
        // Ratio 1920/1080 = 1.77
        // Container Aspect Ratio is 16/9.
        // To fit 1920 into 800, scale = 0.416

        // Actually, CSS transform scale on body of iframe content is tricky from outside.
        // Better to set iframe width to 1920 and scale down with CSS transform on the iframe itself.

        iframe.style.width = '1920px';
        iframe.style.height = '1080px';
        iframe.style.transformOrigin = '0 0';

        // Calculate scale
        const containerWidth = previewContainer.offsetWidth;
        const scale = containerWidth / 1920;
        iframe.style.transform = `scale(${scale})`;

        previewContainer.style.overflow = 'hidden';

        // Handle resize
        window.addEventListener('resize', () => {
             const newScale = previewContainer.offsetWidth / 1920;
             iframe.style.transform = `scale(${newScale})`;
        });

        previewContainer.appendChild(iframe);
    }

    // Expose reload function for Chat to call
    window.reloadPages = loadPages;
});
