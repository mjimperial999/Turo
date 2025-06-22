<footer class="text-center" style="padding:1rem 0;margin-top:2rem;background:#f3f4f6">
    <small>&copy; <?= date('Y'); ?> Turo. All rights reserved.</small>
</footer>
</body>
<script>
    const nav = document.getElementById('primary-navigation');
    const toggle = document.querySelector('.nav-toggle');

    toggle.addEventListener('click', () => {
        const isOpen = nav.getAttribute('data-visible') === 'true';
        nav.setAttribute('data-visible', String(!isOpen));
        toggle.setAttribute('aria-expanded', String(!isOpen));
    });
</script>

</html>