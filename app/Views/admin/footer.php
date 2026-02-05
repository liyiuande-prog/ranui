<script>
    // Mobile Sidebar Toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        
        const isClosed = sidebar.classList.contains('-translate-x-full');
        
        if (isClosed) {
            // Open
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                backdrop.classList.remove('pointer-events-none');
                backdrop.style.opacity = '1';
            }, 10);
        } else {
            // Close
            sidebar.classList.add('-translate-x-full');
            backdrop.style.opacity = '0';
            backdrop.classList.add('pointer-events-none');
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300); // Wait for transition
        }
    }

    // CSRF Post Action Helper
    function postAction(url, confirmMsg = null) {
        if (confirmMsg && !confirm(confirmMsg)) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        // CSRF Token
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = csrfMeta.getAttribute('content');
            form.appendChild(input);
        } else {
             console.error("CSRF Meta tag not found!");
             alert("Security Error: CSRF token missing.");
             return;
        }

        document.body.appendChild(form);
        form.submit();
    }
</script>
</body>
</html>
