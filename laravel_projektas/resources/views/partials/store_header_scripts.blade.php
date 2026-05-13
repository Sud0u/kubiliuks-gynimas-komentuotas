<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;

    const accountBtn = document.getElementById('accountMenuBtn');
    const accountMenu = document.getElementById('accountMenu');
    const accountWrap = document.getElementById('accountMenuWrap');

    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mobileNav = document.getElementById('mobileNav');
    const mobileBackdrop = document.getElementById('mobileMenuBackdrop');

    function closeAccountMenu() {
        if (!accountMenu || !accountBtn) {
            return;
        }

        accountMenu.classList.add('hidden');
        accountBtn.setAttribute('aria-expanded', 'false');
    }

    function toggleAccountMenu() {
        if (!accountMenu || !accountBtn) {
            return;
        }

        const isHidden = accountMenu.classList.contains('hidden');

        if (isHidden) {
            closeMobileMenu();
            accountMenu.classList.remove('hidden');
            accountBtn.setAttribute('aria-expanded', 'true');
        } else {
            closeAccountMenu();
        }
    }

    function closeMobileMenu() {
        if (!mobileNav || !mobileBtn || !mobileBackdrop) {
            return;
        }

        mobileNav.classList.add('hidden');
        mobileBackdrop.classList.add('hidden');
        mobileBtn.setAttribute('aria-expanded', 'false');
        body.classList.remove('overflow-hidden');
    }

    function toggleMobileMenu() {
        if (!mobileNav || !mobileBtn || !mobileBackdrop) {
            return;
        }

        const isHidden = mobileNav.classList.contains('hidden');

        if (isHidden) {
            closeAccountMenu();
            mobileNav.classList.remove('hidden');
            mobileBackdrop.classList.remove('hidden');
            mobileBtn.setAttribute('aria-expanded', 'true');
            body.classList.add('overflow-hidden');
        } else {
            closeMobileMenu();
        }
    }

    if (accountBtn) {
        accountBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggleAccountMenu();
        });
    }

    if (mobileBtn) {
        mobileBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMobileMenu();
        });
    }

    if (mobileBackdrop) {
        mobileBackdrop.addEventListener('click', function () {
            closeMobileMenu();
        });
    }

    document.addEventListener('click', function (e) {
        if (accountWrap && !accountWrap.contains(e.target)) {
            closeAccountMenu();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAccountMenu();
            closeMobileMenu();
        }
    });

    if (mobileNav) {
        mobileNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                closeMobileMenu();
            });
        });
    }

    const desktopCartBadge = document.getElementById('cartCountBadge');
    const mobileCartBadges = document.querySelectorAll('[data-cart-count]');

    function renderCartCount(count) {
        if (desktopCartBadge) {
            if (count > 0) {
                desktopCartBadge.textContent = count;
                desktopCartBadge.classList.remove('hidden');
            } else {
                desktopCartBadge.textContent = '';
                desktopCartBadge.classList.add('hidden');
            }
        }

        mobileCartBadges.forEach(function (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.textContent = '';
                badge.classList.add('hidden');
            }
        });
    }

    async function loadCartCount() {
        try {
            const res = await fetch('/api/v1/cart', {
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!res.ok) {
                renderCartCount(0);
                return;
            }

            const json = await res.json();
            const count = Number(json?.data?.count ?? 0);

            renderCartCount(count);
        } catch (e) {
            renderCartCount(0);
            console.error('Cart badge load error', e);
        }
    }

    window.refreshCartBadge = loadCartCount;

    loadCartCount();

    if (!window.showToast) {
        const wrap = document.createElement('div');
        wrap.id = 'toastWrap';
        wrap.className = 'fixed top-[112px] left-1/2 z-[9999] flex w-[calc(100%-32px)] max-w-[320px] -translate-x-1/2 flex-col gap-2 pointer-events-none sm:left-auto sm:right-5 sm:top-[92px] sm:w-full sm:translate-x-0 sm:items-end';
        document.body.appendChild(wrap);

        window.showToast = function (text, type = 'info', timeoutMs = 1600) {
            const msg = String(text || '').trim();

            if (!msg) {
                return;
            }

            const el = document.createElement('div');
            el.className = 'pointer-events-auto w-full rounded-2xl border bg-white/95 px-4 py-2.5 text-sm shadow-lg backdrop-blur transition';

            const base = ['border-stone-200', 'text-stone-900'];
            const ok = ['border-emerald-200', 'text-emerald-900'];
            const err = ['border-red-200', 'text-red-900'];
            const warn = ['border-amber-200', 'text-amber-900'];

            const cls = type === 'ok' ? ok : type === 'error' ? err : type === 'warn' ? warn : base;
            el.classList.add(...cls);

            el.textContent = msg;
            wrap.appendChild(el);

            const remove = () => {
                if (!el.parentNode) {
                    return;
                }

                el.classList.add('opacity-0');
                setTimeout(() => el.remove(), 180);
            };

            el.addEventListener('click', remove);
            setTimeout(remove, Math.max(700, Number(timeoutMs) || 1600));
        };
    }
});
</script>
