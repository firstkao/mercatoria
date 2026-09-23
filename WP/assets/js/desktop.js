(function(){
'use strict';

// Cart drawer
const drawer=document.querySelector('[data-cart-drawer]');
const backdrop=document.querySelector('[data-cart-backdrop]');
const closeBtn=document.querySelector('[data-cart-close]');

function open(){if(!drawer)return;drawer.classList.add('is-open');drawer.setAttribute('aria-hidden','false');backdrop&&backdrop.classList.add('is-open');document.body.classList.add('no-scroll')}
function close(){if(!drawer)return;drawer.classList.remove('is-open');drawer.setAttribute('aria-hidden','true');backdrop&&backdrop.classList.remove('is-open');document.body.classList.remove('no-scroll')}

document.querySelectorAll('[data-cart-open]').forEach(b=>b.addEventListener('click',open));
closeBtn&&closeBtn.addEventListener('click',close);
backdrop&&backdrop.addEventListener('click',close);
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&drawer&&drawer.classList.contains('is-open'))close()});

// Sticky header
const header=document.getElementById('site-header');
if(header){window.addEventListener('scroll',()=>header.classList.toggle('is-scrolled',window.scrollY>8),{passive:true})}

// Cart badge from localStorage (placeholder)
try{
  const cart=JSON.parse(localStorage.getItem('mercatoria_cart')||'[]');
  const count=cart.reduce((s,i)=>s+(i.qty||0),0);
  document.querySelectorAll('[data-cart-count]').forEach(el=>{el.textContent=count;el.style.display=count>0?'':'none'});
}catch(e){}

  /* ---------- Price Range Slider ---------- */
  document.querySelectorAll('[data-price-slider]').forEach(function (form) {
    const minInput  = form.querySelector('[data-price-min]');
    const maxInput  = form.querySelector('[data-price-max]');
    const minHidden = form.querySelector('[data-price-min-hidden]');
    const maxHidden = form.querySelector('[data-price-max-hidden]');
    const fill      = form.querySelector('.slider-fill');
    const minLabel  = form.parentElement.querySelector('[data-price-min-label]') || form.querySelector('[data-price-min-label]');
    const maxLabel  = form.parentElement.querySelector('[data-price-max-label]') || form.querySelector('[data-price-max-label]');

    if (!minInput || !maxInput) return;

    function formatRp(n) {
      return 'Rp' + Number(n).toLocaleString('id-ID');
    }

    function update() {
      let min = parseInt(minInput.value, 10);
      let max = parseInt(maxInput.value, 10);

      if (min > max) {
        [min, max] = [max, min];
      }

      const rangeMin = parseInt(minInput.min, 10);
      const rangeMax = parseInt(minInput.max, 10);
      const range = rangeMax - rangeMin || 1;
      const left  = ((min - rangeMin) / range) * 100;
      const right = ((max - rangeMin) / range) * 100;

      if (fill) {
        fill.style.left  = left + '%';
        fill.style.width = (right - left) + '%';
      }
      if (minLabel) minLabel.textContent = formatRp(min);
      if (maxLabel) maxLabel.textContent = formatRp(max);

      if (minHidden) minHidden.value = min;
      if (maxHidden) maxHidden.value = max;
    }

    minInput.addEventListener('input', update);
    maxInput.addEventListener('input', update);
    update();
  });

window.MercatoriaUI={open,close};

/* ---------- Hero Slider ---------- */
document.querySelectorAll('[data-hero-slider]').forEach(function (slider) {
  const slides = slider.querySelectorAll('.hero-slide');
  if (slides.length < 1) return;

  const dots     = slider.querySelectorAll('[data-hero-dot]');
  const prevBtn  = slider.querySelector('[data-hero-prev]');
  const nextBtn  = slider.querySelector('[data-hero-next]');
  const autoplay = slider.dataset.autoplay === '1';
  const interval = parseInt(slider.dataset.interval, 10) || 5000;

  let current = 0;
  let timer   = null;

  function goTo(idx) {
    idx = (idx + slides.length) % slides.length;
    slides.forEach(function (s, i) {
      s.classList.toggle('is-active', i === idx);
      s.setAttribute('aria-hidden', i === idx ? 'false' : 'true');
    });
    dots.forEach(function (d, i) {
      d.classList.toggle('is-active', i === idx);
      d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
    });
    current = idx;
  }

  function next() { goTo(current + 1); }
  function prev() { goTo(current - 1); }

  function start() {
    if (!autoplay || slides.length < 2) return;
    stop();
    timer = setInterval(next, interval);
  }
  function stop() {
    if (timer) { clearInterval(timer); timer = null; }
  }

  prevBtn && prevBtn.addEventListener('click', function () { prev(); start(); });
  nextBtn && nextBtn.addEventListener('click', function () { next(); start(); });

  dots.forEach(function (d) {
    d.addEventListener('click', function () {
      goTo(parseInt(d.dataset.heroDot, 10));
      start();
    });
  });

  // Pause saat hover
  slider.addEventListener('mouseenter', stop);
  slider.addEventListener('mouseleave', start);

  // Keyboard nav
  slider.setAttribute('tabindex', '0');
  slider.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowLeft')  { prev(); start(); }
    if (e.key === 'ArrowRight') { next(); start(); }
  });

  // Touch swipe
  let touchX = 0, touchY = 0;
  slider.addEventListener('touchstart', function (e) {
    touchX = e.touches[0].clientX;
    touchY = e.touches[0].clientY;
    stop();
  }, { passive: true });
  slider.addEventListener('touchend', function (e) {
    const dx = e.changedTouches[0].clientX - touchX;
    const dy = e.changedTouches[0].clientY - touchY;
    if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
      dx < 0 ? next() : prev();
    }
    start();
  }, { passive: true });

  // Start autoplay
  start();
});
})();