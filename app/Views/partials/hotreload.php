<script>
    let lastHash = null;

    async function checkReload() {
        try {
            const res = await fetch("<?= base_url('/hotreload/check') ?>");
            const data = await res.json();

            if (lastHash === null) {
                lastHash = data.hash;
            } else if (data.hash !== lastHash) {
                console.log("HotReload: cambio detectado, recargando...");
                location.reload();
            }
        } catch (e) {
            console.warn("HotReload error:", e);
        }
    }

    setInterval(checkReload, 1000);
</script>
