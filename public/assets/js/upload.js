document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileUpload');
    if (!fileInput) return;

    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        const projectId = window.location.pathname.split('/').pop();
        if (!projectId || isNaN(projectId)) {
            console.error("Project ID not found in URL");
            return;
        }

        [...files].forEach(file => {
            uploadFile(file, projectId);
        });
    }

    function uploadFile(file, projectId) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('project_id', projectId);
        // Add CSRF token if needed - CodeIgniter 4 usually looks for 'csrf_token_name'
        // We need to grab it from a meta tag or input
        const csrfTokenName = 'csrf_test_name'; // Default CI4 name
        const csrfHash = document.querySelector('input[name="csrf_test_name"]')?.value
                      || document.querySelector('meta[name="csrf-token"]')?.content;

        if(csrfHash) {
            formData.append(csrfTokenName, csrfHash);
        }

        fetch('/api/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 201 || data.status === 200) {
                // Success
                console.log('Upload success:', data);
                // Ideally refresh asset list here
                alert('فایل با موفقیت آپلود شد: ' + file.name);
            } else {
                console.error('Upload failed:', data);
                alert('خطا در آپلود فایل: ' + (data.messages?.error || data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطای شبکه در آپلود فایل');
        });
    }
});
