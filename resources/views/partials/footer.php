<footer class="text-center" style="padding:1rem 0;margin-top:2rem;background:#f3f4f6">
    <small>&copy; <?= date('Y'); ?> Turo. All rights reserved.</small>
</footer>
</body>
<script>
    const navMobile = document.getElementById('primary-navigation');

    const toggle = document.querySelector('.nav-toggle');



    toggle.addEventListener('click', () => {
        const isOpen = navMobile.getAttribute('data-visible') === 'true';
        navMobile.setAttribute('data-visible', String(!isOpen));
        toggle.setAttribute('aria-expanded', String(!isOpen));
    });


    /* ─── click-to-show details inside calendar ───────*/
    document.querySelectorAll('.calendar td').forEach(td => {
        td.addEventListener('click', () => {
            const copy = td.cloneNode(true);
            copy.querySelectorAll('.num').forEach(n => n.remove());
            document.getElementById('details').innerHTML =
                copy.innerHTML.trim() ? copy.innerHTML : '<em>No items this day</em>';
        });
    });
</script>