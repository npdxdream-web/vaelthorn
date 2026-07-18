document.addEventListener('DOMContentLoaded', () => {
    initCharacterModule();
    initRegisterSteps();
    initCitySelection();
});

function initCharacterModule() {
    const module = document.getElementById('charModule');
    if (!module) return;

    const full = module.querySelector('[data-char-full]');
    const mini = module.querySelector('[data-char-mini]');
    if (!full || !mini) return;

    const COLLAPSE_AT = 140;
    const EXPAND_AT   = 80;
    let collapsed = false;

    // Lock wrapper height before hiding full module to prevent layout shift
    // (height change → scrollY change → toggle flip → flicker loop)
    const lockHeight = () => {
        module.style.minHeight = full.getBoundingClientRect().height + 'px';
    };

    const setCollapsed = (next) => {
        if (next === collapsed) return;
        collapsed = next;
        if (collapsed) {
            lockHeight();
            full.classList.add('hidden');
            mini.classList.remove('hidden');
            module.classList.add('char-module-collapsed');
        } else {
            full.classList.remove('hidden');
            mini.classList.add('hidden');
            module.classList.remove('char-module-collapsed');
            module.style.minHeight = '';
        }
    };

    const onScroll = () => {
        const canCollapse = window.matchMedia('(min-width: 1024px)').matches;
        if (!canCollapse) { setCollapsed(false); return; }
        if (!collapsed && window.scrollY > COLLAPSE_AT) setCollapsed(true);
        else if (collapsed && window.scrollY < EXPAND_AT) setCollapsed(false);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', () => {
        module.style.minHeight = '';
        collapsed = false;
        onScroll();
    });
    onScroll();
}

function initRegisterSteps() {
    const stepCity = document.getElementById('register-step-city');
    const stepCharacter = document.getElementById('register-step-character');
    const continueBtn = document.getElementById('register-continue');
    const backBtn = document.getElementById('register-back');

    if (!stepCity || !stepCharacter || !continueBtn) return;

    continueBtn.addEventListener('click', () => {
        const selected = document.querySelector('input[name="city_id"]:checked');
        if (!selected) return;

        stepCity.classList.add('hidden');
        stepCharacter.classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    backBtn?.addEventListener('click', () => {
        stepCharacter.classList.add('hidden');
        stepCity.classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function initCitySelection() {
    document.querySelectorAll('[data-city-card]').forEach((card) => {
        card.addEventListener('click', () => {
            document.querySelectorAll('[data-city-card]').forEach((c) => {
                c.classList.remove('border-gold', 'glow-gold-strong');
                c.classList.add('border-border');
                c.querySelector('[data-city-check]')?.classList.add('hidden');
            });

            card.classList.remove('border-border');
            card.classList.add('border-gold', 'glow-gold-strong');
            card.querySelector('[data-city-check]')?.classList.remove('hidden');

            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;

            const continueBtn = document.getElementById('register-continue');
            if (continueBtn) continueBtn.disabled = false;
        });
    });

    const preselected = document.querySelector('input[name="city_id"]:checked');
    if (preselected) {
        preselected.closest('[data-city-card]')?.click();
    }
}
