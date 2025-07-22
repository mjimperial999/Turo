<footer class="text-center" style="padding:1rem 0;margin-top:2rem;background:#f3f4f6">
    <small>&copy; <?= date('Y'); ?> Turo. All rights reserved.</small>
</footer>
</body>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    /* ─── navigation bar ───────*/
    const navMobile = document.getElementById('primary-navigation');
    const toggle = document.querySelector('.nav-toggle');
    toggle.addEventListener('click', () => {
        const isOpen = navMobile.getAttribute('data-visible') === 'true';
        navMobile.setAttribute('data-visible', String(!isOpen));
        toggle.setAttribute('aria-expanded', String(!isOpen));
    });


    /* ─── flash popups ───────*/
    document.addEventListener('DOMContentLoaded', () => {
        const stack = document.getElementById('flash-stack');
        if (!stack) return;

        // remove each toast after its fade-out so DOM stays clean
        stack.querySelectorAll('.alert').forEach(t => {
            t.addEventListener('animationend', e => {
                if (e.animationName === 'fadeOut') t.remove();
            });
        });
    });

    /* ─── click calendar tile to display activities ───────*/
    document.querySelectorAll('.calendar td').forEach(td => {
    td.onclick = () => {

        /* 1️⃣ map every recognised marker to its icon + label */
        const map = {
            '•': { icon: '●', label: 'Unlocks'       },   // unchanged
            '×': { icon: '✕', label: 'Due This Day'  },   // unchanged
            '‼': { icon: '‼', label: 'Announcement'  }    // NEW
        };

        /* 2️⃣ build the detail pane */
        const lines = [...td.querySelectorAll('.entry')].map(e => {
            const m = map[e.dataset.marker] ?? { icon: e.dataset.marker, label: '' };
            return `<div>${m.icon} <b>${m.label}</b> – ${e.dataset.text}</div>`;
        });

        /* 3️⃣ inject or fallback */
        document.getElementById('details').innerHTML =
            lines.length ? lines.join('') : '<em>No items this day</em>';
    };
});
</script>